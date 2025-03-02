<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Product');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product $product): bool
    {
        return $user->checkPermissionTo('view Product') && $user->id == $product->supplier_user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user,Product $product): bool
    {
        return $user->checkPermissionTo('create Product') && $user->id == $product->supplier_user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product): bool
    {
        return $user->checkPermissionTo('update Product') && $user->id == $product->supplier_user_id;;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->checkPermissionTo('delete Product');
    }

    /**
     * Determine whether the user can delete any models.
     */
    public function deleteAny(User $user): bool
    {
        return $user->checkPermissionTo('delete-any Product');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Product $product): bool
    {
        return $user->checkPermissionTo('restore Product');
    }

    /**
     * Determine whether the user can restore any models.
     */
    public function restoreAny(User $user): bool
    {
        return $user->checkPermissionTo('restore-any Product');
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, Product $product): bool
    {
        return $user->checkPermissionTo('replicate Product');
    }

    /**
     * Determine whether the user can reorder the models.
     */
    public function reorder(User $user): bool
    {
        return $user->checkPermissionTo('reorder Product');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        return $user->checkPermissionTo('force-delete Product');
    }

    /**
     * Determine whether the user can permanently delete any models.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->checkPermissionTo('force-delete-any Product');
    }
}
