<?php

namespace Modules\PassiveDevice\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\PassiveDevice\Models\CableCore;
use Modules\PassiveDevice\Models\FiberSplice;
use Modules\PassiveDevice\Models\PortTermination;

class FiberService
{
    /**
     * Create a fusion splice between two fiber cores inside an enclosure.
     */
    public function spliceCores(CableCore $incoming, CableCore $outgoing, Model $enclosure, float $attenuation = 0.05)
    {
        return FiberSplice::create([
            'enclosure_type' => get_class($enclosure),
            'enclosure_id' => $enclosure->id,
            'incoming_core_id' => $incoming->id,
            'outgoing_core_id' => $outgoing->id,
            'attenuation' => $attenuation,
            'type' => 'fusion',
        ]);
    }

    /**
     * Terminate a fiber core into a device port (e.g., OLT Port).
     */
    public function terminateCore(CableCore $core, Model $port)
    {
        return PortTermination::create([
            'port_type' => get_class($port),
            'port_id' => $port->id,
            'core_id' => $core->id,
        ]);
    }

    /**
     * Trace the signal path starting from a given core.
     * Returns an ordered array of the path elements.
     */
    public function traceSignal(CableCore $startCore)
    {
        $path = [];
        $visited = [];

        $this->traverse($startCore, $path, $visited);

        return $path;
    }

    private function traverse(CableCore $currentCore, &$path, &$visited)
    {
        if (in_array($currentCore->id, $visited)) {
            return;
        }
        $visited[] = $currentCore->id;

        // Add current core info to path
        $path[] = [
            'type' => 'core',
            'id' => $currentCore->id,
            'name' => "Core {$currentCore->core_number} ({$currentCore->color})",
            'cable' => $currentCore->cable->name ?? 'Unknown Cable',
        ];

        // 1. Check for Termination (End of line)
        $termination = $currentCore->termination;
        if ($termination) {
            $path[] = [
                'type' => 'termination',
                // Assuming port has 'device' relation or 'name'
                'device_type' => $termination->port_type,
                'device_id' => $termination->port_id,
            ];

            return; // Stop tracing
        }

        // 2. Check connections
        // Connection A->B
        $spliceOut = FiberSplice::where('incoming_core_id', $currentCore->id)->first();
        if ($spliceOut) {
            $path[] = [
                'type' => 'splice',
                'enclosure_type' => $spliceOut->enclosure_type,
                'enclosure_id' => $spliceOut->enclosure_id,
            ];
            if ($spliceOut->outgoingCore) {
                $this->traverse($spliceOut->outgoingCore, $path, $visited);
            }

            return;
        }

        // Connection B->A (Backward traversal or Ring)
        $spliceIn = FiberSplice::where('outgoing_core_id', $currentCore->id)->first();
        if ($spliceIn) {
            $path[] = [
                'type' => 'splice',
                'enclosure_type' => $spliceIn->enclosure_type,
                'enclosure_id' => $spliceIn->enclosure_id,
            ];
            if ($spliceIn->incomingCore) {
                $this->traverse($spliceIn->incomingCore, $path, $visited);
            }

            return;
        }
    }
}
