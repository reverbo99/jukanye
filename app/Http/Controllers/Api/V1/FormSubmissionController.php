<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FormSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FormSubmissionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'form' => ['required', 'in:register,contact'],
            'email' => ['nullable', 'email', 'max:255'],
            'payload' => ['nullable', 'array'],
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:5000'],
            'city' => ['nullable', 'string', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
        ]);

        $payload = $data['payload'] ?? [];
        foreach (['name', 'phone', 'message', 'city', 'organization', 'country'] as $key) {
            if (! empty($data[$key])) {
                $payload[$key] = $data[$key];
            }
        }

        $email = $data['email'] ?? ($payload['email'] ?? null);

        $submission = FormSubmission::create([
            'form' => $data['form'],
            'email' => $email,
            'payload' => $payload ?: null,
        ]);

        return response()->json([
            'data' => [
                'id' => $submission->id,
                'form' => $submission->form,
                'email' => $submission->email,
            ],
            'message' => 'Submitted',
            'meta' => (object) [],
        ], 201);
    }
}
