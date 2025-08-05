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

class TicketController extends Controller
{
    public function index(Request $request)
    {
        // --- 1. Obtener datos para los menús desplegables del filtro ---
        $categories = TicketCategory::orderBy('name')->get();
        // Solo los SuperAdmins necesitan la lista de agentes para filtrar
        $agents = Auth::user()->isSuperAdmin() ? User::where('is_client', false)->orderBy('name')->get() : collect();

        // --- 2. Iniciar la consulta ---
        $query = Ticket::query();
        $user = Auth::user();

        // --- 3. Aplicar reglas de visibilidad (quién puede ver qué) ---
        if (!$user->isSuperAdmin()) {
            $query->where(function ($subQuery) use ($user) {
                $subQuery->where('user_id', $user->id)
                        ->orWhere('agent_id', $user->id);
            });
        }

        // --- 4. Aplicar filtros del formulario ---
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
            $query->where('category_id', $request->category_id);
        }
        // El filtro por agente solo se aplica si el usuario es SuperAdmin
        if ($user->isSuperAdmin() && $request->filled('agent_id')) {
            $query->where('agent_id', $request->agent_id);
        }

        // --- 5. Ejecutar consulta y paginar ---
        $tickets = $query->with(['user', 'category', 'agent'])
                        ->latest()
                        ->paginate(15)
                        ->withQueryString(); // withQueryString() conserva los filtros en la paginación

        // --- 6. Devolver la vista con todos los datos necesarios ---
        return view('tickets.index', [
            'tickets' => $tickets,
            'categories' => $categories,
            'agents' => $agents,
            'filters' => $request->all() // Para rellenar el formulario con la búsqueda actual
        ]);
    }

    public function create()
    {
        // Se obtienen todas las categorías para pasarlas al menú desplegable
        $categories = TicketCategory::orderBy('name')->get();
        return view('tickets.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:Baja,Media,Alta',
            'attachment' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category_id' => 'required|exists:ticket_categories,id', // <-- 1. AÑADIR VALIDACIÓN
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
            'category_id' => $request->category_id, // <-- 2. AÑADIR CAMPO AL CREAR
            'status' => 'Abierto',
        ]);

        $ticket->statusHistories()->create([
            'user_id' => Auth::id(),
            'status' => 'Abierto'
        ]);

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

        // Permitir el acceso si el usuario es el creador, un SuperAdmin, O el agente asignado.
        if ($user->id !== $ticket->user_id && !$user->isSuperAdmin() && $user->id !== $ticket->agent_id) {
            abort(403, 'No tienes permiso para ver este ticket.');
        }

        $ticket->load(['user', 'agent', 'category', 'replies.user', 'statusHistories.user']);

        $agents = User::where('is_client', false)->orderBy('name')->get();

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

        $reply = $ticket->replies()->create([
            'user_id' => $user->id,
            'body' => $request->body,
            'is_internal' => $request->has('is_internal') && ($user->isSuperAdmin() || $user->id === $ticket->agent_id),
        ]);

        // --- LÓGICA DE NOTIFICACIÓN ACTUALIZADA ---

        // Si NO es una nota interna, se notifica al usuario o a los admins como antes.
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
        // Si SÍ es una nota interna, la comunicación es solo entre admins y el agente.
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

    /**
     * MÉTODO AÑADIDO PARA ACTUALIZAR EL ESTADO DEL TICKET
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        $user = Auth::user();

        // --- LÓGICA DE AUTORIZACIÓN CORREGIDA ---
        // Permitir si es un SuperAdmin O el agente asignado.
        if (!$user->isSuperAdmin() && $user->id !== $ticket->agent_id) {
            abort(403, 'No tienes permiso para cambiar el estado de este ticket.');
        }

        $request->validate([
            'status' => 'required|in:Abierto,En Proceso,Cerrado',
            'work_summary' => 'required_if:status,Cerrado|string|nullable',
            'closure_evidence' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // --- Lógica de Cierre con Aprobación ---
        $newStatus = $request->status;

        if ($request->status === 'Cerrado') {
            $evidencePath = null;
            if ($request->hasFile('closure_evidence')) {
                $evidencePath = $request->file('closure_evidence')->store('tickets/evidence', 's3');
            }

            $ticket->status = 'Pendiente de Aprobación';
            $ticket->closure_evidence_path = $evidencePath;
            $ticket->work_summary = $request->work_summary; // <-- GUARDAR EL RESUMEN
            $ticket->save();

            $ticket->statusHistories()->create(['user_id' => Auth::id(), 'status' => 'Pendiente de Aprobación']);
            
            $ticket->user->notify(new ClosureRequestNotification($ticket));
            return back()->with('success', 'Se ha solicitado la aprobación de cierre al usuario.');
        }
        
        $ticket->status = $request->status;
        $ticket->save();

        $ticket->statusHistories()->create(['user_id' => Auth::id(), 'status' => $request->status]);

        $ticket->user->notify(new TicketStatusUpdatedNotification($ticket));
        return back()->with('success', 'El estado del ticket ha sido actualizado.');
    }

    // AÑADE ESTE MÉTODO NUEVO
    public function approveClosure(Ticket $ticket)
    {
        // Solo el creador del ticket puede aprobar el cierre
        if (Auth::id() !== $ticket->user_id) {
            abort(403, 'No tienes permiso para aprobar el cierre de este ticket.');
        }

        // Solo se puede aprobar si está en el estado correcto
        if ($ticket->status !== 'Pendiente de Aprobación') {
            return back()->with('error', 'Este ticket no está esperando una aprobación.');
        }

        $ticket->status = 'Cerrado';
        $ticket->save();

        $ticket->statusHistories()->create([
            'user_id' => Auth::id(),
            'status' => 'Cerrado'
        ]);

        // NOTIFICAR A SUPERADMINS QUE EL TICKET FUE CERRADO
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

        // Valida que el agente a asignar no sea un cliente
        if ($agent->is_client) {
            return back()->with('error', 'No se puede asignar un ticket a un usuario cliente.');
        }

        $ticket->agent_id = $agent->id;
        $ticket->save();

        // Notificar al agente asignado
        $agent->notify(new AgentAssignedNotification($ticket));

        return back()->with('success', 'Ticket asignado a ' . $agent->name . ' exitosamente.');
    }

}