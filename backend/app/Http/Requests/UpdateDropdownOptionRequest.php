<?php

namespace App\Http\Requests;

use App\Models\DropdownOption;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDropdownOptionRequest extends FormRequest
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
        $optionId = $this->route('dropdownOption')->id;

        return [
            'type' => [
                'sometimes',
                'required',
                'string',
                Rule::in([DropdownOption::TYPE_SINGLE_SELECT, DropdownOption::TYPE_MULTI_SELECT]),
            ],
            'label' => ['sometimes', 'required', 'string', 'max:255'],
            'value' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('dropdown_options')->where(function ($query) {
                    return $query->where('type', $this->type ?? $this->route('dropdownOption')->type);
                })->ignore($optionId),
            ],
            'is_active' => ['nullable', 'boolean'],
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
            'type.in' => 'The dropdown type must be single_select or multi_select.',
            'label.required' => 'The label is required.',
            'value.required' => 'The value is required.',
            'value.unique' => 'This value already exists for this dropdown type.',
        ];
    }
}
