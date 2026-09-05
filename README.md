# System Rezerwacji Usług

Aplikacja webowa oparta na frameworku Laravel do zarządzania rezerwacjami usług.

## O projekcie

Ta aplikacja to system rezerwacji usług stworzony przy użyciu frameworka Laravel. Pozwala użytkownikom przeglądać dostępne usługi, zarządzać kategoriami usług oraz obsługiwać rezerwacje.

System wykorzystuje:
- Laravel 12.x (PHP 8.2+)
- Bazę danych SQLite (z możliwością użycia MySQL/PostgreSQL)
- Migracje bazy danych do zarządzania schematem
- Eloquent ORM do operacji na bazie danych
- Wbudowany system uwierzytelniania Laravel

## Obecny stan rozwoju

**Status**: Faza rozwoju

Ta aplikacja jest obecnie w fazie aktywnego rozwoju z zaimplementowanymi następującymi funkcjami:
- Zarządzanie usługami i kategoriami usług
- Migracje bazy danych
- Podstawowe modele i fabryki
- Struktura testów (testy jednostkowe i funkcyjne)
- Szkiełko uwierzytelniania
- możliwośc przetwarzania zadań w tle

## Wykorzystane technologie

### Backend
- Framework Laravel 12.x
- PHP 8.2+
- Baza danych SQLite/MySQL/PostgreSQL
- Eloquent ORM
- Framework testów Laravel

### Frontend
- Silnik szablonów Blade
- Vite do kompilacji zasobów
- Nowoczesny JavaScript

## Instalacja

1. Sklonuj repozytorium
2. Uruchom `composer install`
3. Skopiuj `.env.example` do `.env`
4. Wygeneruj klucz aplikacji: `php artisan key:generate`
5. Skonfiguruj bazę danych (SQLite domyślnie, lub skonfiguruj MySQL/PostgreSQL)
6. Uruchom migracje: `php artisan migrate`
7. Zainstaluj zależności npm: `npm install`
8. Skompiluj zasoby: `npm run build`
9. Uruchom serwer deweloperski: `php artisan serve`

## Postęp w rozwoju

### Ukończone
- Struktura bazy danych i migracje
- Podstawowe modele usług i kategorii
- Szkiełko uwierzytelniania
- Konfiguracja frameworka testów
- Pliki konfiguracyjne

### W toku
- Implementacja interfejsu frontendowego
- Funkcjonalność rezerwacji
- Funkcje zarządzania użytkownikami
- Panel administratora

## Licencja

Ta aplikacja to oprogramowanie typu open-source licencjonowane na warunkach [licencji MIT](https://opensource.org/licenses/MIT).