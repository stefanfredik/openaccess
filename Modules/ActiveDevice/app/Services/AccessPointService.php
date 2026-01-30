<?php

namespace Modules\ActiveDevice\Services;

use Illuminate\Http\UploadedFile;
use Modules\ActiveDevice\Models\AccessPoint;

class AccessPointService extends BaseActiveDeviceService
{
    protected function getPhotoStorageFolder(): string
    {
        return 'access-point-photos';
    }

    /**
     * Create a new Access Point with service ports.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>|null  $servicePorts
     */
    public function create(array $data, ?UploadedFile $photo = null, ?array $servicePorts = null): AccessPoint
    {
        $data = $this->prepareDataWithPhoto($data, $photo);

        $accessPoint = AccessPoint::create($data);

        if ($servicePorts) {
            $this->syncServicePorts($accessPoint, $servicePorts, $data['company_id']);
        }

        return $accessPoint;
    }

    /**
     * Update an Access Point with service ports.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>|null  $servicePorts
     */
    public function update(AccessPoint $accessPoint, array $data, ?UploadedFile $photo = null, ?array $servicePorts = null, ?int $companyId = null): AccessPoint
    {
        if ($photo) {
            $this->deletePhoto($accessPoint);
            $data = $this->prepareDataWithPhoto($data, $photo);
        }

        $accessPoint->update($data);

        if ($servicePorts !== null && $companyId) {
            $accessPoint->servicePorts()->delete();
            $this->syncServicePorts($accessPoint, $servicePorts, $companyId);
        }

        return $accessPoint;
    }

    /**
     * Delete an Access Point.
     */
    public function delete(AccessPoint $accessPoint): bool
    {
        $this->deletePhoto($accessPoint);

        return $accessPoint->delete();
    }
}
