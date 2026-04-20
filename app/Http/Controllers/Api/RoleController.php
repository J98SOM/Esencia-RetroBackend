<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StoreRoleRequest;
use App\Http\Requests\Auth\UpdateRoleRequest;
use App\Models\Role;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    /**
     * Get all roles
     */
    public function index(): JsonResponse
    {
        $roles = Role::all();

        return response()->json([
            'roles' => $roles,
        ], 200);
    }

    /**
     * Get a specific role
     */
    public function show(Role $role): JsonResponse
    {
        return response()->json([
            'role' => $role,
        ], 200);
    }

    /**
     * Create a new role
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = Role::create($request->validated());

        return response()->json([
            'message' => 'Role created successfully',
            'role' => $role,
        ], 201);
    }

    /**
     * Update a role
     */
    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $role->update($request->validated());

        return response()->json([
            'message' => 'Role updated successfully',
            'role' => $role,
        ], 200);
    }

    /**
     * Delete a role
     */
    public function destroy(Role $role): JsonResponse
    {
        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully',
        ], 200);
    }
}
