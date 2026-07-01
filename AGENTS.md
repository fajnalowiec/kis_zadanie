# AGENTS.md

## Cel projektu

Projekt jest zadaniem rekrutacyjnym polegajacym na przygotowaniu prostego,
czytelnego i poprawnie przetestowanego REST API udostepniajacego kilka
endpointow.

Projektuj API zgodnie z modelem dojrzalosci Richardsona:

1. Udostepniaj zasoby pod jednoznacznymi URI.
2. Uzywaj metod HTTP zgodnie z ich przeznaczeniem (`GET`, `POST`, `PUT`,
   `PATCH`, `DELETE`).
3. Zwracaj poprawne kody statusu HTTP i spojne reprezentacje bledow.
4. Tam, gdzie ma to sens, udostepniaj linki prowadzace do powiazanych zasobow.

Docelowo API powinno osiagac poziom 3 modelu Richardsona, wykorzystujac
mozliwosci API Platform, w tym dokumentacje OpenAPI i format Hydra.

## Stack technologiczny

- PHP 8.4
- Apache HTTP Server
- Symfony 8+
- API Platform 4+
- PostgreSQL 17
- Composer uruchamiany wewnatrz kontenera WWW
- Docker i Docker Compose
- PHPUnit

Wersje i konfiguracja infrastruktury zdefiniowane w plikach
`Dockerfile.dev-www-image` oraz `docker-compose.yml` sa nadrzednym zrodlem
informacji o srodowisku.

## Struktura projektu

Stosuj standardowa strukture Symfony i API Platform:

- `src/ApiResource` - zasoby API, gdy nie sa bezposrednio encjami Doctrine
- `src/Entity` - encje Doctrine
- `src/Repository` - repozytoria Doctrine
- `src/State` - providery i processory API Platform
- `src/Controller` - tylko kontrolery wymagane poza standardowym mechanizmem API Platform
- `src/Service` - serwisy aplikacyjne i logika biznesowa
- `config` - konfiguracja Symfony i API Platform
- `migrations` - migracje Doctrine
- `tests/Unit` - testy jednostkowe
- `tests/Functional` - testy endpointow i integracji

Nie tworz nowych warstw ani katalogow bez rzeczywistej potrzeby.

## Zasady kodowania

- Stosuj aktualne standardy PSR przyjete przez Symfony, w szczegolnosci
  PSR-1, PSR-4 i PSR-12.
- W nowych plikach PHP dodawaj po znaczniku otwierajacym:
  `declare(strict_types=1);`, o ile dany typ pliku lub uzywane narzedzie tego
  nie wyklucza.
- Korzystaj z typowania argumentow, wartosci zwracanych i wlasciwosci.
- Stosuj atrybuty PHP zamiast adnotacji.
- Preferuj `final` dla klas, ktore nie sa przeznaczone do dziedziczenia.
- Uzywaj wstrzykiwania zaleznosci przez konstruktor.
- Nie umieszczaj logiki biznesowej w kontrolerach, encjach ani konfiguracji.
- Waliduj dane wejsciowe za pomoca Symfony Validator.
- Zwracaj bledy w spojnym formacie obslugiwanym przez API Platform.
- Nie dodawaj zaleznosci Composer bez uzasadnionej potrzeby.

## Zasady projektowania

- KISS - wybieraj najprostsze rozwiazanie spelniajace wymagania.
- DRY - usuwaj istotne powtorzenia, ale unikaj przedwczesnych abstrakcji.
- SOLID - zachowuj pojedyncza odpowiedzialnosc i czytelne granice zaleznosci.
- Preferuj mechanizmy dostarczane przez Symfony i API Platform zamiast
  wlasnych implementacji tego samego zachowania.
- Optymalizuj kod pod katem czytelnosci i latwosci oceny podczas rekrutacji.

## REST API

- Nazwy endpointow powinny opisywac zasoby, a nie wykonywane akcje.
- Stosuj liczbe mnoga w URI kolekcji.
- Nie umieszczaj czasownikow w URI, jezeli operacje mozna wyrazic metoda HTTP.
- Rozrozniaj odpowiedzi `200`, `201`, `204`, `400`, `404`, `409` i `422`
  zgodnie ze znaczeniem operacji.
- Zachowuj idempotentnosc metod `PUT` i `DELETE`.
- Dokumentacja OpenAPI generowana przez API Platform musi odpowiadac
  faktycznemu zachowaniu endpointow.

## Testy

- Tworz testy jednostkowe w PHPUnit dla logiki biznesowej.
- Nie tworz testow jednostkowych dla samych encji Doctrine.
- Testy jednostkowe musza byc szybkie i niezalezne od bazy danych oraz sieci.
- Dla endpointow tworz testy funkcjonalne obejmujace poprawne odpowiedzi,
  walidacje, bledy i najwazniejsze kody HTTP.
- Kazda naprawa bledu powinna zawierac test zapobiegajacy regresji.
- Stosuj czytelny schemat nazw: zachowanie, warunek i oczekiwany rezultat.

## GitFlow

- `main` zawiera stabilne wersje projektu.
- `develop` jest glowna galezia integracyjna.
- Nowe funkcje tworz na galeziach `feature/<nazwa>`.
- Poprawki tworz na galeziach `fix/<nazwa>`.
- Pilne poprawki wersji stabilnej tworz na galeziach `hotfix/<nazwa>`.
- Nie wykonuj bezposrednich commitow do `main`.
- Commity powinny byc male, spojne tematycznie i miec czytelne komunikaty.

## Definicja ukonczenia

Zadanie jest ukonczone, gdy:

- implementacja spelnia wymagania biznesowe,
- API zachowuje sie zgodnie z semantyka HTTP,
- walidacja i obsluga bledow sa kompletne,
- testy jednostkowe i funkcjonalne przechodza,
- dokumentacja OpenAPI jest aktualna,
- migracje bazy danych sa dolaczone i dzialaja,
- kod nie zawiera nieuzywanych klas, plikow ani zaleznosci,
- cale srodowisko uruchamia sie poleceniem `docker compose up -d`.
