<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\NewReplyNotification;
use App\Notifications\NewTicketNotification;
use App\Notifications\TicketStatusUpdatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ClosureRequestNotification;
use App\Notifications\TicketClosedNotification;
use App\Notifications\AgentAssignedNotification;
use App\Models\TicketCategory;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\HardwareAsset;
use App\Models\OrganigramMember;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $categories = TicketCategory::orderBy('name')->get();
        $agents = Auth::user()->isSuperAdmin() ? $this->getAssignableAgents() : collect();

        $query = Ticket::query();
        $user = Auth::user();

        if (!$user->isSuperAdmin()) {
            $query->where(function ($subQuery) use ($user) {
                $subQuery->where('user_id', $user->id)
                        ->orWhere('agent_id', $user->id);
            });
        }

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                ->orWhereHas('user', fn($userQuery) => $userQuery->where('name', 'like', $searchTerm));
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('category_id')) {
            $query->whereHas('subCategory.category', function ($q) use ($request) {
                $q->where('id', $request->category_id);
            });
        }
        
        if ($user->isSuperAdmin() && $request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        $tickets = $query->with(['user', 'subCategory.category', 'agent'])
                        ->latest()
                        ->paginate(15)
                        ->withQueryString();

        return view('tickets.index', [
            'tickets' => $tickets,
            'categories' => $categories,
            'agents' => $agents,
            'filters' => $request->all()
        ]);
    }

    public function create()
    {
        $categories = TicketCategory::with('subCategories')->orderBy('name')->get();
        
        $userAssets = collect();
        $organigramMember = Auth::user()->organigramMember;

        if ($organigramMember) {
            $assignedAssetIds = $organigramMember->assignments()
                ->whereNull('actual_return_date')
                ->pluck('hardware_asset_id');
                
            $userAssets = HardwareAsset::with('model')->whereIn('id', $assignedAssetIds)->get();
        }

        return view('tickets.create', compact('categories', 'userAssets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:Baja,Media,Alta',
            'attachment' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:100048',
            'ticket_sub_category_id' => 'required|exists:ticket_sub_categories,id',
            'hardware_asset_id' => 'nullable|exists:hardware_assets,id',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('tickets/attachments', 's3');
        }

        $ticket = Ticket::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'attachment_path' => $attachmentPath,
            'ticket_sub_category_id' => $request->ticket_sub_category_id,
            'hardware_asset_id' => $request->hardware_asset_id,
            'status' => 'Abierto',
        ]);

        $ticket->statusHistories()->create(['user_id' => Auth::id(), 'status' => 'Abierto']);

        $superAdmins = User::where('is_area_admin', true)
            ->whereHas('area', function ($query) {
                $query->where('name', 'Administración');
            })->get();

        if ($superAdmins->isNotEmpty()) {
            Notification::send($superAdmins, new NewTicketNotification($ticket));
        }
        
        Auth::user()->notify(new NewTicketNotification($ticket));

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket creado exitosamente.');
    }

    public function show(Ticket $ticket)
    {
        $user = Auth::user();

        if ($user->id !== $ticket->user_id && !$user->isSuperAdmin() && $user->id !== $ticket->agent_id) {
            abort(403, 'No tienes permiso para ver este ticket.');
        }

        $ticket->load(['user', 'agent', 'subCategory', 'replies.user', 'statusHistories.user', 'asset.model']);

        $agents = $this->getAssignableAgents();

        return view('tickets.show', compact('ticket', 'agents'));
    }

    public function storeReply(Request $request, Ticket $ticket)
    {
        $user = Auth::user();
        if ($ticket->status === 'Cerrado') {
            return back()->with('error', 'Este ticket está cerrado y no admite más respuestas.');
        }
        if ($user->id !== $ticket->user_id && !$user->isSuperAdmin() && $user->id !== $ticket->agent_id) {
            abort(403, 'No tienes permiso para responder a este ticket.');
        }

        $request->validate(['body' => 'required|string']);
        $isInternal = $request->has('is_internal') && ($user->isSuperAdmin() || $user->id === $ticket->agent_id);

        $reply = $ticket->replies()->create([
            'user_id' => $user->id,
            'body' => $request->body,
            'is_internal' => $isInternal,
        ]);

        if (!$isInternal) {
            $ticket->statusHistories()->create([
                'user_id' => $user->id,
                'status' => $user->id === $ticket->user_id ? 'Mensaje de Usuario' : 'Respuesta de Agente'
            ]);
        }

        if (!$reply->is_internal) {
            if (($user->isSuperAdmin() || $user->id === $ticket->agent_id) && $user->id !== $ticket->user_id) {
                $ticket->user->notify(new NewReplyNotification($reply));
            } elseif ($user->id === $ticket->user_id) {
                $adminsAndAgent = User::where('is_area_admin', true)
                    ->whereHas('area', function ($query) { $query->where('name', 'Administración'); })
                    ->orWhere('id', $ticket->agent_id)
                    ->get()
                    ->unique('id');

                if ($adminsAndAgent->isNotEmpty()) {
                    Notification::send($adminsAndAgent, new NewReplyNotification($reply));
                }
            }
        }
        else {
            if ($user->isSuperAdmin() && $ticket->agent_id && $ticket->agent_id !== $user->id) {
                // Si un admin escribe, notifica solo al agente (si no es él mismo).
                $ticket->agent->notify(new NewReplyNotification($reply));
            } elseif ($user->id === $ticket->agent_id) {
                // Si el agente escribe, notifica solo a los SuperAdmins.
                $superAdmins = User::where('is_area_admin', true)
                    ->whereHas('area', function ($query) { $query->where('name', 'Administración'); })
                    ->get();
                if ($superAdmins->isNotEmpty()) {
                    Notification::send($superAdmins, new NewReplyNotification($reply));
                }
            }
        }

        return back()->with('success', 'Respuesta enviada.');
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin() && $user->id !== $ticket->agent_id) {
            abort(403, 'No tienes permiso para cambiar el estado.');
        }

        $newStatusRequest = $request->input('status');
        $allowedTransitions = [
            'Abierto' => ['En Proceso'],
            'En Proceso' => ['Pendiente de Aprobación'],
        ];

        if ($newStatusRequest === 'Cerrado') $newStatusRequest = 'Pendiente de Aprobación';

        if (!isset($allowedTransitions[$ticket->status]) || !in_array($newStatusRequest, $allowedTransitions[$ticket->status])) {
            return back()->with('error', 'No se puede cambiar el ticket al estado seleccionado desde su estado actual.');
        }

        $request->validate([
            'work_summary' => 'required_if:status,Cerrado|string|nullable',
            'closure_evidence' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:100048',
        ]);

        if ($request->status === 'Cerrado') {
            $evidencePath = null;
            if ($request->hasFile('closure_evidence')) {
                $evidencePath = $request->file('closure_evidence')->store('tickets/evidence', 's3');
            }

            $ticket->status = 'Pendiente de Aprobación';
            $ticket->closure_evidence_path = $evidencePath;
            $ticket->work_summary = $request->work_summary;
            $ticket->save();

            $ticket->statusHistories()->create(['user_id' => Auth::id(), 'status' => 'Pendiente de Aprobación']);
            
            $ticket->user->notify(new ClosureRequestNotification($ticket));
            return back()->with('success', 'Se ha solicitado la aprobación de cierre al usuario.');
        } else {
            $ticket->status = $newStatusRequest;
            $ticket->save();
            $ticket->statusHistories()->create(['user_id' => Auth::id(), 'status' => $newStatusRequest]);
            $ticket->user->notify(new TicketStatusUpdatedNotification($ticket));
        }

        return back()->with('success', 'El estado del ticket ha sido actualizado.');
    }

    public function approveClosure(Ticket $ticket)
    {
        if (Auth::id() !== $ticket->user_id) {
            abort(403, 'No tienes permiso para aprobar el cierre de este ticket.');
        }

        if ($ticket->status !== 'Pendiente de Aprobación') {
            return back()->with('error', 'Este ticket no está esperando una aprobación.');
        }

        $ticket->status = 'Cerrado';
        $ticket->save();

        $ticket->statusHistories()->create([
            'user_id' => Auth::id(),
            'status' => 'Cerrado'
        ]);

        $superAdmins = User::where('is_area_admin', true)
            ->whereHas('area', function ($query) {
                $query->where('name', 'Administración');
            })->get();

        if ($superAdmins->isNotEmpty()) {
            Notification::send($superAdmins, new TicketClosedNotification($ticket));
        }

        return back()->with('success', 'Has aprobado el cierre. El ticket ha sido cerrado exitosamente.');
    }

    public function assignAgent(Request $request, Ticket $ticket)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'No tienes permiso para asignar este ticket.');
        }

        $request->validate(['agent_id' => 'required|exists:users,id']);

        $agent = User::find($request->agent_id);

        if ($agent->is_client) {
            return back()->with('error', 'No se puede asignar un ticket a un usuario cliente.');
        }

        $ticket->agent_id = $agent->id;
        $ticket->save();

        $agent->notify(new AgentAssignedNotification($ticket));

        return back()->with('success', 'Ticket asignado a ' . $agent->name . ' exitosamente.');
    }

    public function storeRating(Request $request, Ticket $ticket)
    {
        if (Auth::id() !== $ticket->user_id) {
            abort(403, 'No tienes permiso para calificar este ticket.');
        }
        if ($ticket->status !== 'Cerrado') {
            return back()->with('error', 'Solo puedes calificar un ticket que ha sido cerrado.');
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'rating_comment' => 'nullable|string|max:1000',
        ]);

        $ticket->rating = $request->rating;
        $ticket->rating_comment = $request->rating_comment;
        $ticket->save();

        return back()->with('success', 'Gracias por tu calificación.');
    }

    public function rejectClosure(Request $request, Ticket $ticket)
    {
        if (Auth::id() !== $ticket->user_id) {
            abort(403, 'No tienes permiso para esta acción.');
        }
        if ($ticket->status !== 'Pendiente de Aprobación') {
            return back()->with('error', 'Este ticket no está esperando una aprobación.');
        }

        $request->validate(['rejection_reason' => 'required|string|min:10']);

        DB::transaction(function () use ($request, $ticket) {
            $ticket->status = 'En Proceso';
            $ticket->save();

            $ticket->statusHistories()->create([
                'user_id' => Auth::id(),
                'status' => 'Cierre Rechazado'
            ]);

            $reply = $ticket->replies()->create([
                'user_id' => Auth::id(),
                'body' => "Motivo del Rechazo:\n" . $request->rejection_reason,
                'is_internal' => false
            ]);

            $recipients = collect([$ticket->agent])->merge($this->getSuperAdmins())->whereNotNull()->unique('id');
            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new NewReplyNotification($reply));
            }
        });

        return redirect()->route('tickets.show', $ticket)->with('success', 'El cierre ha sido rechazado. El ticket vuelve a estar "En Proceso".');
    }

    private function getSuperAdmins()
    {
        return User::where('is_area_admin', true)
            ->whereHas('area', function ($query) {
                $query->where('name', 'Administración');
            })->get();
    }

    private function getAssignableAgents()
    {
        return User::where('is_client', false)
            ->whereHas('area', function ($query) {
                $query->whereIn('name', ['Administración', 'Innovación y Desarrollo']);
            })
            ->orderBy('name')
            ->get();
    }    

}