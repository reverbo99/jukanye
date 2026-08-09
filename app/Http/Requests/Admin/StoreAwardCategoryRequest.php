<?php

namespace App\Http\Requests\Admin;

use App\Support\Bilingual;
use Illuminate\Foundation\Http\FormRequest;

class StoreAwardCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(
            Bilingual::pairRules('name'),
            Bilingual::pairRules('description', ['string'], false),
            [
                'slug' => ['nullable', 'string', 'max:255', 'unique:award_categories,slug'],
                'sort_order' => ['nullable', 'integer', 'min:0'],
            ]
        );
    }
}
