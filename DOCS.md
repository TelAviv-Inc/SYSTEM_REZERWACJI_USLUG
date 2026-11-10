# System Rezerwacji Usług - Dokumentacja Techniczna

## Omówienie

System Rezerwacji Usług to aplikacja internetowa do zarządzania rezerwacjami usług, zbudowana na podstawie frameworka Laravel. System umożliwia użytkownikom rezerwację usług u pracowników, zarządzanie dostępnością, konfliktami i rezerwacjami w sposób wydajny.

## Struktura Projektu

```
SYSTEM_REZERWACJI_USLUG/
├── aplikacja/              # Główna aplikacja Laravel
│   ├── app/                # Kod aplikacji
│   │   ├── Http/           # Kontrolery, middleware, żądania HTTP
│   │   ├── Models/         # Modele Eloquent
│   │   ├── Livewire/       # Komponenty Livewire
│   │   ├── Policies/       # Polityki autoryzacji
│   │   └── Support/        # Klasy wspomagające
│   ├── database/           # Migracje i seedery bazy danych
│   ├── resources/          # Widoki, CSS, JavaScript
│   ├── routes/             # Trasy aplikacji
│   └── tests/              # Zestaw testów
├── README.md               # Omówienie projektu
├── AGENTS.md               # Instrukcje dotyczące rozwoju
└── CLAUDE.md               # Wskazówki dla Claude AI
```

## Główne Komponenty

### Modele

#### Model Użytkownika (`app/Models/User.php`)
- Wykorzystuje UUID jako klucz główny
- Ma role: klient, pracownik, administrator
- Śledzi status aktywności użytkownika (aktywny / nieaktywny)
- Relacje:
  - `reservations()` - Jedno do wielu z rezerwacjami
  - `employee()` - Jedno do jednego z profilem pracownika

#### Model Kategorii Usługi (`app/Models/ServiceCategory.php`)
- Reprezentuje kategorie usług z ikonami
- Ma wiele usług
- Wykorzystuje UUID jako klucz główny

#### Model Usługi (`app/Models/Service.php`)
- Należy do kategorii usług
- Relacja wielu do wielu z pracownikami (przez tabelę pośrednią)
- Ma wiele rezerwacji
- Automatycznie generuje przyjazny dla URL slug na podstawie nazwy

#### Model Pracownika (`app/Models/Employee.php`)
- Należy do użytkownika (który jest pracownikiem)
- Relacja wielu do wielu z usługami
- Ma wiele rekordów dostępności i rezerwacji
- Zakres `bookable()` dla aktywnych pracowników z aktywnymi kontami

#### Model Dostępności Pracownika (`app/Models/EmployeeAvailability.php`)
- Reprezentuje godziny pracy pracowników
- Może być tygodniowe (day_of_week) lub określonych dat (specific_date)
- Rekordy oparte na czasie z start_time i end_time

#### Model Rezerwacji (`app/Models/Reservation.php`)
- Śledzi rezerwacje usług
- Zarządzanie statusami (oczekująca, potwierdzona, zakończona, anulowana)
- Wykrywanie konfliktów rezerwacji
- Relacje:
  - Należy do użytkownika, pracownika i usługi
  - Zakresy dla różnych typów statusów i okresów czasowych

### Schemat Bazy Danych

System korzysta z UUID jako kluczy głównych w całych modelach. Kluczowe tabele obejmują:

1. **users** - Konta użytkowników z rolami i statusem aktywności
2. **service_categories** - Kategorie usług z ikonami
3. **services** - Indywidualne usługi w kategoriach
4. **employees** - Profile pracowników powiązane z użytkownikami
5. **employee_services** - Tabela pośrednia dla relacji pracownik-usługa
6. **employee_availabilities** - Godziny pracy pracowników (tygodniowe lub określone daty)
7. **reservations** - Rekordy rezerwacji usług

### Komponenty Livewire

Interfejs użytkownika jest zbudowany z komponentów Livewire 4, korzystając z wzorca komunikacji opartej na zdarzeniach:

1. `show-services` - Wyświetla kategorie usług i usługi
2. `reserve-service` - Obsługuje proces rezerwacji (wybór pracownika, wyboru daty i godziny)
3. `employee-week-calendar` - Kalendarz do wyboru dni pracy
4. `employee-hour-calendar` - Kalendarz godzinowy dostępności
5. `profile-summary` - Omówienie profilu użytkownika
6. `user-profile-menu` - Karty zarządzania profilem

### Autoryzacja i Autoryzacja

#### Middleware
- `auth` - Wymagana autoryzacja
- `verified` - Wymagana weryfikacja e-maila  
- `active` - Konto użytkownika musi być aktywne (nie zawieszone)
- `role` - Kontrola dostępu oparta na rolach (administrator, pracownik, klient)

#### Polityki
- `ReservationPolicy` - Kontroluje tworzenie, przeglądanie, anulowanie i potwierdzanie rezerwacji

### Przepływ Procesu Rezerwacji

1. **Użytkownik wybiera kategorię usługi**
2. **Użytkownik wybiera konkretną usługę**
3. **System pokazuje dostępnych pracowników dla tej usługi**
4. **Użytkownik wybiera pracownika**
5. **Wybór daty i godziny za pomocą kalendarza**
6. **Weryfikacja rezerwacji na konflikty**
7. **Tworzenie rezerwacji z integralnością transakcji**

### Kluczowe Funkcje

#### 1. Zarządzanie Rezerwacjami
- Wykrywanie konfliktów czasowych za pomocą transakcji bazy danych i blokowania wierszy
- Obsługa dostępności zarówno tygodniowych, jak i określonych dat
- Zarządzanie statusami rezerwacji (oczekująca, potwierdzona, zakończona, anulowana)

#### 2. Kontrola Dostępu Oparta na Rolach
- Rola klienta: może tworzyć rezerwacje i zarządzać swoimi własnymi rezerwacjami
- Rola pracownika: może przeglądać i zarządzać swoim własnym harmonogramem i rezerwacjami
- Rola administratora: pełny dostęp do systemu zarządzania

#### 3. System Profilu Użytkownika
- Rejestracja i logowanie z weryfikacją e-maila
- Zarządzanie profilem (dane osobowe, zmiana hasła)
- Możliwość dezaktywacji i usunięcia konta
- Normalizacja numeru telefonu

#### 4. Ograniczenia Prędkości
- 120 żądań/minuty dla zalogowanych użytkowników
- 300 żąda段/minuty dla aktualizacji Livewire
- 10 żądań/minuty dla działań autoryzacyjnych
- Ochrona przed atakami siły bruto

## Architektura Techniczna

### Szczegóły Frameworku
- Laravel 12.x z PHP 8.2+
- Wykorzystuje UUID jako klucze główne dla wszystkich modeli
- SQLite domyślnie, MySQL w środowisku produkcyjnym
- Tailwind CSS v4 z Alpine.js dla interfejsu前端
- Livewire 4 dla interaktywnych komponentów interfejsu

### Operacje na Bazie Danych
- Tworzenie rezerwacji w transakcji z `lockForUpdate`, aby zapobiec konfliktom
- Wykrywanie konfliktów rezerwacji w zapytaniach do bazy danych
- Poprawne ograniczenia kluczy obcych i definicje relacji

### Funkcje Bezpieczeństwa
- Ochrona przed CSRF
- Ograniczenia prędkości
- Walidacja wprowadzanych danych
- Autoryzacja z wykorzystaniem szkieletu Laravel Breeze
- Bezpieczne przetwarzanie haseł z hashowaniem

## Konfiguracja Rozwoju

### Polecenia Instalacji
```bash
# Szybka instalacja
composer setup

# Krok po kroku instalacja
cd aplikacja
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
```

### Serwer Rozwoju
```bash
# Uruchom serwer rozwoju
php artisan serve

# Uruchom wszystkie usługi
composer dev

# Obserwuj zmiany w kodzie前端
npm run dev
```

### Testy
```bash
# Uruchom wszystkie testy
php artisan test

# Uruchom konkretny test
php artisan test --filter=ProfileTest

# Testuj bazę danych w pamięci
```

## Punkty końcowe API

System głównie korzysta z tras sieciowych z komponentami Livewire dla interaktywności, ale przestrzega standardowych konwencji Laravel:

- `/dashboard` - Główny dashboard z kategoriami usług
- `/dashboard/profile` - Zarządzanie profilem użytkownika
- `/dashboard/reservations` - Historia i zarządzanie rezerwacjami
- Trasy autoryzacyjne w `routes/auth.php`

## Wdrożenie

### Wdrożenie w Dockerze
```bash
docker compose up --build
```

### Środowisko Produkcyjne
- Używa MySQL (SQLite dla rozwoju)
- SSL do połączenia z bazą danych
- Platforma hostingu Render

## Strategia Testowania

Aplikacja korzysta z PestPHP do testowania z:
- Testami jednostkowymi dla modeli i logiki biznesowej
- Testami funkcjonalnymi dla interakcji użytkownika
- Testami integracyjnym些 dla złożonych przepływów pracy
- Danych testowych z `Dump20261007.sql`

## Rozważania dotyczące wydajności

### Optymalizacja Bazy Danych
- Poprawne indeksowanie kluczy obcych
- Efektywne zapytania do sprawdzania dostępności
- Zarządzanie transakcjami, aby zapobiec konfliktom

### Optymalizacja前端
- Komunikacja komponentów Livewire za pomocą zdarzeń
- Efektywne renderowanie kalendarza
- Odpowiedni projekt z Tailwind CSS

## Rozwiązywanie problemów

### Typowe Problemy
1. **Połączenie z bazą danych**: Upewnij się, że konfiguracja bazy danych w `.env` jest poprawna
2. **Brak zależności**: Uruchom `composer install` i `npm install`
3. **Problemy z migracjami**: Użyj `php artisan migrate:fresh --seed`, aby zresetować bazę danych
4. **Błędy uprawnień**: Sprawdź uprawnienia plików dla katalogów magazynowych

### Wskazówki dla Rozwoju
- Użyj `composer dev` dla pełnego środowiska rozwoju
- Skorzystaj z systemu zdarzeń Livewire do komunikacji między komponentami
- Testuj konflikty rezerwacji w scenariuszach z dostępem współbieżnym

## Przyszłe Wzmocnienia

### Planowane Funkcje
1. Zaawansowany panel raportów dla administratorów
2. Powiadomienia SMS o rezerwacjach
3. Rozszerzony widok kalendarza z zarządzaniem zasobami
4. Wsparcie wielojęzyczne
5. Integracja z zewnętrznymi systemami kalendarza