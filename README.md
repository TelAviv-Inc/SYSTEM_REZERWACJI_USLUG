# System Rezerwacji Usług

Aplikacja webowa oparta na frameworku Laravel do zarządzania rezerwacjami usług.

## O projekcie

Ta aplikacja to system rezerwacji usług stworzony przy użyciu frameworka Laravel. Użytkownik wybiera kategorię usług, następnie usługę, pracownika, dzień i godzinę wizyty. System sprawdza dostępność pracownika i kolizje z istniejącymi rezerwacjami.

System wykorzystuje:
- Laravel 12.x (PHP 8.2+)
- Livewire 4 do interaktywnego interfejsu
- Bazę danych SQLite (z możliwością użycia MySQL/PostgreSQL)
- Migracje i seedery bazy danych
- Eloquent ORM z kluczami głównymi UUID
- Uwierzytelnianie oparte na Laravel Breeze

Kod aplikacji znajduje się w katalogu `aplikacja/`.

## Obecny stan rozwoju

**Status**: Faza rozwoju

Zaimplementowane funkcje:
- Rejestracja i logowanie użytkowników
- Role użytkowników: `admin`, `employee`, `user`
- Kategorie usług, usługi i przypisanie pracowników do usług
- Dostępność pracowników (dni tygodnia, konkretne daty, godziny pracy)
- Proces rezerwacji w Livewire: wybór kategorii → usługi → pracownika → dnia → godziny
- Kalendarz tygodniowy z wyborem dnia do 6 tygodni w przód
- Wybór godziny z kontrolą dostępności (frontend i backend) oraz wykrywaniem nakładających się rezerwacji
- Model rezerwacji ze statusami (`pending`, `confirmed`, `completed`, `cancelled`) i polskimi etykietami
- Uprawnienia rezerwacji w `ReservationPolicy` i walidacja w `ReservationForm`
- Strona profilu użytkownika z podsumowaniem konta i rezerwacji (`/dashboard/profile`)
- Obsługa braku usług w kategorii

## Wykorzystane technologie

### Backend
- Framework Laravel 12.x
- PHP 8.2+
- Livewire 4
- Baza danych SQLite/MySQL/PostgreSQL
- Eloquent ORM
- Pest 3 (testy)
- Laravel Pint (styl kodu)

### Frontend
- Silnik szablonów Blade
- Komponenty jednoplikowe Livewire (pliki `⚡nazwa.blade.php`)
- Tailwind CSS i Alpine.js
- Vite do kompilacji zasobów
- Font Awesome i flatpickr

## Instalacja

Wszystkie polecenia uruchamiaj w katalogu `aplikacja/`.

Szybka instalacja:

```bash
composer setup
```

Polecenie instaluje zależności, kopiuje `.env`, generuje klucz, uruchamia migracje i buduje zasoby frontendu.

Instalacja krok po kroku:

1. Sklonuj repozytorium i przejdź do katalogu `aplikacja/`
2. Uruchom `composer install`
3. Skopiuj `.env.example` do `.env`
4. Wygeneruj klucz aplikacji: `php artisan key:generate`
5. Skonfiguruj bazę danych (SQLite domyślnie, lub skonfiguruj MySQL/PostgreSQL)
6. Uruchom migracje z danymi testowymi: `php artisan migrate --seed`
7. Zainstaluj zależności npm: `npm install`
8. Skompiluj zasoby: `npm run build`
9. Uruchom serwer deweloperski: `php artisan serve` (lub `composer dev`, aby uruchomić serwer, kolejkę, logi i Vite jednocześnie)

## Testy

```bash
php artisan test
```

## Postęp w rozwoju

### Ukończone
- Struktura bazy danych, migracje i seedery
- Modele usług, kategorii, pracowników, dostępności i rezerwacji
- Rejestracja i logowanie użytkowników
- Interfejs wyboru usługi, pracownika, dnia i godziny
- Sprawdzanie dostępności godzin i kolizji rezerwacji
- Polityka uprawnień rezerwacji
- Strona profilu użytkownika

### W toku
- Zapis rezerwacji do bazy danych (metoda `reserve()` w komponencie `reserve-service`)
- Zarządzanie rezerwacjami (potwierdzanie, anulowanie)
- Panel pracownika
- Panel administratora
- Zarządzanie użytkownikami, pracownikami i ich dostępnością z poziomu interfejsu

## Licencja

Ta aplikacja to oprogramowanie typu open-source licencjonowane na warunkach [licencji MIT](https://opensource.org/licenses/MIT).
