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

## Troubleshooting

If you encounter issues with Docker, Composer,
API Platform, Swagger UI or JWT authentication,
please refer to our troubleshooting guide:

[View Troubleshooting Guide](docs/TROUBLESHOOTING.md)

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
