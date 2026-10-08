<?php

namespace App\Http\Requests\Auth;

use App\Support\Phone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    /**
     * Strip spaces and dashes from the phone number before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('phone')) {
            $this->merge([
                'phone' => Phone::normalize($this->input('phone')),
            ]);
        }
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'regex:/^(\+48)?\d{9}$/'],

            'password' => ['required', 'confirmed', 'min:8'],
        ];

        // Add role validation only if the field is present
        if ($this->has('role')) {
            $rules['role'] = ['required', 'string', 'in:client,employee,admin'];
        }

        return $rules;
    }
}
