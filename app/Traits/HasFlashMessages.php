<?php

namespace App\Traits;

trait HasFlashMessages
{
    /**
     * Flash a success message for created resource.
     */
    protected function flashCreated(string $resource): string
    {
        return __('messages.created', [
            'resource' => __("messages.resources.{$resource}"),
        ]);
    }

    /**
     * Flash a success message for updated resource.
     */
    protected function flashUpdated(string $resource): string
    {
        return __('messages.updated', [
            'resource' => __("messages.resources.{$resource}"),
        ]);
    }

    /**
     * Flash a success message for deleted resource.
     */
    protected function flashDeleted(string $resource): string
    {
        return __('messages.deleted', [
            'resource' => __("messages.resources.{$resource}"),
        ]);
    }

    /**
     * Flash an error message for delete with relations.
     */
    protected function flashDeleteHasRelations(string $resource): string
    {
        return __('messages.error.delete_has_relations', [
            'resource' => __("messages.resources.{$resource}"),
        ]);
    }

    /**
     * Flash an error message for unauthorized action.
     */
    protected function flashUnauthorized(): string
    {
        return __('messages.error.unauthorized');
    }

    /**
     * Flash an error message for self delete.
     */
    protected function flashSelfDelete(): string
    {
        return __('messages.error.self_delete');
    }
}
