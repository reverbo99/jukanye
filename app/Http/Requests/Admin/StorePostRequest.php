<?php

namespace App\Http\Requests\Admin;

use App\Support\Bilingual;
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
        return array_merge(
            Bilingual::pairRules('title'),
            Bilingual::pairRules('excerpt', ['string'], false),
            Bilingual::pairRules('body', ['string'], false),
            [
                'slug' => ['nullable', 'string', 'max:255', 'unique:posts,slug'],
                'cover_image' => ['nullable', 'image', 'max:4096'],
                'status' => ['required', Rule::in(['draft', 'published'])],
                'published_at' => ['nullable', 'date'],
            ]
        );
    }
}
