# KIS Zadanie

Proste REST API biblioteki przygotowane jako zadanie rekrutacyjne. Aplikacja
umożliwia pobieranie autorów i książek oraz obsługuje wypożyczanie i zwracanie
książek.

## Stack

- PHP 8.4
- Symfony 8
- API Platform 4
- Doctrine ORM
- PostgreSQL 17
- PHPUnit 13
- Apache
- Docker Compose

## Uruchomienie

```bash
docker compose up
```

Pierwsze uruchomienie może potrwać dłużej ze względu na budowanie obrazu i
pobieranie zależności. Środowisko można uruchomić w tle:

```bash
docker compose up -d
```

Logi procesu inicjalizacji:

```bash
docker compose logs -f web
```

Po utworzeniu kontenerów usługa `web` wykonuje kolejno:

1. Czeka na gotowość PostgreSQL.
2. Pobiera branch `main` z repozytorium GitHub.
3. Uruchamia `composer install`.
4. Wykonuje migracje bazy `main`.
5. Tworzy bazę `main_test`, jeśli nie istnieje.
6. Wykonuje migracje testowe.
7. Uruchamia PHPUnit.
8. Dopiero po pomyślnym zakończeniu poprzednich kroków uruchamia Apache.

Jeżeli migracje lub testy zakończą się błędem, Apache nie zostanie uruchomiony.

## Adresy

- API: [http://localhost:8090/api](http://localhost:8090/api)
- Dokumentacja API Platform: [http://localhost:8090/api/docs](http://localhost:8090/api/docs)
- Adminer: [http://localhost:8091](http://localhost:8091)

Dane połączenia w Adminerze:

```text
System: PostgreSQL
Serwer: db
Użytkownik: main
Hasło: main
Baza danych: main
```

## Format API

Projekt korzysta z API Platform dla Symfony i wykorzystuje jego mechanizmy
serializacji, walidacji, routingu, paginacji oraz obsługi relacji.

Domyślnym formatem jest JSON-LD. Relacje między zasobami nie są przekazywane
jako surowe identyfikatory, lecz jako IRI zasobów. Przykładowo:

```json
{
  "title": "The Winter's Tale",
  "author": "/api/authors/1"
}
```

Implementacja została celowo uproszczona zgodnie z KISS. Endpointy zapisujące
korzystają bezpośrednio z encji Doctrine i walidacji Symfony, bez dodatkowych
klas DTO.

W przykładach należy przesyłać nagłówki:

```http
Content-Type: application/ld+json
Accept: application/ld+json
```

## Endpointy

### Pobranie autora

```http
GET /api/authors/{id}
```

Przykład:

```bash
curl http://localhost:8090/api/authors/1 \
  -H 'Accept: application/ld+json'
```

Przykładowa odpowiedź:

```json
{
  "@id": "/api/authors/1",
  "@type": "Author",
  "id": 1,
  "name": "William",
  "surname": "Shakespeare"
}
```

### Pobranie klienta

```http
GET /api/customers/{id}
```

Przykład:

```bash
curl http://localhost:8090/api/customers/100000 \
  -H 'Accept: application/ld+json'
```

### Lista książek

```http
GET /api/books?page={page}
```

Lista jest stronicowana. Liczba elementów na stronie jest określona w
`config/packages/api_platform.yaml` i wynosi domyślnie 5.

Przykład:

```bash
curl 'http://localhost:8090/api/books?page=1' \
  -H 'Accept: application/ld+json'
```

### Dodanie książki

```http
POST /api/books
```

Body:

```json
{
  "title": "The Winter's Tale",
  "author": "/api/authors/1"
}
```

Przykład:

```bash
curl -X POST http://localhost:8090/api/books \
  -H 'Content-Type: application/ld+json' \
  -H 'Accept: application/ld+json' \
  -d '{
    "title": "The Winter'\''s Tale",
    "author": "/api/authors/1"
  }'
```

Poprawne utworzenie zasobu zwraca status `201 Created`.

### Wypożyczenie książki

```http
POST /api/book-loans/borrow
```

Body:

```json
{
  "book": "/api/books/100001",
  "customer": "/api/customers/100000"
}
```

Przykład:

```bash
curl -X POST http://localhost:8090/api/book-loans/borrow \
  -H 'Content-Type: application/ld+json' \
  -H 'Accept: application/ld+json' \
  -d '{
    "book": "/api/books/100001",
    "customer": "/api/customers/100000"
  }'
```

Przed zapisem aplikacja sprawdza, czy książka i klient istnieją oraz czy
książka nie jest aktualnie wypożyczona. Próba ponownego wypożyczenia zwraca
`409 Conflict`.

### Zwrot książki

```http
POST /api/book-loans/return
```

Body:

```json
{
  "book": "/api/books/100003"
}
```

Przykład:

```bash
curl -X POST http://localhost:8090/api/book-loans/return \
  -H 'Content-Type: application/ld+json' \
  -H 'Accept: application/ld+json' \
  -d '{
    "book": "/api/books/100003"
  }'
```

Aplikacja wyszukuje najnowsze wypożyczenie książki i ustawia `returnedAt` na
bieżącą datę. Próba zwrotu książki, która nie jest wypożyczona, zwraca
`409 Conflict`.

## Błędy

Błędy API są zwracane jako `application/problem+json`. Odpowiedzi nie
udostępniają stosu wywołań ani pól takich jak `trace`, `file` lub `line`.

Nieznany URL zwraca `404 Not Found`, a użycie niedozwolonej metody dla
istniejącego endpointu zwraca `405 Method Not Allowed`.

## Testy

Testy są automatycznie uruchamiane podczas startu kontenera. Można je również
uruchomić ręcznie:

```bash
docker compose exec web vendor/bin/phpunit
```

Tylko testy jednostkowe:

```bash
docker compose exec web vendor/bin/phpunit --testsuite Unit
```

Tylko testy funkcjonalne:

```bash
docker compose exec web vendor/bin/phpunit --testsuite Functional
```

Testy funkcjonalne korzystają z osobnej bazy `main_test`. Każdy test działa w
transakcji wycofywanej po zakończeniu, dzięki czemu baza wraca do stanu
początkowego przed następnym testem.
