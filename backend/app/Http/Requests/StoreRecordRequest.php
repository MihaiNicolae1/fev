<?php

namespace App\Http\Requests;

use App\Models\DropdownOption;
use Illuminate\Foundation\Http\FormRequest;

class StoreRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'text_field' => ['required', 'string', 'max:255'],
            'single_select_id' => [
                'nullable',
                'integer',
                'exists:dropdown_options,id,type,' . DropdownOption::TYPE_SINGLE_SELECT,
            ],
            'multi_select_ids' => ['nullable', 'array'],
            'multi_select_ids.*' => [
                'integer',
                'exists:dropdown_options,id,type,' . DropdownOption::TYPE_MULTI_SELECT,
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'text_field.required' => 'The text field is required.',
            'text_field.max' => 'The text field must not exceed 255 characters.',
            'single_select_id.exists' => 'The selected single select option is invalid.',
            'multi_select_ids.*.exists' => 'One or more multi-select options are invalid.',
        ];
    }
}
