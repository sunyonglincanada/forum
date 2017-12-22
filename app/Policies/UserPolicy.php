<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Check Whether the user can update the given profile
     * @param User $signedInUser
     * @param User $user
     * @return bool
     *
     * @author Eric
     * @date 2017-12-22
     */
    public function update(User $signedInUser, User $user)
    {
        return $signedInUser->id === $user->id;
    }
}
