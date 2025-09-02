# Changelog

All notable changes to this project will be documented in this file.

The format is based on Keep a Changelog, and this project adheres to Semantic Versioning.

- Keep a Changelog: https://keepachangelog.com/en/1.1.0/
- Semantic Versioning: https://semver.org/spec/v2.0.0.html

## [Unreleased]

### Added
- CKM API integration: new CKM client configured via `CKM_API_BASE_URL` with default `https://ckm.openehr.org/ckm/rest/v1/`.
- New Tools: `ckm_archetype_list`, `ckm_archetype_get`.
- New Prompt: `ckm_archetype_explorer`.

## [0.1.0] - 2025-09-01

Initial public release.

### Added
- PHP-based MCP server for openEHR integration.
- Core tools and prompts (EHR management, composition operations, stored queries, vital sign capture, medication review, patient assessment, template management).
- Configuration via environment variables (APP_ENV, LOG_LEVEL, OPENEHR_API_BASE_URL, HTTP_SSL_VERIFY, HTTP_TIMEOUT).
- Logging via Monolog.
- HTTP client via Guzzle.
- PHPUnit tests and PHPStan configuration.
- Dockerfile and docker-compose setup for local development.
- Documentation and contribution guidelines.

[Unreleased]: https://github.com/code24-nl/openehr-mcp-server/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/code24-nl/openehr-mcp-server/releases/tag/v0.1.0