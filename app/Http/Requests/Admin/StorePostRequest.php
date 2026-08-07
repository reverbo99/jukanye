<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title_en' => ['required', 'string', 'max:255'],
            'title_sw' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:posts,slug'],
            'excerpt_en' => ['nullable', 'string'],
            'excerpt_sw' => ['nullable', 'string'],
            'body_en' => ['nullable', 'string'],
            'body_sw' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
