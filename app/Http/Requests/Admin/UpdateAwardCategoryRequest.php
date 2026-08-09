<?php

namespace App\Http\Requests\Admin;

use App\Support\Bilingual;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAwardCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('award_category')?->id;

        return array_merge(
            Bilingual::pairRules('name'),
            Bilingual::pairRules('description', ['string'], false),
            [
                'slug' => ['nullable', 'string', 'max:255', Rule::unique('award_categories', 'slug')->ignore($id)],
                'sort_order' => ['nullable', 'integer', 'min:0'],
            ]
        );
    }
}
