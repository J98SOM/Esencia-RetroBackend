<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\StoreUserRequest;
use App\Http\Requests\Auth\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $userRole = \App\Models\Role::where('name', 'user')->first();

        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'role_id' => $userRole?->id,
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => new UserResource($user),
            'token' => $user->createToken('auth_token')->plainTextToken,
        ], 201);
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = User::where('email', $credentials['email'])->first();

        return response()->json([
            'message' => 'Login successful',
            'user' => new UserResource($user),
            'token' => $user->createToken('auth_token')->plainTextToken,
        ], 200);
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ], 200);
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        // For TransientToken (SPA usage), no deletion needed
        // For PersonalAccessToken, delete it from database
        $token = $request->user()->currentAccessToken();
        
        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }

    /**
     * Create a new user (admin endpoint)
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'role_id' => $request->validated('role_id'),
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => new UserResource($user),
        ], 201);
    }

    /**
     * Get all users
     */
    public function index(): JsonResponse
    {
        $users = User::with('role')->get();

        return response()->json([
            'users' => UserResource::collection($users),
        ], 200);
    }

    /**
     * Get a specific user
     */
    public function show(User $user): JsonResponse
    {
        $user->load('role');

        return response()->json([
            'user' => new UserResource($user),
        ], 200);
    }

    /**
     * Update a user
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => new UserResource($user),
        ], 200);
    }

    /**
     * Delete a user
     */
    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ], 200);
    }
}
