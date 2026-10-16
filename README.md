# System Rezerwacji Usług

Aplikacja webowa oparta na frameworku Laravel do zarządzania rezerwacjami usług.

[![Render](https://img.shields.io/badge/Render-live-46E3B7?logo=render&logoColor=white)](https://system-rezerwacji-uslug.onrender.com/)

🌐 **Wersja online:** https://system-rezerwacji-uslug.onrender.com/

## O projekcie

Użytkownik wybiera kategorię usług, następnie usługę, pracownika, dzień i godzinę wizyty. System sprawdza dostępność pracownika i kolizje z istniejącymi rezerwacjami, a zapis rezerwacji jest zabezpieczony przed jednoczesną rezerwacją tego samego terminu przez dwie osoby.

Kod aplikacji znajduje się w katalogu `aplikacja/`.

## Konta testowe

| Rola       | Email                       | Hasło      |
|------------|-----------------------------|------------|
| `admin`    | `mp@z.pl`                   | `admin123` |
| `client`   | `tomasz.wojcik@example.com` | `password` |
| `employee` | `marta.nowak@salon.test`    | `password` |

Konta są dostępne w wersji online. Pochodzą ze zrzutu bazy `szczegoly/Dump20261007.sql`, a nie z seederów, więc po lokalnym `php artisan migrate --seed` ich nie będzie.

## Obecny stan rozwoju

**Status**: Faza rozwoju, wersja testowa wdrożona na Render.

### Rezerwacje
- Kategorie usług, usługi i przypisanie pracowników do usług
- Dostępność pracowników (dni tygodnia, konkretne daty, godziny pracy)
- Proces rezerwacji w Livewire: wybór kategorii → usługi → pracownika → dnia → godziny
- Kalendarz tygodniowy z wyborem dnia do 6 tygodni w przód
- Wybór godziny z kontrolą dostępności (frontend i backend)
- Zapis rezerwacji w transakcji z blokadą wiersza pracownika (`lockForUpdate`) i wykrywaniem nakładających się rezerwacji
- Statusy rezerwacji (`pending`, `confirmed`, `completed`, `cancelled`) z polskimi etykietami
- Uprawnienia w `ReservationPolicy` (tworzenie, podgląd, anulowanie, potwierdzanie)

### Konto i profil
- Rejestracja i logowanie (z animacją ładowania przycisków)
- Role użytkowników: `client` (domyślna), `employee`, `admin`
- Strona profilu (`/dashboard/profile`) z podsumowaniem konta i rezerwacji
- Zakładki profilu: informacje o koncie, dane osobowe (email, telefon), zmiana hasła, ustawienia
- Dezaktywacja i usuwanie konta; nieaktywni użytkownicy są wylogowywani (middleware `active`)
- Walidacja i normalizacja numeru telefonu

### Bezpieczeństwo i infrastruktura
- Limity zapytań: 120/min dla stron, 300/min dla żądań Livewire, 10/min dla formularzy logowania, rejestracji i zmiany hasła
- Własne strony błędów (403, 404, 419, 429, 500, 503)
- Obraz Docker i wdrożenie na Render (MySQL z połączeniem SSL)

### W toku
- Strona „Moje rezerwacje” (`/dashboard/reservations`) – na razie szkielet
- Zarządzanie rezerwacjami (zmiana terminu, anulowanie, potwierdzanie)
- Panel pracownika
- Panel administratora
- Zarządzanie użytkownikami, pracownikami i ich dostępnością z poziomu interfejsu

## Wykorzystane technologie

### Backend
- Laravel 12.x, PHP 8.2+
- Livewire 4
- SQLite (lokalnie), MySQL (produkcja)
- Eloquent ORM z kluczami głównymi UUID
- Uwierzytelnianie oparte na Laravel Breeze
- Pest 3 (testy), Laravel Pint (styl kodu)

### Frontend
- Blade i komponenty jednoplikowe Livewire (pliki `⚡nazwa.blade.php`)
- Tailwind CSS v4 i Alpine.js
- Vite
- Font Awesome i flatpickr

### Wdrożenie
- Docker (PHP 8.3 + Apache, wieloetapowy build z zasobami Vite)
- Render

## Instalacja lokalna

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
5. Skonfiguruj bazę danych (domyślnie SQLite)
6. Uruchom migracje z danymi testowymi: `php artisan migrate --seed`
7. Zainstaluj zależności npm: `npm install`
8. Skompiluj zasoby: `npm run build`
9. Uruchom serwer: `php artisan serve` (lub `composer dev`, aby uruchomić serwer, kolejkę, logi i Vite jednocześnie)

## Docker

W katalogu `aplikacja/` utwórz plik `.env` z kluczem aplikacji (`APP_KEY=...`, wygenerujesz go przez `php artisan key:generate --show`), a następnie:

```bash
docker compose up --build
```

Aplikacja będzie dostępna pod adresem http://localhost:8080. Przy starcie kontener sam uruchamia migracje. Domyślnie używa SQLite w wolumenie `storage/`; aby użyć MySQL, ustaw zmienne `DB_*`, a dla połączenia SSL także `MYSQL_ATTR_SSL_CA` (ścieżka do certyfikatu CA).

## Testy

```bash
php artisan test
```

## Licencja

Ta aplikacja to oprogramowanie typu open-source licencjonowane na warunkach [licencji MIT](https://opensource.org/licenses/MIT).
