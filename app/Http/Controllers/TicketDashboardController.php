<?php
namespace App\Http\Controllers;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TicketDashboardController extends Controller
{
    public function index()
    {
        return view('tickets.dashboard');
    }

    public function getChartData()
    {
        // Métrica 1: Tickets por día (sin cambios)
        $ticketsPerDay = Ticket::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')->orderBy('date', 'asc')->get();

        // Métrica 2: Tickets por estatus (sin cambios)
        $ticketsByStatus = Ticket::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')->pluck('count', 'status');
        
        // Métrica 3: Tickets por categoría (sin cambios)
        $ticketsByCategory = Ticket::join('ticket_sub_categories', 'tickets.ticket_sub_category_id', '=', 'ticket_sub_categories.id')
            ->join('ticket_categories', 'ticket_sub_categories.ticket_category_id', '=', 'ticket_categories.id')
            ->select('ticket_categories.name', DB::raw('count(tickets.id) as count'))
            ->groupBy('ticket_categories.name')->pluck('count', 'name');

        // --- NUEVAS MÉTRICAS ---

        // Métrica 4: Tickets por prioridad
        $ticketsByPriority = Ticket::select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')->pluck('count', 'priority');

        // Métrica 5: Tickets cerrados por agente
        $ticketsByAgent = Ticket::where('status', 'Cerrado')->whereNotNull('agent_id')
            ->join('users', 'tickets.agent_id', '=', 'users.id')
            ->select('users.name', DB::raw('count(*) as count'))
            ->groupBy('users.name')->orderBy('count', 'desc')->limit(10)->pluck('count', 'name');
            
        // Métrica 6: Tiempo promedio de resolución (en horas)
        $avgResolutionTime = Ticket::where('status', 'Cerrado')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours'))
            ->value('avg_hours');

        return response()->json([
            'ticketsPerDay' => ['labels' => $ticketsPerDay->pluck('date'), 'data' => $ticketsPerDay->pluck('count')],
            'ticketsByStatus' => ['labels' => $ticketsByStatus->keys(), 'data' => $ticketsByStatus->values()],
            'ticketsByCategory' => ['labels' => $ticketsByCategory->keys(), 'data' => $ticketsByCategory->values()],
            'ticketsByPriority' => ['labels' => $ticketsByPriority->keys(), 'data' => $ticketsByPriority->values()],
            'ticketsByAgent' => ['labels' => $ticketsByAgent->keys(), 'data' => $ticketsByAgent->values()],
            'avgResolutionTime' => round($avgResolutionTime, 1),
        ]);
    }
}