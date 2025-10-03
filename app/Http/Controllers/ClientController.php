<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    /**
     * Display a listing of the clients.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Client::class);

        $query = Client::withCount(['invoices as total_invoices', 'invoices as paid_invoices' => function ($query) {
            $query->where('status', 'paid');
        }, 'invoices as pending_invoices' => function ($query) {
            $query->whereIn('status', ['draft', 'sent']);
        }])
        ->withSum('invoices as total_revenue', 'total_amount')
        ->with(['invoices' => function ($query) {
            $query->select('client_id', 'status', 'total_amount')
                  ->latest()
                  ->limit(5);
        }]);

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('tax_number', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && $request->status) {
            if ($request->status === 'active') {
                $query->has('invoices');
            } elseif ($request->status === 'inactive') {
                $query->doesntHave('invoices');
            }
        }

        // Sort options
        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        $allowedSortFields = ['name', 'email', 'created_at', 'total_revenue', 'total_invoices'];
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->latest();
        }

        $clients = $query->paginate($request->get('per_page', 15));

        return response()->json($clients);
    }

    /**
     * Store a newly created client in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Client::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string|max:50',
            'company_name' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $client = Client::create($validated);

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($client)
                ->withProperties(['attributes' => $validated])
                ->log('created');

            DB::commit();

            return response()->json([
                'message' => 'Client created successfully.',
                'client' => $client->loadCount(['invoices as total_invoices'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create client.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Display the specified client.
     */
    public function show(Client $client)
    {
        $this->authorize('view', $client);

        $client->load([
            'invoices' => function ($query) {
                $query->select(['id', 'client_id', 'invoice_number', 'issue_date', 'due_date', 'total_amount', 'status'])
                      ->latest()
                      ->limit(10);
            },
            'invoices.user:id,name'
        ]);

        $stats = [
            'total_invoices' => $client->invoices()->count(),
            'total_revenue' => $client->invoices()->sum('total_amount'),
            'paid_invoices' => $client->invoices()->where('status', 'paid')->count(),
            'pending_invoices' => $client->invoices()->whereIn('status', ['draft', 'sent'])->count(),
            'overdue_invoices' => $client->invoices()->where('status', 'overdue')->count(),
            'average_invoice_amount' => $client->invoices()->avg('total_amount'),
        ];

        return response()->json([
            'client' => $client,
            'stats' => $stats,
            'recent_invoices' => $client->invoices
        ]);
    }

    /**
     * Update the specified client in storage.
     */
    public function update(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('clients')->ignore($client->id),
            ],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string|max:50',
            'company_name' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $oldAttributes = $client->getAttributes();
            $client->update($validated);

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($client)
                ->withProperties([
                    'old' => $oldAttributes,
                    'attributes' => $validated
                ])
                ->log('updated');

            DB::commit();

            return response()->json([
                'message' => 'Client updated successfully.',
                'client' => $client->fresh(['invoices' => function ($query) {
                    $query->latest()->limit(5);
                }])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update client.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Remove the specified client from storage.
     */
    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);

        // Check if client has invoices
        if ($client->invoices()->exists()) {
            return response()->json([
                'message' => 'Cannot delete client with existing invoices. Please delete or reassign the invoices first.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            $clientName = $client->name;
            $client->delete();

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->withProperties(['client' => $clientName])
                ->log('deleted');

            DB::commit();

            return response()->json([
                'message' => 'Client deleted successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete client.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get client statistics for dashboard.
     */
    public function stats()
    {
        $this->authorize('viewAny', Client::class);

        $stats = [
            'total_clients' => Client::count(),
            'active_clients' => Client::has('invoices')->count(),
            'new_this_month' => Client::whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count(),
            'top_clients' => Client::withSum('invoices as total_revenue', 'total_amount')
                ->has('invoices')
                ->orderBy('total_revenue', 'desc')
                ->limit(5)
                ->get(['id', 'name', 'email']),
        ];

        return response()->json($stats);
    }

    /**
     * Search clients by name or email.
     */
    public function search(Request $request)
    {
        $this->authorize('viewAny', Client::class);

        $request->validate([
            'query' => 'required|string|min:2',
        ]);

        $clients = Client::where('name', 'like', "%{$request->query}%")
            ->orWhere('email', 'like', "%{$request->query}%")
            ->select(['id', 'name', 'email', 'phone'])
            ->limit(10)
            ->get();

        return response()->json($clients);
    }

    /**
     * Get client's invoices.
     */
    public function invoices(Client $client, Request $request)
    {
        $this->authorize('view', $client);

        $query = $client->invoices()
            ->with(['user:id,name', 'items'])
            ->withSum('payments as paid_amount', 'amount');

        // Status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->has('date_from') && $request->date_from) {
            $query->where('issue_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('issue_date', '<=', $request->date_to);
        }

        $invoices = $query->latest()
            ->paginate($request->get('per_page', 10));

        return response()->json($invoices);
    }

    /**
     * Bulk delete clients.
     */
    public function bulkDestroy(Request $request)
    {
        $this->authorize('delete', Client::class);

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:clients,id',
        ]);

        $clients = Client::whereIn('id', $request->ids)
            ->withCount('invoices')
            ->get();

        $clientsWithInvoices = $clients->filter(fn($client) => $client->invoices_count > 0);

        if ($clientsWithInvoices->isNotEmpty()) {
            return response()->json([
                'message' => 'Some clients have invoices and cannot be deleted.',
                'clients_with_invoices' => $clientsWithInvoices->pluck('name'),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $deletedCount = Client::whereIn('id', $request->ids)->delete();

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->withProperties(['count' => $deletedCount])
                ->log('bulk_deleted_clients');

            DB::commit();

            return response()->json([
                'message' => "{$deletedCount} clients deleted successfully.",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete clients.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Export clients to CSV or Excel.
     */
    public function export(Request $request)
    {
        $this->authorize('viewAny', Client::class);

        $request->validate([
            'format' => 'nullable|in:csv,xlsx',
        ]);

        $format = $request->get('format', 'csv');

        $clients = Client::withCount('invoices')
            ->withSum('invoices as total_revenue', 'total_amount')
            ->get();

        if ($format === 'csv') {
            return $this->exportToCsv($clients);
        }

        return $this->exportToExcel($clients);
    }

    /**
     * Export clients to CSV.
     */
    private function exportToCsv($clients)
    {
        $fileName = 'clients-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($clients) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

            // Headers
            fputcsv($file, [
                'Name',
                'Email',
                'Phone',
                'Company',
                'Tax Number',
                'Total Invoices',
                'Total Revenue',
                'Created At'
            ]);

            // Data
            foreach ($clients as $client) {
                fputcsv($file, [
                    $client->name,
                    $client->email,
                    $client->phone ?? '',
                    $client->company_name ?? '',
                    $client->tax_number ?? '',
                    $client->invoices_count,
                    $client->total_revenue ?? 0,
                    $client->created_at->format('Y-m-d'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export clients to Excel (placeholder - you can use Laravel Excel package).
     */
    private function exportToExcel($clients)
    {
        // For Excel export, you would typically use Maatwebsite/Laravel-Excel
        // This is a simplified version that returns JSON
        // In a real application, implement proper Excel export

        return response()->json([
            'message' => 'Excel export would be implemented with Laravel Excel package',
            'clients' => $clients
        ]);
    }

    /**
     * Import clients from CSV.
     */
    public function import(Request $request)
    {
        $this->authorize('create', Client::class);

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:1024',
        ]);

        try {
            DB::beginTransaction();

            $file = $request->file('file');
            $path = $file->getRealPath();
            $data = array_map('str_getcsv', file($path));

            // Remove header
            array_shift($data);

            $imported = 0;
            $errors = [];

            foreach ($data as $index => $row) {
                try {
                    if (count($row) < 2) continue;

                    $clientData = [
                        'name' => $row[0] ?? null,
                        'email' => $row[1] ?? null,
                        'phone' => $row[2] ?? null,
                        'company_name' => $row[3] ?? null,
                        'tax_number' => $row[4] ?? null,
                        'address' => $row[5] ?? null,
                    ];

                    // Validate required fields
                    if (empty($clientData['name']) || empty($clientData['email'])) {
                        $errors[] = "Row {$index}: Missing required fields (name or email)";
                        continue;
                    }

                    // Check for duplicate email
                    if (Client::where('email', $clientData['email'])->exists()) {
                        $errors[] = "Row {$index}: Client with email {$clientData['email']} already exists";
                        continue;
                    }

                    Client::create($clientData);
                    $imported++;

                } catch (\Exception $e) {
                    $errors[] = "Row {$index}: " . $e->getMessage();
                }
            }

            DB::commit();

            return response()->json([
                'message' => "Successfully imported {$imported} clients.",
                'errors' => $errors,
                'imported_count' => $imported,
                'error_count' => count($errors),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to import clients.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}