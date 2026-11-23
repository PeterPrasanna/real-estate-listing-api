# Real Estate Listing API 🏠

Portfolio project by Prasanna Peter — a REST API for real estate listings built with **Symfony 7.4, API Platform 4, PHP 8.3, PostgreSQL 16, Doctrine and JWT**.

> Personal project. Not affiliated with SeLoger or any former employer. No proprietary code or data is used.

## Features
- CRUD for property listings (apartment, house, land, commercial)
- User registration with hashed passwords and JWT login
- Authenticated users can create properties; owner-only edit/delete checks
- Data validation and automatic Swagger/OpenAPI documentation
- Docker Compose and PostgreSQL
- PHPUnit entity unit test

## Quick start

Requirements: Docker and Docker Compose.

```bash
docker compose up --build -d
```

Generate JWT keys and database tables:

```bash
docker compose exec app php bin/console lexik:jwt:generate-keypair
docker compose exec app php bin/console doctrine:schema:update --force
```

Visit **http://localhost:8000/api/docs**.

**Note:** This is a development scaffold. Dependency installation and end-to-end tests have not been verified in this environment. The generated JWT keys are local to the container. For persistence across rebuilds, mount `config/jwt` as a volume.



## Troubleshooting

If you encounter issues with Docker, Composer,
API Platform, Swagger UI or JWT authentication,
please refer to our troubleshooting guide:

[View Troubleshooting Guide](https://github.com/PeterPrasanna/real-estate-listing-api/blob/main/doc/TROUBLESHOOTING.md)


## API endpoints

| Method | Path | Access |
| --- | --- | --- |
| POST | `/api/register` | Public |
| POST | `/api/login` | Public |
| GET | `/api/properties` | Public |
| GET | `/api/properties/{id}` | Public |
| POST | `/api/properties` | Authenticated |
| PUT / PATCH | `/api/properties/{id}` | Owner |
| DELETE | `/api/properties/{id}` | Owner |

### Register

```bash
curl -X POST http://localhost:8000/api/register \
  -H 'Content-Type: application/ld+json' \
  -d '{"email":"developer@example.com","plainPassword":"MySecurePassword123!"}'
```

### Login

```bash
curl -X POST http://localhost:8000/api/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"developer@example.com","password":"MySecurePassword123!"}'
```

Use the returned token in the `Authorization: Bearer <token>` header.

### Create property

```bash
curl -X POST http://localhost:8000/api/properties \
  -H 'Content-Type: application/ld+json' \
  -H 'Authorization: Bearer YOUR_TOKEN' \
  -d '{"title":"Bright apartment","description":"Near the metro","price":285000,"city":"Lyon","postalCode":"69003","propertyType":"apartment"}'
```

## Roadmap
- [ ] Add Doctrine migrations and demo fixtures
- [ ] Add city/price/type filters and sorting
- [ ] Add integration tests for registration, JWT and ownership authorization
- [ ] Add CI (GitHub Actions), static analysis and coding standards
- [ ] Add pagination, rate limiting and structured logs

## Notes
- Prices are stored as whole euro amounts for simplicity.
- Development database credentials in `compose.yaml` are not production-safe.
- Never commit JWT private keys or real secrets.
- Run and verify all endpoints before publicly claiming production readiness.

## License
MIT



## Version 2 — filters, migrations, tests, CI

### Search examples

```http
GET /api/properties?city=Lyon
GET /api/properties?propertyType=apartment
GET /api/properties?price[gte]=150000&price[lte]=350000
```

Search uses API Platform Doctrine ORM filters. `city` supports partial matching, `propertyType` exact matching, and `price` range matching.

### Database migrations

For a **new, empty** database, run:

```bash
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```

**Existing database warning:** If you already ran `doctrine:schema:update --force`, do **not** apply the initial migration blindly: the tables may already exist. Back up your database and reconcile the migration state, or use a fresh local database.

### Tests

Integration tests are in `tests/Api/PropertyApiTest.php` and require a separate migrated test database. They cover public search, anonymous write protection, and owner-only deletion. Run them only against the test database:

```bash
docker compose exec -e APP_ENV=test -e DATABASE_URL='postgresql://app:app@database:5432/real_estate_test?serverVersion=16&charset=utf8' app php bin/phpunit
```

Create `real_estate_test` and apply migrations to it before running the tests. For example: `docker compose exec database createdb -U app real_estate_test`, then run the migration command with `APP_ENV=test` and a test-only `DATABASE_URL`. The test suite clears its test data and must **never** point at a development or production database.

### GitHub Actions

`.github/workflows/ci.yml` sets up PHP 8.3 and PostgreSQL 16, installs Composer dependencies, generates development-only JWT keys, applies migrations and runs PHPUnit.

### Validation status

This V2 is a **proposed implementation**, not a verified green build. Review and execute it locally before presenting the CI badge as passing. In particular, verify the installed API Platform version's filter and test-client behavior and adjust the test setup if necessary.
