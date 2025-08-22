# Development Guidelines — openEHR MCP Server (PHP)

This document captures project-specific knowledge to speed up development and troubleshooting. It assumes familiarity with PHP 8.4, Composer, Docker, PHPUnit, and PHPStan.

## Build and Configuration

- PHP runtime: PHP 8.4+
- Package manager: Composer
- Containerization: Docker / Docker Compose (WSL2 on Windows)
- Main entrypoint: `php server.php` (supports `--transport=stdio` and `--transport=streamable-http`)
- Environment file: `.env` (template at `.env.example`)

Recommended workflows

1) Local development (no Docker for the app)
- Install dependencies: `composer install`
- Create env file: `cp .env.example .env` and adjust values as needed:
  - `OPENEHR_API_BASE_URL` (e.g., `http://localhost:8080/ehrbase/rest/openehr`)
  - `LOG_LEVEL` (debug during development is useful)
  - `HTTP_TIMEOUT`, `HTTP_SSL_VERIFY` as needed
- Run the server:
  - `php server.php --transport=stdio`
  - or: `php server.php --transport=streamable-http` (listens on port `8242` at `/mcp_openehr`)

2) Local stack with Docker (optional EHRbase and Postgres)
- Start only EHRbase backing services: `docker compose --profile ehrbase up -d` (or `docker compose up -d ehrbase ehrdb`)
- EHRbase will be exposed on `http://localhost:8080`
- Check health: `docker compose ps`

3) Containerized server
- Production image: `docker compose up -d mcp`
- Development image: `docker compose --profile dev up -d mcp-dev` (bind-mounts source; command: `php server.php`)

WSL2/Docker specifics and running commands via mcp-dev
- This project is developed using Docker on WSL2. Always run PHP, Composer, and tooling inside the mcp-dev container to ensure consistent environment.
- Start dev container: `docker compose --profile dev up -d mcp-dev`
- Execute commands inside container (examples):
  - Install deps: `docker compose exec mcp-dev composer install`
  - Update deps: `docker compose exec mcp-dev composer update`
  - Run the server (stdio): `docker compose exec mcp-dev php server.php --transport=stdio`
  - Run the server (streamable HTTP): `docker compose exec mcp-dev php server.php --transport=streamable-http`
  - PHPUnit: `docker compose exec mcp-dev composer test`
  - Coverage: `docker compose exec mcp-dev composer test:coverage`
  - PHPStan: `docker compose exec mcp-dev composer check:phpstan`
  - PHP linter/fixer (if applicable): `docker compose exec mcp-dev composer fix` or `docker compose exec mcp-dev vendor/bin/php-cs-fixer fix`
- If you need to run php-lic or custom PHP binaries, prefix with `docker compose exec mcp-dev`: e.g., `docker compose exec mcp-dev php vendor/bin/php-lic --help`.
- On Windows, edit files from the WSL filesystem (e.g., \\wsl.localhost\<distro>\...) to avoid performance issues; volumes are already configured in docker-compose.yml for mcp-dev.

Notes on Dockerfile
- Multi-stage build: base, development (xdebug, composer, git), vendor-builder (prod deps), production (minimal app)
- PHP ini overlays: `docker/php/*.ini`
- Exposes port `8242` for streamable HTTP transport

## Testing

- PHPUnit 12 configuration at tests/phpunit.xml; tests live under tests/ (PSR-4: Code24\\OpenEHR\\MCP\\Server\\Tests)

Running tests
- Locally: `composer test`
- Coverage: `composer test:coverage` (ensure `XDEBUG_MODE=coverage` is set by script)

Static analysis
- PHPStan config at `tests/phpstan.neon`
- Run: `composer check:phpstan`

Test structure guidelines
- Namespace: `Code24\\OpenEHR\\MCP\\Server\\Tests`
- File naming: `*Test.php`
- Avoid networked tests by default; mock HTTP where possible.

## Additional Development Notes

MCP client compatibility
- Verified with `Claude Desktop`; compatible with `LibreChat.ai` MCP integration (stdio and streamable HTTP).

Logging
- Monolog is used; log level controlled via `LOG_LEVEL`.

MCP discovery
- MCP Tools and Prompts are discovered via attributes in `server.php` scanning `src/Tools` and `src/Prompts`.
- Add new tools under `src/Tools` and annotate public methods with `#[McpTool(name: '...')]`. Keep signatures minimal and validate inputs defensively within the tool.

Environment and configuration
- `.env.example` documents supported variables: `APP_ENV`, `LOG_LEVEL`, `OPENEHR_API_BASE_URL`, `HTTP_TIMEOUT`, `HTTP_SSL_VERIFY`.
- Authorization headers are not configured; if needed, extend `server.php` to add them to the Guzzle client.

EHRbase dependency
- Keep integration with EHRbase optional; unit tests should not require a live EHRbase instance.
- For manual testing, start the EHRbase profile via `docker compose` and point `OPENEHR_API_BASE_URL` to `http://localhost:8080/ehrbase/rest/openehr`.

Troubleshooting
- Coverage warnings: ensure `XDEBUG_MODE=coverage` when requesting coverage.
- Port conflicts on `8242`: adjust published port in `docker-compose.yml` if needed.
- SSL issues: set `HTTP_SSL_VERIFY=false` for local self-signed scenarios (not recommended for production).

CI/automation tips
- Prefer deterministic runs with the development container: `docker compose run --rm --profile dev mcp-dev composer test`.
- For ad-hoc commands in CI, always target the mcp-dev service and avoid host-level PHP/Composer.

Release notes
- Project version is tracked in `src/constants.php` (`APP_VERSION`). Follow semver for breaking changes to MCP Tool interfaces.
