<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function salesReport(Request $request)
    {
        $query = Invoice::select(
            DB::raw('DATE(issue_date) as date'),
            DB::raw('COUNT(*) as invoice_count'),
            DB::raw('SUM(total_amount) as total_sales'),
            DB::raw('AVG(total_amount) as average_sale')
        )
        ->when($request->date_from, function ($q) use ($request) {
            return $q->where('issue_date', '>=', $request->date_from);
        })
        ->when($request->date_to, function ($q) use ($request) {
            return $q->where('issue_date', '<=', $request->date_to);
        })
        ->groupBy('date')
        ->orderBy('date');

        $report = $request->has('paginate') 
            ? $query->paginate(30)
            : $query->get();

        return response()->json($report);
    }

    public function clientReport(Request $request)
    {
        $report = DB::table('clients')
            ->leftJoin('invoices', 'clients.id', '=', 'invoices.client_id')
            ->select(
                'clients.id',
                'clients.name',
                DB::raw('COUNT(invoices.id) as invoice_count'),
                DB::raw('COALESCE(SUM(invoices.total_amount), 0) as total_amount'),
                DB::raw('COALESCE(AVG(invoices.total_amount), 0) as average_amount')
            )
            ->groupBy('clients.id', 'clients.name')
            ->orderBy('total_amount', 'desc')
            ->paginate(20);

        return response()->json($report);
    }

    public function dashboardStats()
    {
        $stats = [
            'total_invoices' => Invoice::count(),
            'total_revenue' => Invoice::where('status', 'paid')->sum('total_amount'),
            'pending_invoices' => Invoice::whereIn('status', ['draft', 'sent'])->count(),
            'overdue_invoices' => Invoice::where('status', 'overdue')->count(),
        ];

        return response()->json($stats);
    }
}