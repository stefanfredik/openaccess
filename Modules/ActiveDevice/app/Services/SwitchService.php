<?php

namespace Modules\ActiveDevice\Services;

use Illuminate\Http\UploadedFile;
use Modules\ActiveDevice\Models\AdSwitch;

class SwitchService extends BaseActiveDeviceService
{
    protected function getPhotoStorageFolder(): string
    {
        return 'switch-photos';
    }

    /**
     * Create a new switch with photo and service ports.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>|null  $servicePorts
     */
    public function create(array $data, ?UploadedFile $photo = null, ?array $servicePorts = null): AdSwitch
    {
        $data = $this->prepareDataWithPhoto($data, $photo);

        $switch = AdSwitch::create($data);

        if ($servicePorts) {
            $this->syncServicePorts($switch, $servicePorts, $data['company_id']);
        }

        return $switch;
    }

    /**
     * Update a switch with photo and service ports.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>|null  $servicePorts
     */
    public function update(AdSwitch $switch, array $data, ?UploadedFile $photo = null, ?array $servicePorts = null, ?int $companyId = null): AdSwitch
    {
        if ($photo) {
            $this->deletePhoto($switch);
            $data = $this->prepareDataWithPhoto($data, $photo);
        }

        $switch->update($data);

        if ($servicePorts !== null && $companyId) {
            $switch->servicePorts()->delete();
            $this->syncServicePorts($switch, $servicePorts, $companyId);
        }

        return $switch;
    }

    /**
     * Delete a switch and its associated photo.
     */
    public function delete(AdSwitch $switch): bool
    {
        $this->deletePhoto($switch);

        return $switch->delete();
    }
}
