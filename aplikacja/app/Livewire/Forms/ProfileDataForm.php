<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ProfileDataForm extends Form
{
    public string $email = '';
    public string $phone = '';
    public string $current_password = '';

    public function setUser(User $user)
    {
        $this->fill($user->only(['email', 'phone']));

    }

    public function rules(): array
    {
        $user = auth()->user;

        return [
            "email" => ['required', 'string', 'email', 'max:255',  Rule::unique('users', 'email')->ignore($user)],
            "phone" => ['required', 'string', ],
            "current_password" => [Rule::requiredIf(fn() => $this->email !== $user->email), 'nullable', 'current_password']
        ];
    }
}
