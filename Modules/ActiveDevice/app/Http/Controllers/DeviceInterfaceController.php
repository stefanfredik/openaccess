<?php

namespace Modules\ActiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\ActiveDevice\Models\DeviceInterface;

class DeviceInterfaceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('activedevice::index');
    }

    /**
     * Get interfaces for a specific device.
     */
    public function getByDevice(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'id' => 'required|integer',
        ]);

        $interfaces = DeviceInterface::where('interfacable_type', $validated['type'])
            ->where('interfacable_id', $validated['id'])
            ->get();

        return response()->json($interfaces);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('activedevice::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $commonRules = [
            'interfacable_id' => 'required|integer',
            'interfacable_type' => 'required|string',
            'type' => 'nullable|string|max:255',
            'speed' => 'nullable|string|max:255',
            'mac_address' => 'nullable|string|max:255',
            'status' => 'required|string|in:up,down,idle,error',
            'description' => 'nullable|string',
        ];

        if ($request->boolean('is_bulk')) {
            $validated = $request->validate(array_merge($commonRules, [
                'prefix' => 'nullable|string|max:255',
                'start_number' => 'required|integer|min:0',
                'count' => 'required|integer|min:1|max:48',
            ]));

            $validated['company_id'] = $request->user()->company_id;
            $prefix = $validated['prefix'] ?? '';
            $start = $validated['start_number'];
            $count = $validated['count'];

            for ($i = 0; $i < $count; $i++) {
                $data = $validated;
                unset($data['prefix'], $data['start_number'], $data['count']);
                $data['name'] = $prefix.($start + $i);
                DeviceInterface::create($data);
            }

            return back()->with('success', "$count Interfaces created successfully.");
        }

        $validated = $request->validate(array_merge($commonRules, [
            'name' => 'required|string|max:255',
        ]));

        $validated['company_id'] = $request->user()->company_id;

        DeviceInterface::create($validated);

        return back()->with('success', 'Interface created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DeviceInterface $interface): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'speed' => 'nullable|string|max:255',
            'mac_address' => 'nullable|string|max:255',
            'status' => 'required|string|in:up,down,idle,error',
            'description' => 'nullable|string',
        ]);

        $interface->update($validated);

        return back()->with('success', 'Interface updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeviceInterface $interface): RedirectResponse
    {
        $interface->delete();

        return back()->with('success', 'Interface deleted successfully.');
    }
}
