<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'Pole :attribute musi zostać zaakceptowane.',
    'accepted_if' => 'Pole :attribute musi zostać zaakceptowane, gdy :other ma wartość :value.',
    'active_url' => 'Pole :attribute musi być prawidłowym adresem URL.',
    'after' => 'Pole :attribute musi być datą późniejszą niż :date.',
    'after_or_equal' => 'Pole :attribute musi być datą nie wcześniejszą niż :date.',
    'alpha' => 'Pole :attribute może zawierać tylko litery.',
    'alpha_dash' => 'Pole :attribute może zawierać tylko litery, cyfry, myślniki i podkreślenia.',
    'alpha_num' => 'Pole :attribute może zawierać tylko litery i cyfry.',
    'any_of' => 'Pole :attribute jest nieprawidłowe.',
    'array' => 'Pole :attribute musi być tablicą.',
    'ascii' => 'Pole :attribute może zawierać tylko jednobajtowe znaki alfanumeryczne i symbole.',
    'before' => 'Pole :attribute musi być datą wcześniejszą niż :date.',
    'before_or_equal' => 'Pole :attribute musi być datą nie późniejszą niż :date.',
    'between' => [
        'array' => 'Pole :attribute musi zawierać od :min do :max elementów.',
        'file' => 'Pole :attribute musi mieć od :min do :max kilobajtów.',
        'numeric' => 'Pole :attribute musi mieścić się w przedziale od :min do :max.',
        'string' => 'Pole :attribute musi mieć od :min do :max znaków.',
    ],
    'boolean' => 'Pole :attribute musi mieć wartość prawda lub fałsz.',
    'can' => 'Pole :attribute zawiera niedozwoloną wartość.',
    'confirmed' => 'Potwierdzenie pola :attribute nie zgadza się.',
    'contains' => 'W polu :attribute brakuje wymaganej wartości.',
    'current_password' => 'Hasło jest nieprawidłowe.',
    'date' => 'Pole :attribute musi być prawidłową datą.',
    'date_equals' => 'Pole :attribute musi być datą równą :date.',
    'date_format' => 'Pole :attribute musi mieć format :format.',
    'decimal' => 'Pole :attribute musi mieć :decimal miejsc po przecinku.',
    'declined' => 'Pole :attribute musi zostać odrzucone.',
    'declined_if' => 'Pole :attribute musi zostać odrzucone, gdy :other ma wartość :value.',
    'different' => 'Pola :attribute i :other muszą się różnić.',
    'digits' => 'Pole :attribute musi składać się z :digits cyfr.',
    'digits_between' => 'Pole :attribute musi mieć od :min do :max cyfr.',
    'dimensions' => 'Pole :attribute ma nieprawidłowe wymiary obrazu.',
    'distinct' => 'Pole :attribute zawiera zduplikowaną wartość.',
    'doesnt_contain' => 'Pole :attribute nie może zawierać żadnej z następujących wartości: :values.',
    'doesnt_end_with' => 'Pole :attribute nie może kończyć się żadną z następujących wartości: :values.',
    'doesnt_start_with' => 'Pole :attribute nie może zaczynać się żadną z następujących wartości: :values.',
    'email' => 'Pole :attribute musi być prawidłowym adresem e-mail.',
    'encoding' => 'Pole :attribute musi być zakodowane w :encoding.',
    'ends_with' => 'Pole :attribute musi kończyć się jedną z następujących wartości: :values.',
    'enum' => 'Wybrana wartość pola :attribute jest nieprawidłowa.',
    'exists' => 'Wybrana wartość pola :attribute jest nieprawidłowa.',
    'extensions' => 'Pole :attribute musi mieć jedno z następujących rozszerzeń: :values.',
    'file' => 'Pole :attribute musi być plikiem.',
    'filled' => 'Pole :attribute musi mieć wartość.',
    'gt' => [
        'array' => 'Pole :attribute musi zawierać więcej niż :value elementów.',
        'file' => 'Pole :attribute musi mieć więcej niż :value kilobajtów.',
        'numeric' => 'Pole :attribute musi być większe niż :value.',
        'string' => 'Pole :attribute musi mieć więcej niż :value znaków.',
    ],
    'gte' => [
        'array' => 'Pole :attribute musi zawierać co najmniej :value elementów.',
        'file' => 'Pole :attribute musi mieć co najmniej :value kilobajtów.',
        'numeric' => 'Pole :attribute musi być większe lub równe :value.',
        'string' => 'Pole :attribute musi mieć co najmniej :value znaków.',
    ],
    'hex_color' => 'Pole :attribute musi być prawidłowym kolorem szesnastkowym.',
    'image' => 'Pole :attribute musi być obrazem.',
    'in' => 'Wybrana wartość pola :attribute jest nieprawidłowa.',
    'in_array' => 'Pole :attribute musi istnieć w :other.',
    'in_array_keys' => 'Pole :attribute musi zawierać co najmniej jeden z następujących kluczy: :values.',
    'integer' => 'Pole :attribute musi być liczbą całkowitą.',
    'ip' => 'Pole :attribute musi być prawidłowym adresem IP.',
    'ipv4' => 'Pole :attribute musi być prawidłowym adresem IPv4.',
    'ipv6' => 'Pole :attribute musi być prawidłowym adresem IPv6.',
    'json' => 'Pole :attribute musi być prawidłowym ciągiem JSON.',
    'list' => 'Pole :attribute musi być listą.',
    'lowercase' => 'Pole :attribute musi być zapisane małymi literami.',
    'lt' => [
        'array' => 'Pole :attribute musi zawierać mniej niż :value elementów.',
        'file' => 'Pole :attribute musi mieć mniej niż :value kilobajtów.',
        'numeric' => 'Pole :attribute musi być mniejsze niż :value.',
        'string' => 'Pole :attribute musi mieć mniej niż :value znaków.',
    ],
    'lte' => [
        'array' => 'Pole :attribute nie może zawierać więcej niż :value elementów.',
        'file' => 'Pole :attribute może mieć co najwyżej :value kilobajtów.',
        'numeric' => 'Pole :attribute musi być mniejsze lub równe :value.',
        'string' => 'Pole :attribute może mieć co najwyżej :value znaków.',
    ],
    'mac_address' => 'Pole :attribute musi być prawidłowym adresem MAC.',
    'max' => [
        'array' => 'Pole :attribute nie może zawierać więcej niż :max elementów.',
        'file' => 'Pole :attribute nie może mieć więcej niż :max kilobajtów.',
        'numeric' => 'Pole :attribute nie może być większe niż :max.',
        'string' => 'Pole :attribute nie może mieć więcej niż :max znaków.',
    ],
    'max_digits' => 'Pole :attribute nie może mieć więcej niż :max cyfr.',
    'mimes' => 'Pole :attribute musi być plikiem typu: :values.',
    'mimetypes' => 'Pole :attribute musi być plikiem typu: :values.',
    'min' => [
        'array' => 'Pole :attribute musi zawierać co najmniej :min elementów.',
        'file' => 'Pole :attribute musi mieć co najmniej :min kilobajtów.',
        'numeric' => 'Pole :attribute musi wynosić co najmniej :min.',
        'string' => 'Pole :attribute musi mieć co najmniej :min znaków.',
    ],
    'min_digits' => 'Pole :attribute musi mieć co najmniej :min cyfr.',
    'missing' => 'Pole :attribute nie może występować.',
    'missing_if' => 'Pole :attribute nie może występować, gdy :other ma wartość :value.',
    'missing_unless' => 'Pole :attribute nie może występować, chyba że :other ma wartość :value.',
    'missing_with' => 'Pole :attribute nie może występować, gdy podano :values.',
    'missing_with_all' => 'Pole :attribute nie może występować, gdy podano wszystkie z :values.',
    'multiple_of' => 'Pole :attribute musi być wielokrotnością :value.',
    'not_in' => 'Wybrana wartość pola :attribute jest nieprawidłowa.',
    'not_regex' => 'Format pola :attribute jest nieprawidłowy.',
    'numeric' => 'Pole :attribute musi być liczbą.',
    'password' => [
        'letters' => 'Pole :attribute musi zawierać co najmniej jedną literę.',
        'mixed' => 'Pole :attribute musi zawierać co najmniej jedną wielką i jedną małą literę.',
        'numbers' => 'Pole :attribute musi zawierać co najmniej jedną cyfrę.',
        'symbols' => 'Pole :attribute musi zawierać co najmniej jeden symbol.',
        'uncompromised' => 'Podana wartość pola :attribute pojawiła się w wycieku danych. Wybierz inną wartość.',
    ],
    'present' => 'Pole :attribute musi być obecne.',
    'present_if' => 'Pole :attribute musi być obecne, gdy :other ma wartość :value.',
    'present_unless' => 'Pole :attribute musi być obecne, chyba że :other ma wartość :value.',
    'present_with' => 'Pole :attribute musi być obecne, gdy podano :values.',
    'present_with_all' => 'Pole :attribute musi być obecne, gdy podano wszystkie z :values.',
    'prohibited' => 'Pole :attribute jest niedozwolone.',
    'prohibited_if' => 'Pole :attribute jest niedozwolone, gdy :other ma wartość :value.',
    'prohibited_if_accepted' => 'Pole :attribute jest niedozwolone, gdy :other zostało zaakceptowane.',
    'prohibited_if_declined' => 'Pole :attribute jest niedozwolone, gdy :other zostało odrzucone.',
    'prohibited_unless' => 'Pole :attribute jest niedozwolone, chyba że :other jest jedną z wartości: :values.',
    'prohibits' => 'Pole :attribute wyklucza obecność pola :other.',
    'regex' => 'Format pola :attribute jest nieprawidłowy.',
    'required' => 'Pole :attribute jest wymagane.',
    'required_array_keys' => 'Pole :attribute musi zawierać wpisy dla: :values.',
    'required_if' => 'Pole :attribute jest wymagane, gdy :other ma wartość :value.',
    'required_if_accepted' => 'Pole :attribute jest wymagane, gdy :other zostało zaakceptowane.',
    'required_if_declined' => 'Pole :attribute jest wymagane, gdy :other zostało odrzucone.',
    'required_unless' => 'Pole :attribute jest wymagane, chyba że :other jest jedną z wartości: :values.',
    'required_with' => 'Pole :attribute jest wymagane, gdy podano :values.',
    'required_with_all' => 'Pole :attribute jest wymagane, gdy podano wszystkie z :values.',
    'required_without' => 'Pole :attribute jest wymagane, gdy nie podano :values.',
    'required_without_all' => 'Pole :attribute jest wymagane, gdy nie podano żadnego z :values.',
    'same' => 'Pole :attribute musi być zgodne z polem :other.',
    'size' => [
        'array' => 'Pole :attribute musi zawierać :size elementów.',
        'file' => 'Pole :attribute musi mieć :size kilobajtów.',
        'numeric' => 'Pole :attribute musi wynosić :size.',
        'string' => 'Pole :attribute musi mieć :size znaków.',
    ],
    'starts_with' => 'Pole :attribute musi zaczynać się jedną z następujących wartości: :values.',
    'string' => 'Pole :attribute musi być tekstem.',
    'timezone' => 'Pole :attribute musi być prawidłową strefą czasową.',
    'unique' => 'Taka wartość pola :attribute jest już zajęta.',
    'uploaded' => 'Nie udało się przesłać pola :attribute.',
    'uppercase' => 'Pole :attribute musi być zapisane wielkimi literami.',
    'url' => 'Pole :attribute musi być prawidłowym adresem URL.',
    'ulid' => 'Pole :attribute musi być prawidłowym identyfikatorem ULID.',
    'uuid' => 'Pole :attribute musi być prawidłowym identyfikatorem UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'name' => 'imię',
        'surname' => 'nazwisko',
        'email' => 'adres e-mail',
        'phone' => 'numer telefonu',
        'password' => 'hasło',
        'password_confirmation' => 'potwierdzenie hasła',
        'current_password' => 'obecne hasło',
        'token' => 'token',
        'date' => 'data',
        'hour' => 'godzina',

        // Livewire form objects ($form) prefix their fields with "form."
        'form' => [
            'email' => 'adres e-mail',
            'phone' => 'numer telefonu',
            'password' => 'hasło',
            'password_confirmation' => 'potwierdzenie hasła',
            'current_password' => 'obecne hasło',
            'service_id' => 'usługa',
            'employee_id' => 'pracownik',
            'date' => 'data',
            'hour' => 'godzina',
        ],
    ],

    'values' => [
        'date' => [
            'today' => 'dzisiaj',
            'tomorrow' => 'jutro',
            'yesterday' => 'wczoraj',
            'now' => 'teraz',
        ],

        'form' => [
            'date' => [
                'today' => 'dzisiaj',
                'tomorrow' => 'jutro',
                'yesterday' => 'wczoraj',
                'now' => 'teraz',
            ],
        ],
    ],

];
