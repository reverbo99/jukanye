<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\StoresPublicImages;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ApiMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use StoresPublicImages;

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone' => ['nullable', 'string', 'max:40'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'is_admin' => false,
        ]);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'user' => $this->userPayload($user),
            ],
            'meta' => (object) [],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'user' => $this->userPayload($user),
            ],
            'meta' => (object) [],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->userPayload($request->user()),
            'meta' => (object) [],
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:40'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'current_password' => ['required_with:password', 'current_password'],
        ]);

        if (array_key_exists('name', $data)) {
            $user->name = $data['name'];
        }
        if (array_key_exists('email', $data)) {
            $user->email = $data['email'];
        }
        if (array_key_exists('phone', $data)) {
            $user->phone = $data['phone'];
        }
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return response()->json([
            'data' => $this->userPayload($user->fresh()),
            'meta' => (object) [],
        ]);
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'avatar' => ['required', 'image', 'max:4096'],
        ]);

        $this->deletePublicImage($user->avatar);
        $user->avatar = $this->storePublicImage($data['avatar'], 'avatars');
        $user->save();

        return response()->json([
            'data' => $this->userPayload($user->fresh()),
            'meta' => (object) [],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out',
            'meta' => (object) [],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar_url' => ApiMedia::url($user->avatar),
            'is_admin' => (bool) $user->is_admin,
        ];
    }
}
