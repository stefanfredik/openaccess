<?php

namespace Modules\ActiveDevice\Services;

use Illuminate\Http\UploadedFile;
use Modules\ActiveDevice\Models\Router;

class RouterService extends BaseActiveDeviceService
{
    protected function getPhotoStorageFolder(): string
    {
        return 'router-photos';
    }

    /**
     * Create a new router with photo and service ports.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>|null  $servicePorts
     */
    public function create(array $data, ?UploadedFile $photo = null, ?array $servicePorts = null): Router
    {
        $data = $this->prepareDataWithPhoto($data, $photo);

        $router = Router::create($data);

        if ($servicePorts) {
            $this->syncServicePorts($router, $servicePorts, $data['company_id']);
        }

        return $router;
    }

    /**
     * Update a router with photo and service ports.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>|null  $servicePorts
     */
    public function update(Router $router, array $data, ?UploadedFile $photo = null, ?array $servicePorts = null, ?int $companyId = null): Router
    {
        if ($photo) {
            $this->deletePhoto($router);
            $data = $this->prepareDataWithPhoto($data, $photo);
        }

        $router->update($data);

        if ($servicePorts !== null && $companyId) {
            $router->servicePorts()->delete();
            $this->syncServicePorts($router, $servicePorts, $companyId);
        }

        return $router;
    }

    /**
     * Delete a router and its associated photo.
     */
    public function delete(Router $router): bool
    {
        $this->deletePhoto($router);

        return $router->delete();
    }
}
