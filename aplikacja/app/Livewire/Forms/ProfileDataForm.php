<?php

namespace App\Livewire\Forms;

use App\Models\User;
use App\Support\Phone;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
        $user = auth()->user();


        return [
            "email" => ['required', 'string', 'email', 'max:255',  Rule::unique('users', 'email')->ignore($user)],
            "phone" => ['required', 'string', 'regex:/^(\+48)?\d{9}$/'],
            "current_password" => [Rule::requiredIf(fn() => $this->email !== $user->email), 'nullable', 'current_password']
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Ten adres email jest juz zajety',
            'phone.regex' => 'Podaj poprawny numer telefenu',
            'current_password.required' => 'Podaj obecne haslo aby zmienic email',
            'current_password.current_password' => 'Nieprawidlowe haslo'
        ];
    }

    public function update(): bool 
    {
        $this->phone = Phone::normalize($this->phone);
        $this->validate();
        $user = auth()->user();
        $user->fill($this->only(['email', 'phone']));
        if (! $user->isDirty()) return false;

        try {
            $user->save();
        } catch (UniqueConstraintViolationException) {
            // Someone took this email between validate() and save()
            throw ValidationException::withMessages([
                $this->getPropertyName().'.email' => $this->messages()['email.unique'],
            ]);
        }

        $this->reset('current_password');
        return true;
    }
    protected function normalizePhone()
    {
        $this->phone = Phone::normalize($this->phone);
    }
}
