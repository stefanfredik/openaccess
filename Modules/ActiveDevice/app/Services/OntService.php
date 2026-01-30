<?php

namespace Modules\ActiveDevice\Services;

use Illuminate\Http\UploadedFile;
use Modules\ActiveDevice\Models\Ont;

class OntService extends BaseActiveDeviceService
{
    protected function getPhotoStorageFolder(): string
    {
        return 'ont-photos';
    }

    /**
     * Create a new ONT with service ports.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>|null  $servicePorts
     */
    public function create(array $data, ?UploadedFile $photo = null, ?array $servicePorts = null): Ont
    {
        $data = $this->prepareDataWithPhoto($data, $photo);

        $ont = Ont::create($data);

        if ($servicePorts) {
            $this->syncServicePorts($ont, $servicePorts, $data['company_id']);
        }

        return $ont;
    }

    /**
     * Update an ONT with service ports.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>|null  $servicePorts
     */
    public function update(Ont $ont, array $data, ?UploadedFile $photo = null, ?array $servicePorts = null, ?int $companyId = null): Ont
    {
        if ($photo) {
            $this->deletePhoto($ont);
            $data = $this->prepareDataWithPhoto($data, $photo);
        }

        $ont->update($data);

        if ($servicePorts !== null && $companyId) {
            $ont->servicePorts()->delete();
            $this->syncServicePorts($ont, $servicePorts, $companyId);
        }

        return $ont;
    }

    /**
     * Delete an ONT.
     */
    public function delete(Ont $ont): bool
    {
        $this->deletePhoto($ont);

        return $ont->delete();
    }
}
