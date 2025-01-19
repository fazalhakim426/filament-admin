<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Deposit;
use App\Models\User;

class DepositPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Deposit');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Deposit $deposit): bool
    {
        return $user->checkPermissionTo('view Deposit');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Deposit');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Deposit $deposit): bool
    {
        return $user->checkPermissionTo('update Deposit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Deposit $deposit): bool
    {
        return $user->checkPermissionTo('delete Deposit');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any Deposit');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Deposit $deposit): bool
    {
        return $user->checkPermissionTo('restore Deposit');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any Deposit');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Deposit $deposit): bool
    {
        return $user->checkPermissionTo('replicate Deposit');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder Deposit');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Deposit $deposit): bool
    {
        return $user->checkPermissionTo('force-delete Deposit');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any Deposit');
    }
}
