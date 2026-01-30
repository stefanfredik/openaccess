<?php

namespace App\Policies;

use App\Models\User;
use Modules\Area\Models\InfrastructureArea;

class AreaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view areas
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, InfrastructureArea $area): bool
    {
        // Users can only view areas belonging to their company
        return $user->company_id === $area->company_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // All authenticated users can create areas
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, InfrastructureArea $area): bool
    {
        // Users can only update areas belonging to their company
        return $user->company_id === $area->company_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, InfrastructureArea $area): bool
    {
        // Users can only delete areas belonging to their company
        return $user->company_id === $area->company_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, InfrastructureArea $area): bool
    {
        return $user->company_id === $area->company_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, InfrastructureArea $area): bool
    {
        return $user->company_id === $area->company_id;
    }
}
