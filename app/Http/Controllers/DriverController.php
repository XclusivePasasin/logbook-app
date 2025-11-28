<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    /**
     * Display a listing of drivers.
     */
    public function index(Request $request)
    {
        $query = Driver::query();

        // Filter by active status if provided
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $drivers = $query->orderBy('name')->get();

        return response()->json($drivers);
    }

    /**
     * Store a newly created driver.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'license_number' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $driver = Driver::create($validated);

        return response()->json([
            'driver' => $driver,
            'message' => 'Conductor creado exitosamente'
        ], 201);
    }

    /**
     * Display the specified driver.
     */
    public function show(Driver $driver)
    {
        return response()->json($driver->load('trips'));
    }

    /**
     * Update the specified driver.
     */
    public function update(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'license_number' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $driver->update($validated);

        return response()->json([
            'driver' => $driver,
            'message' => 'Conductor actualizado exitosamente'
        ]);
    }

    /**
     * Remove the specified driver.
     */
    public function destroy(Driver $driver)
    {
        $driver->delete();

        return response()->json([
            'message' => 'Conductor eliminado exitosamente'
        ]);
    }
}
