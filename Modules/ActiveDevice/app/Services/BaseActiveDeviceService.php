<?php

namespace Modules\ActiveDevice\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Base service for Active Device operations.
 * Provides common functionality for Router, Switch, OLT, ONT, and AccessPoint.
 */
abstract class BaseActiveDeviceService
{
    /**
     * Get the storage folder for photos.
     */
    abstract protected function getPhotoStorageFolder(): string;

    /**
     * Get the photo attribute name on the model.
     */
    protected function getPhotoAttribute(): string
    {
        return 'photo';
    }

    /**
     * Store a photo and return the path.
     */
    protected function storePhoto(UploadedFile $photo): string
    {
        return $photo->store($this->getPhotoStorageFolder(), 'public');
    }

    /**
     * Delete a device's photo if it exists.
     */
    protected function deletePhoto(Model $device): void
    {
        $photoAttribute = $this->getPhotoAttribute();

        if ($device->{$photoAttribute}) {
            Storage::disk('public')->delete($device->{$photoAttribute});
        }
    }

    /**
     * Sync service ports for a device.
     *
     * @param  array<int, array<string, mixed>>  $servicePorts
     */
    protected function syncServicePorts(Model $device, array $servicePorts, int $companyId): void
    {
        foreach ($servicePorts as $portData) {
            $device->servicePorts()->create(array_merge($portData, [
                'company_id' => $companyId,
            ]));
        }
    }

    /**
     * Prepare data with photo if provided.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function prepareDataWithPhoto(array $data, ?UploadedFile $photo): array
    {
        if ($photo) {
            $data[$this->getPhotoAttribute()] = $this->storePhoto($photo);
        }

        return $data;
    }
}
