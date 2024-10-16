<?php

namespace App\Policies;

use App\Models\Tool;
use App\Models\User;

class ToolPolicy
{
    public function update(User $user, Tool $tool) : bool
    {
        return $user->id === $tool->user_id;
    }

    public function delete(User $user, Tool $tool) : bool
    {
        return $user->id === $tool->user_id;
    }
}
