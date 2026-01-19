<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getStats()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $endOfMonth = $currentMonth::now()->endOfMonth();

        $totalTickets = Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])->count();

        $activeTickets = Ticket::whereBetween('created_at', [$currentMonth, $endOfMonth])
            ->where('status', '!=', 'resolved')
            ->count();

        $openTickets = Ticket::where('status', 'open')->whereBetween('created_at', [$currentMonth, $endOfMonth])->count();

        $inProgressTickets = Ticket::where('status', 'in_progress')->whereBetween('created_at', [$currentMonth, $endOfMonth])->count();

        $resolvedTickets = Ticket::where('status', 'resolved')->whereBetween('created_at', [$currentMonth, $endOfMonth])->count();   
        
        $avgResolutionTime = Ticket::where('status', 'resolved')
            ->whereBetween('created_at', [$currentMonth, $endOfMonth])
            ->whereNotNull('completed_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, completed_at)) as avg_time'))
            ->value('avg_time') ?? 0;

        
        $statusDistribution = [
            'open' => Ticket::where('status', 'open')->whereBetween('created_at', [$currentMonth, $endOfMonth])->count(),
            'in_progress' => Ticket::where('status', 'in_progress')->whereBetween('created_at', [$currentMonth, $endOfMonth])->count(),
            'resolved' => Ticket::where('status', 'resolved')->whereBetween('created_at', [$currentMonth, $endOfMonth])->count(),
            'rejected' => Ticket::where('status', 'rejected')->whereBetween('created_at', [$currentMonth, $endOfMonth])->count(),
        ];

        $dashboardData = [
            'total_tickets' => $totalTickets,
            'active_tickets' => $activeTickets,
            'open_tickets' => $openTickets,
            'in_progress_tickets' => $inProgressTickets,
            'resolved_tickets' => $resolvedTickets,
            'avg_resolution_time' => $avgResolutionTime,
            'status_distribution' => $statusDistribution,
        ];
        return response()->json([
            'message' => 'Dashboard stats fetched successfully',
            'data' => $dashboardData,
        ], 200);
    }
}
