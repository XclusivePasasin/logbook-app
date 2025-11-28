<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TripController extends Controller
{
    /**
     * Display a listing of trips.
     */
    public function index(Request $request)
    {
        $query = Trip::with(['driver', 'creator']);

        // Search by origin or destination
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('origin', 'like', '%' . $search . '%')
                  ->orWhere('destination', 'like', '%' . $search . '%');
            });
        }

        // Filter by specific date
        if ($request->has('date')) {
            $query->whereDate('trip_date', $request->date);
        }

        // Filter by month (format: YYYY-MM)
        if ($request->has('month')) {
            $yearMonth = $request->month;
            $year = substr($yearMonth, 0, 4);
            $month = substr($yearMonth, 5, 2);
            $query->whereYear('trip_date', $year)
                  ->whereMonth('trip_date', $month);
        }

        // Filter by driver
        if ($request->has('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        // Filter by payment method
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $trips = $query->orderBy('trip_date', 'desc')
                       ->orderBy('trip_time', 'desc')
                       ->paginate($request->get('per_page', 15));

        return response()->json($trips);
    }

    /**
     * Store a newly created trip.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'trip_date' => 'required|date',
            'trip_time' => 'required|date_format:H:i',
            'passengers' => 'required|integer|min:1',
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'payment_method' => 'required|in:E,CH,TJ',
            'equipment_number' => 'nullable|string|max:255',
            'is_ida' => 'boolean',
            'is_vuelta' => 'boolean',
            'amount' => 'required|numeric|min:0',
            'driver_id' => 'required|exists:drivers,id',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = $request->user()->id;

        $trip = Trip::create($validated);

        return response()->json([
            'trip' => $trip->load(['driver', 'creator']),
            'message' => 'Viaje registrado exitosamente'
        ], 201);
    }

    /**
     * Display the specified trip.
     */
    public function show(Trip $trip)
    {
        return response()->json($trip->load(['driver', 'creator']));
    }

    /**
     * Update the specified trip.
     */
    public function update(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'trip_date' => 'sometimes|required|date',
            'trip_time' => 'sometimes|required|date_format:H:i',
            'passengers' => 'sometimes|required|integer|min:1',
            'origin' => 'sometimes|required|string|max:255',
            'destination' => 'sometimes|required|string|max:255',
            'payment_method' => 'sometimes|required|in:E,CH,TJ',
            'equipment_number' => 'nullable|string|max:255',
            'is_ida' => 'boolean',
            'is_vuelta' => 'boolean',
            'amount' => 'sometimes|required|numeric|min:0',
            'driver_id' => 'sometimes|required|exists:drivers,id',
            'notes' => 'nullable|string',
        ]);

        $trip->update($validated);

        return response()->json([
            'trip' => $trip->load(['driver', 'creator']),
            'message' => 'Viaje actualizado exitosamente'
        ]);
    }

    /**
     * Remove the specified trip.
     */
    public function destroy(Trip $trip)
    {
        $trip->delete();

        return response()->json([
            'message' => 'Viaje eliminado exitosamente'
        ]);
    }

    /**
     * Get trip statistics and reports.
     */
    public function report(Request $request)
    {
        $query = Trip::query();

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('trip_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('trip_date', '<=', $request->end_date);
        }

        $stats = [
            'total_trips' => $query->count(),
            'total_amount' => $query->sum('amount'),
            'total_passengers' => $query->sum('passengers'),
            'by_payment_method' => $query->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
                ->groupBy('payment_method')
                ->get(),
            'by_driver' => Trip::with('driver')
                ->select('driver_id', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
                ->when($request->has('start_date'), fn($q) => $q->where('trip_date', '>=', $request->start_date))
                ->when($request->has('end_date'), fn($q) => $q->where('trip_date', '<=', $request->end_date))
                ->groupBy('driver_id')
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Download PDF report for a specific date.
     */
    public function downloadReport(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        
        // Get trips for the specified date
        $trips = Trip::with(['driver', 'creator'])
            ->whereDate('trip_date', $date)
            ->orderBy('trip_time', 'asc')
            ->get();

        // Calculate statistics
        $stats = [
            'total_trips' => $trips->count(),
            'total_amount' => $trips->sum('amount'),
            'total_passengers' => $trips->sum('passengers'),
            'by_payment_method' => Trip::select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
                ->whereDate('trip_date', $date)
                ->groupBy('payment_method')
                ->get(),
            'by_driver' => Trip::with('driver')
                ->select('driver_id', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
                ->whereDate('trip_date', $date)
                ->groupBy('driver_id')
                ->get(),
        ];

        // Format date for display
        $formattedDate = \Carbon\Carbon::parse($date)->format('d/m/Y');

        // Generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.trips-report', [
            'trips' => $trips,
            'stats' => $stats,
            'date' => $formattedDate,
        ]);

        // Download PDF
        return $pdf->download('reporte-viajes-' . $date . '.pdf');
    }
}
