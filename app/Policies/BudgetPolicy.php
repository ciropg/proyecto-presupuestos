<?php

namespace App\Policies;

use App\Models\Budget;
use App\Models\User;

class BudgetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user !== null;
    }

    public function view(User $user, Budget $budget): bool
    {
        return $user->isAdmin() || $budget->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user !== null;
    }

    public function update(User $user, Budget $budget): bool
    {
        return $user->isAdmin() || $budget->user_id === $user->id;
    }

    public function delete(User $user, Budget $budget): bool
    {
        return $user->isAdmin() || $budget->user_id === $user->id;
    }

    public function publish(User $user, Budget $budget): bool
    {
        return $this->update($user, $budget);
    }
}
