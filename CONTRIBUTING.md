# Contributing to openehr-mcp-server

Thank you for your interest in contributing! This document explains how to set up your environment, propose changes, and follow our conventions so that we can review and merge your work efficiently.

This guide is self‑contained; you do not need to read guidelines.md. The most relevant development and testing instructions have been incorporated here per GitHub best practices for CONTRIBUTING files.


## Table of contents
- Code of Conduct
- Getting help and asking questions
- Project setup (Docker)
- Environment configuration
- Running the server (stdio and streamable HTTP)
- Running tests and coverage
- Static analysis and code style
- MCP conventions (Tools and Prompts)
- Troubleshooting tips
- Commit messages and pull requests
- Branching, issues, and release notes
- Versioning
- Security


## Code of Conduct
Please be respectful and constructive. By participating, you agree to uphold a professional and inclusive environment. If you encounter unacceptable behavior, contact the maintainers privately via the repository’s security/contact channels.


## Getting help and asking questions
- For usage questions, open a GitHub Discussion (if enabled) or a Question issue with a minimal reproducible example.
- For bugs, open an Issue and include: expected behavior, actual behavior, steps to reproduce, environment details, and logs if relevant.
- For feature requests, explain the use‑case and proposed API/UX.


## Project setup (Docker)
Prerequisites:
- Docker and Docker Compose

Recommended developer workflow (inside Docker):
1. git clone <your-fork-url>
2. cd openehr-mcp-server
3. docker compose --profile dev up -d mcp-dev
4. cp .env.example .env
5. docker compose exec mcp-dev composer install

Notes
- Always execute PHP/Composer inside the mcp-dev container to match PHP 8.4 and extensions.
- On Windows/WSL2, edit within the WSL filesystem for performance.


## Environment configuration
Edit .env and set at least:
- OPENEHR_API_BASE_URL (e.g., http://localhost:8080/ehrbase/rest/openehr)
- CKM_API_BASE_URL (default https://ckm.openehr.org/ckm/rest)
- LOG_LEVEL (set to debug during development if needed)
- HTTP_TIMEOUT (float seconds), HTTP_SSL_VERIFY (bool or CA path)

Optional backing services for manual checks
- docker compose --profile ehrbase up -d
- EHRbase will be available at http://localhost:8080


## Running the server (stdio and streamable HTTP)
Inside the dev container:
- Stdio: docker compose exec mcp-dev php server.php --transport=stdio
- Streamable HTTP (default): docker compose exec mcp-dev php server.php --transport=streamable-http
  - HTTP server listens on port 8242 at path /mcp_openehr


## Running tests and coverage
- Full test suite: docker compose exec mcp-dev composer test
- Run a subset: docker compose exec mcp-dev vendor\bin\phpunit --filter SomeTest
- Coverage HTML report: docker compose exec mcp-dev composer test:coverage

Testing guidelines
- Tests live under tests/ with namespace Code24\\OpenEHR\\MCP\\Server\\Tests
- Name files *Test.php; keep tests unit/integration and mock HTTP instead of calling live EHRbase.


## Static analysis and code style
- Coding standard: PSR-12. Use PHP CS Fixer (or IDE) if available.
- Static analysis (PHPStan): docker compose exec mcp-dev composer check:phpstan
- Keep methods small; use typed signatures; add phpdoc where types aren’t obvious.


## MCP conventions (Tools and Prompts)
- Tools live in src/Tools; annotate public methods with #[McpTool(name: '...')] for discovery by php-mcp/server in server.php.
- Prompts live in src/Prompts; keep prompt descriptions focused on guiding tool orchestration.


## Troubleshooting tips
- Port 8242 conflicts: adjust published port in docker-compose.yml.
- Coverage requires Xdebug; use the composer test:coverage script which sets XDEBUG_MODE.
- SSL issues in dev: set HTTP_SSL_VERIFY=false (do not use in production).


## Commit messages and pull requests
- Use conventional commits when possible (feat:, fix:, docs:, refactor:, test:, chore:).
- Write descriptive titles and include context in the body: what, why, how, and risks.
- One logical change per PR. Large changes can be split into smaller PRs.
- Run the full test suite and static analysis locally before pushing.
- Link related issues using GitHub keywords (Fixes #123).

PR checklist:
- Tests added/updated
- Docs updated if needed
- No debug code or leftover comments
- All checks (CI) pass


## Branching, issues, and release notes
- Default branch: main
- Create feature branches from main: feature/short-description or fix/short-description
- We follow SemVer for releases and maintain a CHANGELOG.md (Keep a Changelog format recommended).


## Versioning
- Application version is defined in src/constants.php (APP_VERSION). Update it when making breaking MCP Tool interface changes.


## Security
Do not open public issues for security vulnerabilities. Instead, please report privately using GitHub’s security advisories or the contact method listed in SECURITY.md if present. If not available, email the maintainers.

Thank you for contributing!