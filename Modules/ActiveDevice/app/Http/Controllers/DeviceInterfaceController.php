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
        $validated = $request->validate([
            'interfacable_id' => 'required|integer',
            'interfacable_type' => 'required|string',
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'speed' => 'nullable|string|max:255',
            'mac_address' => 'nullable|string|max:255',
            'status' => 'required|string|in:up,down,idle,error',
            'description' => 'nullable|string',
        ]);

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
