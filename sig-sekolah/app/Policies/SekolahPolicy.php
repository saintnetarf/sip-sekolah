<?php

namespace App\Policies;

use App\Models\Sekolah;
use App\Models\User;

class SekolahPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Sekolah $sekolah): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Sekolah $sekolah): bool
    {
        if ($user->isDinas()) {
            return true;
        }

        return $user->isSekolah()
            && ! is_null($user->sekolah_id)
            && (int) $user->sekolah_id === (int) $sekolah->id;
    }
}
