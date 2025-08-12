<?php

namespace App\Http\Controllers\CustomerService;

use App\Http\Controllers\Controller;
use App\Models\CsCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerController extends Controller
{
    protected $channels = ['Corporate', 'Especialista', 'Moderno', 'On', 'On trade', 'POSM', 'Private'];

    public function index()
    {
        $customers = $this->getFilteredCustomers(new Request())->paginate(25);
        return view('customer-service.customers.index', ['customers' => $customers, 'channels' => $this->channels]);
    }

    public function filter(Request $request)
    {
        $customers = $this->getFilteredCustomers($request)->paginate(25);
        return response()->json(['table' => view('customer-service.customers.partials.table', compact('customers'))->render()]);
    }

    public function create()
    {
        return view('customer-service.customers.create', ['channels' => $this->channels]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'client_id' => ['required', 'string', 'max:255', Rule::unique('cs_customers')->where('channel', $request->channel)],
            'name' => 'required|string|max:255',
            'channel' => ['required', Rule::in($this->channels)],
        ]);

        CsCustomer::create($validatedData + ['created_by_user_id' => Auth::id()]);
        return redirect()->route('customer-service.customers.index')->with('success', 'Cliente creado exitosamente.');
    }

    public function edit(CsCustomer $customer)
    {
        return view('customer-service.customers.edit', ['customer' => $customer, 'channels' => $this->channels]);
    }

    public function update(Request $request, CsCustomer $customer)
    {
        $validatedData = $request->validate([
            'client_id' => ['required', 'string', 'max:255', Rule::unique('cs_customers')->where('channel', $request->channel)->ignore($customer->id)],
            'name' => 'required|string|max:255',
            'channel' => ['required', Rule::in($this->channels)],
        ]);

        $customer->update($validatedData + ['updated_by_user_id' => Auth::id()]);
        return redirect()->route('customer-service.customers.index')->with('success', 'Cliente actualizado exitosamente.');
    }

    public function destroy(CsCustomer $customer)
    {
        $customer->delete();
        return redirect()->route('customer-service.customers.index')->with('success', 'Cliente eliminado exitosamente.');
    }

    public function exportCsv(Request $request)
    {
        $customers = $this->getFilteredCustomers($request)->get();
        $fileName = "export_clientes_" . date('Y-m-d') . ".csv";
        $headers = [ "Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=$fileName" ];

        $callback = function() use ($customers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID Cliente', 'Nombre', 'Canal', 'Creado por', 'Fecha Creacion']);
            foreach ($customers as $customer) {
                fputcsv($file, [$customer->client_id, $customer->name, $customer->channel, $customer->createdBy->name, $customer->created_at->format('Y-m-d')]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function importCsv(Request $request)
    {
        $request->validate(['csv_file' => 'required|mimes:csv,txt']);
        $path = $request->file('csv_file')->getRealPath();
        
        // --- INICIA CORRECCIÓN: Se convierte el archivo a UTF-8 ---
        $fileContent = file_get_contents($path);
        // Detecta la codificación actual y la convierte a UTF-8
        $utf8Content = mb_convert_encoding($fileContent, 'UTF-8', mb_detect_encoding($fileContent, 'UTF-8, ISO-8859-1', true));
        
        // Se crea un archivo temporal en memoria con el contenido corregido
        $file = fopen("php://memory", 'r+');
        fwrite($file, $utf8Content);
        rewind($file);
        // --- TERMINA CORRECCIÓN ---

        fgetcsv($file); // Omitir cabecera

        while (($row = fgetcsv($file, 1000, ",")) !== FALSE) {
            if (count(array_filter($row)) == 0) continue;

            $channel = trim($row[2]);
            if (!in_array($channel, $this->channels)) continue;

            CsCustomer::updateOrCreate(
                ['client_id' => trim($row[0]), 'channel' => $channel],
                ['name' => trim($row[1]), 'created_by_user_id' => Auth::id()]
            );
        }
        fclose($file);
        return redirect()->route('customer-service.customers.index')->with('success', 'Archivo CSV importado exitosamente.');
    }

    public function downloadTemplate()
    {
        $headers = [ "Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=plantilla_clientes.csv" ];
        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID Cliente', 'Nombre', 'Canal']);
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    private function getFilteredCustomers(Request $request)
    {
        $query = CsCustomer::with('createdBy');
        if ($request->filled('search') && strlen($request->search) > 1) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(fn($q) => $q->where('client_id', 'like', $searchTerm)->orWhere('name', 'like', $searchTerm));
        }
        if ($request->filled('channel')) { $query->where('channel', $request->channel); }
        return $query->orderBy('name', 'asc');
    }

    public function dashboard()
    {
        // Gráfico 1: Clientes por Canal
        $customersByChannel = CsCustomer::select('channel', DB::raw('count(*) as total'))
            ->groupBy('channel')
            ->pluck('total', 'channel');
        
        // Gráfico 2: Clientes creados en los últimos 12 meses
        $recentCustomers = CsCustomer::where('created_at', '>=', Carbon::now()->subYear())
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->get([
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            ]);

        // Gráfico 3: Top 10 Clientes (por nombre)
        $topCustomers = CsCustomer::select('name', DB::raw('count(*) as total'))
            ->groupBy('name')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->pluck('total', 'name');

        $chartData = [
            'customersByChannel' => [
                'labels' => $customersByChannel->keys(),
                'data' => $customersByChannel->values(),
            ],
            'recentCustomers' => [
                'labels' => $recentCustomers->pluck('month'),
                'data' => $recentCustomers->pluck('count'),
            ],
            'topCustomers' => [
                'labels' => $topCustomers->keys(),
                'data' => $topCustomers->values(),
            ]
        ];

        return view('customer-service.customers.dashboard', compact('chartData'));
    }

}
