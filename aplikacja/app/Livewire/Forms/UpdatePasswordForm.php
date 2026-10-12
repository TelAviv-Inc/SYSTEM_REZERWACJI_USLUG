<?php

namespace App\Livewire\Forms;

use Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Validate;
use Livewire\Form;

class UpdatePasswordForm extends Form
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults() ,'confirmed']  
        ];
    }
    public function update(): void
    {
        $this->validate();

        auth()->user()->update(['password' => Hash ::make($this->password)]);
        Auth::logoutOtherDevices($this->password);
        $this->reset();
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Podaj obecne haslo',
            'current_password.current_password' => 'Nieprawidlowe haslo',
            'password.required' => 'Podaj nowe haslo',
            'password.confirmed' => 'Hasla nie sa takie same',
            'password.min' => 'Haslo musi miec co najmniej 8 znakow'

        ];
    }
}
