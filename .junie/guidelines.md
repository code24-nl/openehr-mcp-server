# Project Development Guidelines (Junie Notes)

Audience: Advanced developers contributing to code24-nl/openehr-mcp-server. This document captures project-specific build, testing, and debugging knowledge verified against the current repo state and Docker workflows.

Date verified: 2025-09-02

## 1) Build / Configuration (project-specific)

- Runtime: PHP 8.4. Use Docker (preferred) for a consistent environment; host PHP is not required.
- Entrypoint: php server.php with transports:
  - --transport=stdio
  - --transport=streamable-http (default when omitted; HTTP on :8242 at path /mcp_openehr)
- Environment: .env (copy from .env.example). Key vars:
  - OPENEHR_API_BASE_URL (e.g., http://localhost:8080/ehrbase/rest/openehr)
  - CKM_API_BASE_URL (default https://ckm.openehr.org/ckm/rest)
  - HTTP_TIMEOUT (float seconds), HTTP_SSL_VERIFY (bool or path)
  - LOG_LEVEL (Monolog)
- Docker services (from docker-compose.yml):
  - mcp: production image (runs server)
  - mcp-dev: development image (bind mounts source; run commands inside this)
  - ehrbase stack (optional profile ehrbase) for manual testing

Typical dev flow (Docker-only)
1. Start dev container:
   - docker compose --profile dev up -d mcp-dev
2. Prepare env:
   - cp .env.example .env
   - edit .env as needed (see variables above)
3. Install deps inside container:
   - docker compose exec mcp-dev composer install
4. Run server inside container (pick one):
   - docker compose exec mcp-dev php server.php --transport=stdio
   - docker compose exec mcp-dev php server.php --transport=streamable-http

Optional backing EHRbase (for manual integration checks)
- docker compose --profile ehrbase up -d
- EHRbase: http://localhost:8080

Notes
- The repo’s Dockerfile is multi-stage; dev image includes tooling, prod is minimal. Port 8242 is exposed for streamable HTTP.
- If you need auth to your openEHR server, extend server.php to add Guzzle Authorization headers; not configured by default.

## 2) Testing

- Framework: PHPUnit 12; config: tests/phpunit.xml
- Autoloading: PSR-4 for tests Code24\\OpenEHR\\MCP\\Server\\Tests (composer.json autoload-dev).
- Composer scripts (run inside mcp-dev container):
  - Tests (no coverage): docker compose exec mcp-dev composer test
  - Coverage (HTML in var/phpunit/code-coverage): docker compose exec mcp-dev composer test:coverage
  - Static analysis (PHPStan): docker compose exec mcp-dev composer check:phpstan

Running the existing suite (verified)
- With mcp-dev running, docker compose exec mcp-dev composer test executes PHPUnit with tests/phpunit.xml and XDEBUG_MODE=off. Coverage and PHPStan also run via the scripts above.

Adding a new test
- Location: tests/ under namespace Code24\\OpenEHR\\MCP\\Server\\Tests
- Naming: *Test.php
- Keep tests unit/integration without depending on a live EHRbase; mock HTTP.
- Example minimal test (works when placed under tests/Helpers or tests/):

  <?php
  declare(strict_types=1);
  
  namespace Code24\OpenEHR\MCP\Server\Tests;
  
  use PHPUnit\Framework\TestCase;
  
  final class SmokeTest extends TestCase
  {
      public function test_truth(): void
      {
          $this->assertTrue(true);
      }
  }

Execution
- After adding the file, run: docker compose exec mcp-dev composer test
- For a subset: docker compose exec mcp-dev vendor\bin\phpunit --filter SmokeTest

Coverage
- Coverage script sets XDEBUG_MODE=coverage for you: docker compose exec mcp-dev composer test:coverage

Cleanup policy for this note
- Do not leave ad-hoc test files in the repo; remove sample tests after local verification. This document itself is the only artifact to commit.

## 3) Additional Development Information

Code style and QA
- Coding standard: PSR-12. Use PHP CS Fixer (or IDE equivalent) if configured; not enforced by a composer script in this repo at this time.
- Keep methods small; prefer typed signatures. Add phpdoc only when types aren’t self-evident.
- Run full test + static analysis before pushing: composer test; composer check:phpstan.

MCP-specific conventions
- Tools live in src/Tools; annotate public methods with #[McpTool(name: '...')] for discovery by php-mcp/server in server.php.
- Prompts live in src/Prompts; keep prompt descriptions focused on guiding tool orchestration.
- Server transports: stdio for desktop MCP clients (e.g., Claude), streamable HTTP for web clients (e.g., LibreChat). Default is streamable HTTP; pass --transport to override.

Logging and debugging
- Set LOG_LEVEL=debug during development to get detailed Monolog output.
- HTTP client controls via HTTP_TIMEOUT and HTTP_SSL_VERIFY for local/self-signed environments. For SSL issues in dev, set HTTP_SSL_VERIFY=false (not for production).

Versioning
- Application version is defined in src/constants.php (APP_VERSION). Follow semver; update when making breaking MCP Tool interface changes.

WSL2/Docker tips
- Always execute PHP/Composer inside the mcp-dev container to match the project’s PHP 8.4 and extensions (json, curl). On Windows, prefer editing within the WSL filesystem for performance.

Known pitfalls
- Port 8242 conflicts: adjust docker-compose.yml published port.
- Coverage requires Xdebug; use the provided composer script which sets XDEBUG_MODE.
- If your IDE or host attempts to run vendor/bin/phpunit directly, ensure it runs inside the container or uses PHP 8.4 with required extensions.
