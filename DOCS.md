# System Rezerwacji Usług — dokumentacja techniczna

## Omówienie

System Rezerwacji Usług to aplikacja Laravel, w której klienci rezerwują wizyty u pracowników. Pracownicy obsługują własne wizyty i przeglądają statystyki, a administratorzy zarządzają katalogiem usług, kontami, grafikami i rezerwacjami oraz korzystają z raportów.

## Struktura projektu

```
SYSTEM_REZERWACJI_USLUG/
├── aplikacja/              # Główna aplikacja Laravel
│   ├── app/                # Kod aplikacji
│   │   ├── Http/           # Kontrolery, middleware, żądania HTTP
│   │   ├── Models/         # Modele Eloquent
│   │   ├── Livewire/       # Formularze Livewire
│   │   ├── Policies/       # Polityki autoryzacji
│   │   └── Support/        # Klasy wspomagające
│   ├── database/           # Migracje, fabryki i seedery
│   ├── resources/          # Widoki Blade, komponenty Livewire, CSS i JavaScript
│   ├── routes/             # Trasy aplikacji
│   └── tests/              # Zestaw testów
├── README.md               # Omówienie projektu
├── DOCS.md                 # Dokumentacja techniczna
├── AGENTS.md               # Instrukcje dotyczące rozwoju
└── CLAUDE.md               # Wskazówki dla Claude AI
```

## Główne komponenty

### Modele

#### Model użytkownika (`app/Models/User.php`)

- Wykorzystuje UUID jako klucz główny
- Obsługuje role klienta (`client`), pracownika (`employee`) i administratora (`admin`)
- Śledzi status aktywności użytkownika (aktywny / nieaktywny)
- Relacja `reservations()` łączy użytkownika z jego rezerwacjami, a `employee()` z profilem pracownika

#### Model kategorii usług (`app/Models/ServiceCategory.php`)

- Reprezentuje kategorie usług z ikonami
- Ma wiele usług
- Wykorzystuje UUID jako klucz główny

#### Model usługi (`app/Models/Service.php`)

- Należy do kategorii usług
- Relacja wielu do wielu z pracownikami (przez tabelę pośrednią)
- Ma wiele rezerwacji
- Generuje `slug` przy tworzeniu usługi i aktualizuje go po zmianie nazwy

#### Model pracownika (`app/Models/Employee.php`)

- Należy do użytkownika
- Relacja wielu do wielu z usługami
- Ma wiele rekordów dostępności i rezerwacji
- Zakres `bookable()` dla aktywnych pracowników z aktywnymi kontami

#### Model dostępności pracownika (`app/Models/EmployeeAvailability.php`)

- Przechowuje przedziały godzin pracy w polach `start_time` i `end_time`
- Kalendarz i walidacja rezerwacji korzystają z tygodniowych przedziałów `day_of_week`. Przedziały zapisane dla dzisiejszej lub przyszłej daty (`specific_date`) są wyświetlane w panelu pracownika

#### Model rezerwacji (`app/Models/Reservation.php`)

- Obsługuje statusy `pending` (oczekująca), `confirmed` (potwierdzona), `completed` (zakończona) i `cancelled` (anulowana)
- Należy do użytkownika, pracownika i usługi
- Udostępnia zakresy zapytań do filtrowania według statusu i wykrywania kolizji oraz metodę `hasStarted()`, która porównuje datę i godzinę rozpoczęcia wizyty z bieżącym czasem
- Przechowuje cenę usługi z chwili rezerwacji w `reservations.price`. Przy dodaniu kolumny migracja uzupełnia istniejące rekordy ceną usługi obowiązującą w momencie migracji

### Schemat bazy danych

Tabele domenowe korzystają z UUID jako kluczy głównych.

1. **users** - Konta użytkowników z rolami i statusem aktywności
2. **service_categories** - Kategorie usług z ikonami
3. **services** - Indywidualne usługi w kategoriach
4. **employees** - Profile pracowników powiązane z użytkownikami
5. **employee_services** - Tabela pośrednia dla relacji pracownik-usługa
6. **employee_availabilities** - Przedziały godzin pracy pracowników
7. **reservations** - Terminy, statusy i zapisane ceny wizyt

### Komponenty Livewire

Komponenty jednoplikowe Livewire 4 znajdują się w `aplikacja/resources/views/components/` (pliki `⚡nazwa.blade.php`). Kalendarze i formularze komunikują się zdarzeniami Livewire.

1. `show-services` - Wyświetla kategorie usług i usługi
2. `reserve-service` - Obsługuje wybór pracownika, daty i godziny oraz zapis wizyty
3. `employee-week-calendar` - Kalendarz do wyboru dni pracy
4. `employee-hour-calendar` - Kalendarz godzinowy dostępności
5. `reservations-menu` i `reservation-edit` - Lista własnych rezerwacji i wspólne okno szczegółów wizyty
6. `profile-summary` i `user-profile-menu` - Podsumowanie konta i zarządzanie profilem
7. `employee-menu` - Zakładki z wizytami, statystykami i informacjami pracownika
8. `admin-menu` i `admin-reports` - Zarządzanie danymi i raporty administratora

### Uwierzytelnianie i autoryzacja

#### Middleware

- `auth` - Wymagane zalogowanie
- `verified` - Przypisany do tras panelu. Wymuszanie weryfikacji przez to middleware wymaga, aby model `User` implementował `MustVerifyEmail`. Obecny model `User` nie implementuje tego kontraktu
- `active` - Wylogowuje użytkownika, którego konto jest nieaktywne
- `role` - Kontrola dostępu oparta na rolach (administrator, pracownik, klient)

#### Polityki

- `ReservationPolicy` określa uprawnienia do tworzenia, podglądu, zmiany terminu, anulowania, potwierdzania i kończenia wizyt. Użytkownik widzi rezerwacje dokonane na swoim koncie, pracownik widzi także przypisane mu wizyty, a administrator ma wgląd we wszystkie. [ReservationPolicy.php](aplikacja/app/Policies/ReservationPolicy.php)
- Polityki `UserPolicy`, `EmployeePolicy`, `ServicePolicy` i `ServiceCategoryPolicy` kontrolują operacje na danych zarządzanych w panelu administratora. Raporty dodatkowo sprawdzają uprawnienie `view-reports`

### Przepływ rezerwacji

1. Użytkownik wybiera kategorię i aktywną usługę, a następnie pracownika przypisanego do tej usługi. Profil i konto pracownika muszą być aktywne. Pracownik nie może zarezerwować wizyty u siebie.
2. Użytkownik wybiera dzień i godzinę. Kalendarz tygodniowy pozwala przejść do sześciu tygodni naprzód, a kalendarz godzinowy proponuje godziny rozpoczęcia co 30 minut w przedziałach pracy, z uwzględnieniem czasu trwania usługi.
3. Przy zapisie formularz sprawdza poprawność danych i przypisanie usługi. W transakcji sprawdza, czy termin nie jest w przeszłości, czy cały przedział mieści się w jednym przedziale pracy oraz czy nie koliduje z inną rezerwacją. Nowa wizyta otrzymuje status `pending` i bieżącą cenę usługi.

### Kluczowe funkcje

#### 1. Zarządzanie rezerwacjami

Aktywna rezerwacja ma status `pending` lub `confirmed`.

| Operacja | Kto może wykonać | Warunki |
| --- | --- | --- |
| Anulowanie | Klient, który zarezerwował wizytę, przypisany pracownik lub administrator | Klient i pracownik mogą anulować aktywną wizytę do chwili jej rozpoczęcia, także w dniu wizyty. Administrator może anulować aktywną wizytę również po rozpoczęciu. |
| Zmiana terminu | Klient, który zarezerwował wizytę, lub administrator | Przed dniem aktywnej wizyty, jeśli profil pracownika jest aktywny. Zmiana przywraca status `pending` i zachowuje zapisaną cenę. |
| Potwierdzenie | Przypisany pracownik lub administrator | Przed rozpoczęciem wizyty ze statusem `pending`. |
| Zakończenie | Przypisany pracownik lub administrator | Od godziny rozpoczęcia aktywnej wizyty. |

Dezaktywacja usługi blokuje nowe rezerwacje. Termin istniejącej wizyty można nadal zmienić, jeśli usługa jest wciąż przypisana do pracownika i wizyta spełnia warunki zmiany terminu.

#### 2. Panele pracownika i administratora

- Pracownik przegląda własny grafik, usługi, wizyty i statystyki. Lista wizyt ma m.in. filtry „Do potwierdzenia” i „Do zakończenia”.
- Administrator zarządza użytkownikami, pracownikami, tygodniowym grafikiem, kategoriami, usługami i rezerwacjami. Zmiana godzin pracy zachowuje istniejące wizyty. Panel pracownika w widoku administratora wskazuje nadchodzące wizyty, które wypadają poza aktualnymi godzinami pracy.
- Raporty administratora grupują rezerwacje według kategorii, usług lub pracowników. Obejmują porównanie okresów, wykresy, obłożenie grafiku w raporcie pracowników, eksport CSV i wydruk. Przychód jest sumą zapisanych cen zakończonych wizyt. Raport osobno pokazuje wartość wizyt oczekujących i potwierdzonych oraz anulowanych. [Report.php](aplikacja/app/Support/Report.php)

#### 3. Konto i profil użytkownika

- Rejestracja, logowanie, resetowanie hasła i trasy weryfikacji adresu e-mail
- Zarządzanie profilem (dane osobowe, zmiana hasła)
- Możliwość dezaktywacji i usunięcia konta
- Normalizacja numeru telefonu

#### 4. Limity żądań

- 120 żądań na minutę dla zwykłych tras webowych i osobny limit 300 na minutę dla aktualizacji Livewire, według identyfikatora użytkownika lub adresu IP dla gościa
- 10 żądań na minutę na ścieżkę dla formularzy objętych `throttle:auth`, m.in. rejestracji, logowania i resetowania hasła

## Architektura techniczna

### Technologie

- Laravel 12.x z PHP 8.2+
- Eloquent z kluczami UUID w tabelach domenowych
- Domyślnie SQLite, z konfiguracją dla MySQL
- Blade, Livewire 4, Alpine.js, Tailwind CSS v4, Vite i Chart.js
- Domyślna strefa czasowa `Europe/Warsaw`, konfigurowana przez `APP_TIMEZONE`

### Zapis rezerwacji w bazie danych

Tworzenie i zmiana terminu używają tej samej walidacji terminu w `ReservationForm`. Po rozpoczęciu transakcji formularz wywołuje `lockForUpdate()` dla wiersza pracownika, pobiera czas trwania usługi i sprawdza pasujący wiersz grafiku również z `lockForUpdate()`. Brak pasującego przedziału lub wykrycie kolizji przerywa zapis i zwraca błąd formularza. W MySQL te odczyty blokują wiersze do końca transakcji. SQLite korzysta z własnego mechanizmu blokad transakcyjnych. [ReservationForm.php](aplikacja/app/Livewire/Forms/ReservationForm.php)

Zapytanie o kolizje pomija anulowane wizyty. Przy zmianie terminu pomija też rezerwację, której dotyczy aktualizacja. Dwa terminy mogą się stykać, jeśli jeden kończy się dokładnie w chwili rozpoczęcia drugiego. [Reservation.php](aplikacja/app/Models/Reservation.php)

### Bezpieczeństwo

- Ochrona przed CSRF
- Limity żądań
- Walidacja wprowadzanych danych
- Uwierzytelnianie oparte na Laravel Breeze i autoryzacja przez middleware oraz polityki
- Haszowanie haseł

## Konfiguracja środowiska deweloperskiego

Wszystkie polecenia uruchamiaj z katalogu `aplikacja/`.

### Instalacja

`composer setup` instaluje zależności, kopiuje `.env.example` do `.env`, jeśli plik jeszcze nie istnieje, generuje klucz aplikacji, uruchamia migracje i buduje zasoby interfejsu. Aby dodać dane z `DatabaseSeeder`, uruchom osobno `php artisan db:seed`.

Instalacja ręczna na nowej kopii projektu wygląda następująco. Jeśli używasz MySQL, wpisz ustawienia połączenia w `.env` przed uruchomieniem migracji.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
```

### Serwer deweloperski

```bash
# Uruchom serwer Laravel
php artisan serve

# Uruchom serwer, kolejkę, logi i Vite
composer dev

# Uruchom sam serwer Vite
npm run dev
```

### Testy

Testy uruchamiają się na SQLite w pamięci dzięki konfiguracji w `phpunit.xml`.

```bash
# Uruchom wszystkie testy
php artisan test

# Uruchom testy edycji i anulowania wizyt
php artisan test --filter=ReservationEditTest
```

## Trasy aplikacji

Trasy paneli są zdefiniowane w `routes/dashboard.php`. Operacje w widokach obsługują komponenty Livewire. Logowanie, rejestracja, resetowanie hasła i weryfikacja adresu e-mail są zdefiniowane w `routes/auth.php`.

| Trasa `GET` | Widok | Role |
| --- | --- | --- |
| `/dashboard` | Katalog usług i rezerwowanie wizyt | `client`, `employee`, `admin` |
| `/dashboard/profile` | Konto, dane osobowe, hasło i ustawienia | `client`, `employee`, `admin` |
| `/dashboard/reservations` | Lista własnych rezerwacji | `client`, `employee`, `admin` |
| `/dashboard/employee` | Panel pracownika | `employee`, `admin` |
| `/dashboard/admin` | Panel administratora | `admin` |
| `/dashboard/admin/reports` | Raporty administratora | `admin` |

## Wdrożenie

### Docker

W katalogu `aplikacja/` ustaw `APP_KEY` w `.env`. Klucz do wklejenia wygenerujesz poleceniem `php artisan key:generate --show` po zainstalowaniu zależności PHP.

```bash
docker compose up --build
```

Aplikacja jest dostępna pod adresem `http://localhost:8080`. Obraz buduje zasoby Vite w osobnym etapie i uruchamia aplikację na PHP 8.3 z Apache. Kontener przy starcie wykonuje migracje i domyślnie przechowuje bazę SQLite w wolumenie `app-storage`.

### Wdrożenie na Render

Konfigurację środowiska opisanego w `README.md` przekazuje się do kontenera przez zmienne środowiskowe. Dla MySQL ustaw `DB_CONNECTION=mysql`, parametry `DB_*` i `MYSQL_ATTR_SSL_CA` ze ścieżką do certyfikatu CA używanego przez połączenie TLS. [README.md](README.md)

## Strategia testowania

Aplikacja korzysta z Pest 3. Testy funkcjonalne w `aplikacja/tests/Feature/` odtwarzają schemat przez `RefreshDatabase` i przygotowują własne dane. Zrzut `szczegoly/Dump20261007.sql` zawiera dane demonstracyjne.

- `ReservationTest.php` sprawdza m.in. kolizje i odrzucenie terminu usuniętego z grafiku na początku transakcji.
- `ReservationEditTest.php` obejmuje zasady anulowania w dniu wizyty, granicę godziny rozpoczęcia oraz odrzucenie zmiany terminu po usunięciu go z grafiku.
- Testy paneli sprawdzają dostęp według ról, filtrowanie wizyt, dane raportów i eksport CSV.

## Rozwiązywanie problemów

- Przy błędzie połączenia sprawdź ustawienia bazy w `.env` i uruchom `php artisan config:clear`, jeśli konfiguracja była zapisana w pamięci podręcznej.
- Przy braku zależności lub zasobów uruchom `composer install`, `npm install` i `npm run build`.
- Przy błędzie zapisu sprawdź uprawnienia do `storage/` i `bootstrap/cache/`.

## Kierunki dalszego rozwoju


