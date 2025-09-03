# Changelog

All notable changes to this project will be documented in this file.

The format is based on Keep a Changelog, and this project adheres to Semantic Versioning.

- Keep a Changelog: https://keepachangelog.com/en/1.1.0/
- Semantic Versioning: https://semver.org/spec/v2.0.0.html

## [Unreleased]

## [0.4.0] - 2025-09-03

### Changed

- update stored query execution signature and corresponding tests, rename `openehr_query_stored_execute` to `openehr_stored_query_execute`

## [0.3.2] - 2025-09-03

### Changed

- Changed method signatures to enforce primitive types in mcp-tools schema with default argument values 
- Documentation and stability fixes

## [0.3.0] - 2025-09-03

### Added

- openEHR Type specification support: new service based on BMM JSON file-based specifications.
- New Tools: `openehr_type_specification_list`, `openehr_type_specification_get`.
- New Prompt: `openehr_type_specification_explorer`.

## [0.2.0] - 2025-09-02

### Added
- CKM API integration: new CKM client configured via `CKM_API_BASE_URL` with default `https://ckm.openehr.org/ckm/rest`.
- New Tools: `ckm_archetype_list`, `ckm_archetype_get`.
- New Prompt: `ckm_archetype_explorer`.

### Changed
- Improved documentation.

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

[Unreleased]: https://github.com/code24-nl/openehr-mcp-server/compare/v0.3.0...HEAD

[0.3.0]: https://github.com/code24-nl/openehr-mcp-server/compare/v0.2.0...v0.3.0

[0.2.0]: https://github.com/code24-nl/openehr-mcp-server/compare/v0.1.0...v0.2.0
[0.1.0]: https://github.com/code24-nl/openehr-mcp-server/releases/tag/v0.1.0