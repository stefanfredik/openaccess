<?php

namespace Modules\Pop\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Pop\Models\Pop;

class PopService
{
    /**
     * Create a new POP with photos.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>|null  $photos
     */
    public function create(array $data, ?array $photos = null): Pop
    {
        $pop = Pop::create($data);

        if ($photos) {
            $this->storePhotos($pop, $photos);
        }

        return $pop;
    }

    /**
     * Update a POP with photos.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>|null  $photos
     */
    public function update(Pop $pop, array $data, ?array $photos = null): Pop
    {
        $pop->update($data);

        if ($photos) {
            $this->storePhotos($pop, $photos);
        }

        return $pop;
    }

    /**
     * Delete a POP and its associated photos.
     */
    public function delete(Pop $pop): bool
    {
        // Delete all photos from storage
        foreach ($pop->photos as $photo) {
            Storage::disk('public')->delete($photo->path);
        }

        return $pop->delete();
    }

    /**
     * Store photos for a POP.
     *
     * @param  array<int, UploadedFile>  $photos
     */
    private function storePhotos(Pop $pop, array $photos): void
    {
        foreach ($photos as $photo) {
            $path = $photo->store('pop-photos', 'public');
            $pop->photos()->create([
                'path' => $path,
            ]);
        }
    }
}
