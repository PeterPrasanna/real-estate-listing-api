# Troubleshooting Guide

This guide documents common setup and runtime issues encountered while developing the **Real Estate Listing API** (Symfony 7.4, API Platform 4, PostgreSQL, Docker Compose and JWT authentication).

> Commands are intended to run from the repository root in PowerShell, unless otherwise stated. Do not commit secrets, private keys or real passwords. Some fixes below are diagnostic steps and have not yet been confirmed as resolving every environment's issue.

## 1. Docker Desktop Linux engine is unavailable

**Symptom**

```text
failed to connect to the docker API at npipe:////./pipe/dockerDesktopLinuxEngine
open //./pipe/dockerDesktopLinuxEngine: The system cannot find the file specified
```

**Likely cause:** Docker Desktop is not running, or its Linux engine / WSL 2 backend is not available.

**Steps**

1. Open Docker Desktop and wait until the engine reports that it is running.
2. Check that both the client and server are accessible:

   ```powershell
   docker version
   docker info
   ```

3. If the Linux engine still cannot start, inspect WSL:

   ```powershell
   wsl --status
   wsl --list --verbose
   ```

4. If WSL is not installed, follow Microsoft's WSL installation instructions. An elevated PowerShell session can typically use `wsl --install`; restart Windows if requested.
5. Retry:

   ```powershell
   docker compose up --build -d
   ```

## 2. Composer blocks the `symfony/runtime` plugin

**Symptom**

```text
symfony/runtime contains a Composer plugin which is blocked by your allow-plugins config.
```

**Cause:** Composer requires explicit authorization for plugins when installing non-interactively.

**Fix:** In `composer.json`, add `symfony/runtime` to the existing `config.allow-plugins` object (do not replace your other dependencies):

```json
"config": {
  "allow-plugins": {
    "php-http/discovery": true,
    "symfony/flex": true,
    "symfony/runtime": true
  },
  "sort-packages": true
}
```

Then rebuild:

```powershell
docker compose build --no-cache
docker compose up -d
```

Only authorize Composer plugins you trust.

## 3. API Platform Doctrine ORM integration is missing

**Symptom**

```text
In ApiPlatformExtension.php line 843:
Doctrine support cannot be enabled as the doctrine ORM component is not installed.
Try running "composer require api-platform/doctrine-orm".
```

**Cause:** `doctrine/orm` alone does not provide the API Platform Doctrine integration package.

**Fix:** Add the integration dependency to your project, using a version compatible with your installed API Platform 4 packages:

```bash
composer require api-platform/doctrine-orm
```

If Composer is only available inside a running app container:

```powershell
docker compose exec app composer require api-platform/doctrine-orm
```

If the app cannot start, run Composer in a suitable temporary PHP environment with the required PHP extensions, or add the dependency to `composer.json` and regenerate `composer.lock` there. Commit both files together. Do not use `--ignore-platform-reqs` to conceal missing extensions.

Then rebuild and verify Symfony:

```powershell
docker compose up --build -d
docker compose exec app php bin/console about
```

## 4. API documentation appears unstyled or blank

**Symptom:** `http://localhost:8000/api/docs` shows the API Platform name, links to JSON-LD/OpenAPI/ReDoc/Scalar and image links, but not the expected interactive Swagger UI.

**Possible causes:** Missing or inaccessible static assets, JavaScript errors, browser cache, or an API Platform UI configuration issue. The exact cause has not yet been confirmed.

**Diagnostic steps**

1. Install Symfony bundle assets and clear the cache:

   ```powershell
   docker compose exec app php bin/console assets:install public
   docker compose exec app php bin/console cache:clear
   ```

2. Hard-refresh the page (`Ctrl + F5`).
3. Check whether `http://localhost:8000/bundles/apiplatform/logo-header.svg` loads.
4. Try `http://localhost:8000/api/docs?ui=scalar` or `http://localhost:8000/api/docs?ui=re_doc`.
5. Open browser developer tools (`F12`) and inspect **Console** and **Network** for failed CSS/JS requests or HTTP 404/500 responses.
6. Review `config/packages/api_platform.yaml` and confirm that Swagger UI is enabled if explicitly configured:

   ```yaml
   api_platform:
       enable_swagger_ui: true
   ```

   Keep existing project settings such as `title`, `version` and `formats`.

**Status:** The API documentation endpoint responded, but a confirmed fix for the UI rendering issue has not yet been established.

## 5. JWT token encoding fails (HTTP 500)

**Symptom**

```text
Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException
An error occurred while trying to encode the JWT token.
Please verify your configuration (private key/passphrase).
```

**Likely cause:** Missing/unreadable JWT private key, incorrect passphrase, or inconsistent environment variables.

**Steps**

1. Verify the JWT key files exist:

   ```powershell
   docker compose exec app ls -l config/jwt
   ```

   Expected files: `private.pem` and `public.pem`.

2. Check `config/packages/lexik_jwt_authentication.yaml`:

   ```yaml
   lexik_jwt_authentication:
       secret_key: '%env(resolve:JWT_SECRET_KEY)%'
       public_key: '%env(resolve:JWT_PUBLIC_KEY)%'
       pass_phrase: '%env(JWT_PASSPHRASE)%'
       token_ttl: 3600
   ```

3. Verify the effective `JWT_SECRET_KEY`, `JWT_PUBLIC_KEY` and `JWT_PASSPHRASE` settings in `.env`, `.env.local` and Docker environment configuration. **Never paste or publish real passphrases.**
4. For a local development environment only, if the keys are missing or need replacement, regenerate them:

   ```powershell
   docker compose exec app php bin/console lexik:jwt:generate-keypair --overwrite
   docker compose exec app php bin/console cache:clear
   ```

   **Warning:** `--overwrite` replaces the keys and invalidates JWTs signed with the previous private key. Ensure the generation command uses the intended passphrase.

5. Retry `POST /api/login` with an existing test user. A successful response should include a `token` property. Never share a live JWT in public logs or GitHub issues.

If it still fails, inspect the effective bundle configuration without exposing secrets:

```powershell
docker compose exec app php bin/console debug:config lexik_jwt_authentication
```

**Status:** The HTTP 500 was observed; the final fix has not yet been confirmed.

## 6. Useful diagnostic commands

```powershell
# Container status
docker compose ps

# Application logs
docker compose logs --tail=100 app

# Symfony version and runtime
docker compose exec app php bin/console about

# Registered API routes
docker compose exec app php bin/console debug:router

# Database connectivity (requires PostgreSQL running)
docker compose exec app php bin/console doctrine:query:sql "SELECT 1"
```

## Reporting a new issue

Include the command run, the exact error text, your OS, Docker version, PHP/Symfony versions, and relevant **redacted** logs. Do not post `.env.local`, JWT private keys, access tokens, passwords or other credentials.
