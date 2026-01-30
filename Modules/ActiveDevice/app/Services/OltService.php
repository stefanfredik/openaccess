<?php

namespace Modules\ActiveDevice\Services;

use Illuminate\Http\UploadedFile;
use Modules\ActiveDevice\Models\Olt;

class OltService extends BaseActiveDeviceService
{
    protected function getPhotoStorageFolder(): string
    {
        return 'olt-images';
    }

    protected function getPhotoAttribute(): string
    {
        return 'device_image';
    }

    /**
     * Create a new OLT with photo and service ports.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>|null  $servicePorts
     */
    public function create(array $data, ?UploadedFile $photo = null, ?array $servicePorts = null): Olt
    {
        $data = $this->prepareDataWithPhoto($data, $photo);

        $olt = Olt::create($data);

        if ($servicePorts) {
            $this->syncServicePorts($olt, $servicePorts, $data['company_id']);
        }

        return $olt;
    }

    /**
     * Update an OLT with photo and service ports.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>|null  $servicePorts
     */
    public function update(Olt $olt, array $data, ?UploadedFile $photo = null, ?array $servicePorts = null, ?int $companyId = null): Olt
    {
        if ($photo) {
            $this->deletePhoto($olt);
            $data = $this->prepareDataWithPhoto($data, $photo);
        }

        $olt->update($data);

        if ($servicePorts !== null && $companyId) {
            $olt->servicePorts()->delete();
            $this->syncServicePorts($olt, $servicePorts, $companyId);
        }

        return $olt;
    }

    /**
     * Delete an OLT and its associated photo.
     */
    public function delete(Olt $olt): bool
    {
        $this->deletePhoto($olt);

        return $olt->delete();
    }
}
