<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;

interface UserServiceInterface
{
    /**
     * Return the users available within the current tenant context.
     */
    public function list(Request $request): Collection;

    public function createForm(Request $request): Collection;

    public function editForm(Request $request): Collection;

    public function store(Request $request): void;

    public function update(Request $request, User $user): void;

    public function delete(Request $request, User $user): void;
}
