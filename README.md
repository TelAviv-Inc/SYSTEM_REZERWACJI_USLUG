# System Rezerwacji Usług

Aplikacja Laravel do rezerwowania wizyt, zarządzania grafikami pracowników oraz raportowania rezerwacji i przychodów.

[![Render](https://img.shields.io/badge/Render-live-46E3B7?logo=render&logoColor=white)](https://system-rezerwacji-uslug.onrender.com/)

[Wersja demonstracyjna na Render](https://system-rezerwacji-uslug.onrender.com/)

## O projekcie

Użytkownik wybiera kategorię, usługę, pracownika, dzień i godzinę wizyty. Przy tworzeniu i zmianie terminu system sprawdza grafik pracownika oraz kolizje z innymi wizytami w tej samej transakcji co zapis.

Kod aplikacji znajduje się w katalogu `aplikacja/`. Zasady rezerwacji, uprawnienia i architekturę opisuje [dokumentacja techniczna](DOCS.md).

## Konta testowe

| Rola       | Email                       | Hasło      |
|------------|-----------------------------|------------|
| `admin`    | `mp@z.pl`                   | `admin123` |
| `client`   | `tomasz.wojcik@example.com` | `password` |
| `employee` | `marta.nowak@salon.test`    | `password` |

Konta demonstracyjne pochodzą ze zrzutu `szczegoly/Dump20261007.sql`. Domyślny seeder tworzy osobny zestaw przykładowych użytkowników i kategorii.

## Funkcje

### Rezerwacje

- Kalendarz rezerwacji korzysta z tygodniowego grafiku pracownika i pozwala przeglądać terminy do sześciu tygodni naprzód.
- W widoku „Moje rezerwacje” (`/dashboard/reservations`) klient przegląda wizyty, zmienia termin przed dniem wizyty i może anulować aktywną wizytę do chwili jej rozpoczęcia, także tego samego dnia.
- Rezerwacja zachowuje cenę z chwili zapisu. Zmiana terminu przywraca status „Oczekująca”.

### Panele pracownika i administratora

- W panelu pracownika (`/dashboard/employee`) pracownik przegląda swoje wizyty, grafik i statystyki. Może potwierdzić lub anulować przypisaną wizytę przed jej rozpoczęciem i oznaczyć ją jako zakończoną od godziny rozpoczęcia.
- Panel administratora (`/dashboard/admin`) obejmuje zarządzanie użytkownikami, pracownikami, usługami, kategoriami, grafikami i rezerwacjami.
- Raporty (`/dashboard/admin/reports`) pokazują rezerwacje i przychody według kategorii, usług lub pracowników. Raport pracowników uwzględnia obłożenie grafików. Raporty oferują porównanie okresów, wykresy, eksport CSV i wydruk.

### Konto i profil

- Rejestracja, logowanie i resetowanie hasła. Nowe konto otrzymuje rolę klienta (`client`).
- Profil (`/dashboard/profile`) umożliwia edycję danych i zmianę hasła oraz dezaktywację lub usunięcie konta.

## Wykorzystane technologie

### Backend

- Laravel 12.x, PHP 8.2+
- SQLite domyślnie, MySQL na Render
- Eloquent ORM z kluczami głównymi UUID
- Uwierzytelnianie oparte na Laravel Breeze
- Pest 3 (testy), Laravel Pint (styl kodu)

### Frontend

- Blade i komponenty jednoplikowe Livewire 4 (pliki `⚡nazwa.blade.php`)
- Tailwind CSS v4 i Alpine.js
- Vite, Chart.js i Font Awesome

### Wdrożenie

- Docker (PHP 8.3 i Apache, osobny etap budowania zasobów Vite)
- Render

## Instalacja lokalna

Potrzebujesz PHP 8.2+, Composera oraz Node.js 20.19+ w linii 20.x lub 22.12+ z npm. Na nowej kopii repozytorium uruchom poniższe polecenia.

```bash
cd aplikacja
composer setup
php artisan serve
```

Aplikacja będzie dostępna pod adresem `http://127.0.0.1:8000`. `composer setup` instaluje zależności, tworzy `.env` na podstawie `.env.example`, generuje klucz, uruchamia migracje i buduje zasoby. Aby dodać przykładowe dane z seederów, uruchom `php artisan db:seed` w katalogu `aplikacja/`.

Instrukcja instalacji ręcznej i uruchamiania Vite znajduje się w [dokumentacji technicznej](DOCS.md#konfiguracja-środowiska-deweloperskiego).

## Docker

W katalogu `aplikacja/` wpisz `APP_KEY` do `.env`. Po zainstalowaniu zależności PHP możesz wygenerować klucz poleceniem `php artisan key:generate --show`. Następnie uruchom kontener.

```bash
docker compose up --build
```

Aplikacja będzie dostępna pod adresem `http://localhost:8080`. Przy starcie kontener uruchamia migracje i domyślnie przechowuje bazę SQLite w wolumenie `app-storage`. Aby użyć MySQL, przekaż parametry `DB_*` do środowiska kontenera, w tym `DB_CONNECTION=mysql`. Dla połączenia TLS podaj ścieżkę do certyfikatu CA w `MYSQL_ATTR_SSL_CA`.

## Testy

Uruchom polecenie z katalogu `aplikacja/`. Testy korzystają z SQLite w pamięci.

```bash
php artisan test
```

## Licencja

Projekt jest udostępniany na warunkach [licencji MIT](LICENSE).
