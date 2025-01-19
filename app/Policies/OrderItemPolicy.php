<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\OrderItem;
use App\Models\User;

class OrderItemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any OrderItem');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OrderItem $orderitem): bool
    {
        return $user->checkPermissionTo('view OrderItem');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create OrderItem');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OrderItem $orderitem): bool
    {
        return $user->checkPermissionTo('update OrderItem');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OrderItem $orderitem): bool
    {
        return $user->checkPermissionTo('delete OrderItem');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any OrderItem');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, OrderItem $orderitem): bool
    {
        return $user->checkPermissionTo('restore OrderItem');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any OrderItem');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, OrderItem $orderitem): bool
    {
        return $user->checkPermissionTo('replicate OrderItem');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder OrderItem');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, OrderItem $orderitem): bool
    {
        return $user->checkPermissionTo('force-delete OrderItem');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any OrderItem');
    }
}
