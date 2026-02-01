<?php

namespace Modules\PassiveDevice\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\PassiveDevice\Models\CableCore;
use Modules\PassiveDevice\Models\JointBox;
use Modules\PassiveDevice\Models\Odf;
use Modules\PassiveDevice\Models\Odp;
use Modules\PassiveDevice\Services\FiberService;

class SplicingController extends Controller
{
    public function __construct(protected FiberService $fiberService) {}

    public function splice(Request $request)
    {
        $request->validate([
            'incoming_core_id' => 'required|exists:pd_cable_cores,id',
            'outgoing_core_id' => 'required|exists:pd_cable_cores,id',
            'enclosure_type' => 'required|string', // e.g., 'joint_box', 'odp'
            'enclosure_id' => 'required|integer',
        ]);

        $incoming = CableCore::find($request->incoming_core_id);
        $outgoing = CableCore::find($request->outgoing_core_id);

        $modelClass = $this->resolveEnclosureModel($request->enclosure_type);
        $enclosure = $modelClass::findOrFail($request->enclosure_id);

        $this->fiberService->spliceCores($incoming, $outgoing, $enclosure);

        return response()->json(['message' => 'Spliced successfully']);
    }

    public function trace(Request $request, $coreId)
    {
        $core = CableCore::findOrFail($coreId);
        $path = $this->fiberService->traceSignal($core);

        return response()->json(['path' => $path]);
    }

    public function getEnclosureDetails(Request $request)
    {
        $request->validate([
            'enclosure_type' => 'required|string',
            'enclosure_id' => 'required|integer',
        ]);

        $modelClass = $this->resolveEnclosureModel($request->enclosure_type);
        $enclosure = $modelClass::with('area')->findOrFail($request->enclosure_id);

        // Fetch cables connected to this enclosure
        $cables = \Modules\PassiveDevice\Models\Cable::query()
            ->where(function ($q) use ($enclosure, $modelClass) {
                $q->where('start_node_type', $modelClass)
                    ->where('start_node_id', $enclosure->id);
            })
            ->orWhere(function ($q) use ($enclosure, $modelClass) {
                $q->where('end_node_type', $modelClass)
                    ->where('end_node_id', $enclosure->id);
            })
            ->with(['tubes.cores.incomingSplices', 'tubes.cores.outgoingSplices', 'tubes.cores.termination'])
            ->get();

        return response()->json([
            'enclosure' => $enclosure,
            'cables' => $cables,
        ]);
    }

    private function resolveEnclosureModel($type)
    {
        return match ($type) {
            'joint_box' => JointBox::class,
            'odp' => Odp::class,
            'odf' => Odf::class,
            default => throw new \Exception("Unknown enclosure type: $type")
        };
    }
}
