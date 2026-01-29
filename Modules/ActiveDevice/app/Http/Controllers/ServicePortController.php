<?php

namespace Modules\ActiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Modules\ActiveDevice\Http\Requests\StoreServicePortRequest;
use Modules\ActiveDevice\Models\ServicePort;

class ServicePortController extends Controller
{
    public function store(StoreServicePortRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['company_id'] = $request->user()->company_id;

        ServicePort::create($data);

        return back()->with('success', 'Service port added successfully.');
    }

    public function destroy(ServicePort $servicePort): RedirectResponse
    {
        $servicePort->delete();

        return back()->with('success', 'Service port deleted successfully.');
    }
}
