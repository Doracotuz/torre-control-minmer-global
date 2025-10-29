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
        $ticketsPerDay = Ticket::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')->orderBy('date', 'asc')->get();

        $ticketsByStatus = Ticket::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')->pluck('count', 'status');
        
        $ticketsByCategory = Ticket::join('ticket_sub_categories', 'tickets.ticket_sub_category_id', '=', 'ticket_sub_categories.id')
            ->join('ticket_categories', 'ticket_sub_categories.ticket_category_id', '=', 'ticket_categories.id')
            ->select('ticket_categories.name', DB::raw('count(tickets.id) as count'))
            ->groupBy('ticket_categories.name')->pluck('count', 'name');

        $ticketsByPriority = Ticket::select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')->pluck('count', 'priority');

        $ticketsByAgent = Ticket::where('status', 'Cerrado')->whereNotNull('agent_id')
            ->join('users', 'tickets.agent_id', '=', 'users.id')
            ->select('users.name', DB::raw('count(*) as count'))
            ->groupBy('users.name')->orderBy('count', 'desc')->limit(10)->pluck('count', 'name');
            
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