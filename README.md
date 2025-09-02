# openEHR MCP Server (PHP)

A PHP 8.4 MCP (Model Context Protocol) server that interfaces with the openEHR REST API (ITS-REST). 
See the specification: https://specifications.openehr.org/releases/ITS-REST/development/overview.html
It exposes MCP Tools for template, EHR, composition, and query operations, and optional MCP Prompts to guide AI clients.
Works with MCP clients like `Claude Desktop` and is compatible with `LibreChat.ai` MCP integration.

Purpose
- This MCP server helps clinicians and openEHR developers interact with an openEHR server safely and efficiently via AI assistants. It streamlines common operations such as listing/uploading templates, creating and querying EHRs and COMPOSITIONs, and running AQL — while enforcing correct API usage and payload formats.

Compatibility
- Tested with EHRbase (openEHR reference platform implementation) out of the box.
- Not limited to EHRbase: you can point the server at any openEHR REST implementation by configuring `OPENEHR_API_BASE_URL`.

Note: For production-grade EHR integrations, ensure your AI model and deployment meet your organization’s data privacy and compliance requirements.

## Features

- PHP 8.4, PSR-compliant
- attribute-based MCP discovery using https://github.com/php-mcp/server library
- Docker images for production and development
- Streamable HTTP and stdio transports
- Structured logging with Monolog
- Simple environment configuration

## Available MCP Tools

CKM (Clinical Knowledge Manager)
- `ckm_archetype_list`: List archetypes from the CKM server
- `ckm_archetype_get`: Get a CKM archetype by its CID identifier

Template Definition
- `openehr_template_list`: List templates (supports ADL 1.4/ADL2 filter)
- `openehr_template_get`: Get a template by ID (format json/xml)
- `openehr_template_upload`: Upload a template (format json/xml)
- `openehr_template_example_get`: Get example composition for a template (format json/flat/xml)

Stored Query Definition
- `openehr_stored_query_upload`: Upload a stored AQL query (optional version)
- `openehr_stored_query_list`: List stored queries (name filter supports `namespace::name` pattern)
- `openehr_stored_query_get`: Get a stored query definition by name and version

Query Execution
- `openehr_query_adhoc`: Execute AQL (supports offset/fetch)
- `openehr_query_stored_execute`: Execute stored AQL by name/version with parameters

EHR Management
- `openehr_ehr_create`: Create an EHR (optional subject id/namespace)
- `openehr_ehr_get`: Get EHR by `ehr_id`
- `openehr_ehr_get_by_subject`: Get EHR by subject id/namespace
- `openehr_ehr_status_get`: Get `EHR_STATUS` for an EHR
- `openehr_ehr_contribution_create`: Create Contribution for an EHR
- `openehr_ehr_contribution_get`: Get Contribution for an EHR

Composition Management
- `openehr_composition_create`: Create composition (format json/flat/xml)
- `openehr_composition_get`: Get composition by UID (format json/flat/xml)
- `openehr_composition_update`: Update composition (If-Match with preceding version)
- `openehr_composition_delete`: Delete composition (versioned delete)
- `openehr_composition_revision_history`: Get composition revision history

## MCP Prompts

These optional prompts provide structured guidance that helps AI assistants assist clinicians and developers in common workflows against an openEHR server.
- `vital_sign_capture`: Capture vital signs for a given EHR
- `patient_assessment`: Guidance for general patient assessments
- `medication_review`: Guidance for medication review/update
- `aql_query_runner`: Guidance for crafting and executing AQL queries
- `template_management`, `ehr_management`, `composition_management`: Operational guidance aligned with the tools

These optional prompts provide structured guidance around Archetypes in common workflows against an CKM server.
- `ckm_archetype_explorer`: Explore CKM archetypes; use tools to list and fetch ADL.

## Transports

- `stdio`: Suitable for process-based MCP clients
- `streamable-http` (default): HTTP server on port `8242` with MCP at `/mcp_openehr`

Start option: pass `--transport=stdio` or `--transport=streamable-http` to `server.php`; if `--transport` is skipped, the default is `streamable-http`.

## Quick Start with Docker

Prerequisites
- Docker and Docker Compose
- Git

1) Clone

```bash
git clone https://github.com/code24-nl/openehr-mcp-server.git
cd openehr-mcp-server
```

2) Run the MCP server (production image)

```bash
docker compose up -d mcp
```

- The server listens on port `8242` as streamable HTTP transport.

3) Optional: Start EHRbase stack

```bash
docker compose --profile ehrbase up -d
```

- EHRbase on http://localhost:8080 (with a backing Postgres container).

## Local Development 

Prerequisites
- PHP 8.4+
- Composer

1) Install dependencies

```bash
docker compose run --rm -i mcp-dev sh
composer install
```

2) Configure environment

```bash
cp .env.example .env
# Edit .env as needed (see variables below)
```

3) Run the MCP server

```bash
php server.php --transport=stdio
# or
php server.php --transport=streamable-http
```

## Environment Variables

- `APP_ENV`: application environment (`development`/`production`). Default: `development`
- `LOG_LEVEL`: Monolog level (`debug`, `info`, `warning`, `error`, etc.). Default: `info`
- `OPENEHR_API_BASE_URL`: base URL for your openEHR REST server (e.g., EHRbase: `http://localhost:8080/ehrbase/rest/openehr`). This is how you switch between EHRbase and other openEHR servers.
- `CKM_API_BASE_URL`: base URL for the openEHR CKM REST API. Default: `https://ckm.openehr.org/ckm/rest`
- `HTTP_TIMEOUT`: HTTP client timeout in seconds (float). Default: `2.0`
- `HTTP_SSL_VERIFY`: set to `false` to disable verification or provide a CA bundle path. Default: `true`

Note: Authorization headers are not configured by default. If your openEHR server requires auth, extend `server.php` to add `Authorization` headers to Guzzle.

## Integrate with Claude Desktop and LibreChat

### Claude Desktop mcpServers examples

Stdio (recommended for local dev without docker)
```json
{
  "mcpServers": {
    "openehr": {
      "command": "php",
      "args": ["server.php", "--transport=stdio"],
      "env": {
        "OPENEHR_API_BASE_URL": "http://localhost:8080/ehrbase/rest/openehr",
        "LOG_LEVEL": "info"
      }
    }
  }
}
```

Docker (stdio) example
```json
{
  "mcpServers": {
    "openehr": {
      "command": "docker",
      "args": [
        "run", "-i", "--rm", 
        "--network=host", "-e", "OPENEHR_API_BASE_URL=http://localhost:8080/ehrbase/rest/openehr",
        "code24-nl/openehr-mcp-server:latest",
        "php", "server.php", "--transport=stdio"
      ]
    }
  }
}
```

### Streamable HTTP in LibreChat

LibreChat.ai MCP example
- Run first the MCP server (see above, e.g. `docker compose up -d mcp`)
- Run the LibreChat server (see https://github.com/LibreChat/librechat-server)
- Configure LibreChat to use the MCP server (see https://github.com/LibreChat/librechat-server/blob/main/docs/mcp.md)
- The server is accessible at http://localhost:8242/mcp_openehr
- The server is compatible with LibreChat’s MCP integration. Example minimal server entry in LibreChat config (YAML):
```yaml
mcpServers:
    openehr-mcp-server:
        type: streamable-http
        url: http://host.docker.internal:8242/mcp_openehr
```

## Testing and QA

- Unit tests: composer test (PHPUnit 12)
- Test with coverage: composer test:coverage
- Static analysis: composer check:phpstan

## Project Structure

- `server.php`: MCP server entry point
- `src/`
  - `Tools/`: MCP Tools (Definition, EHR, Composition, Query)
  - `Prompts/`: MCP Prompts
  - `Helpers/`: Internal helpers (e.g., content type and ADL mapping)
  - `constants.php`: loads env and defaults
- `docker-compose.yml`: services (`mcp`, `mcp-dev`, optional `ehrbase` stack)
- `Dockerfile`: multi-stage build (development, production)
- `tests/`: PHPUnit and PHPStan config and tests

## Acknowledgments

This project is inspired by and grateful to:
- The original Python openEHR MCP Server: https://github.com/deak-ai/openehr-mcp-server
- Seref Arikan, Sidharth Ramesh — for inspiration on AI MCP integration
- The PHP MCP Server framework: https://github.com/php-mcp/server

## Contributing

We welcome contributions! Please read CONTRIBUTING.md for guidelines on setting up your environment, coding style, testing, and how to propose changes.

See CHANGELOG.md for notable changes and update it with every release.

## License

MIT License — see `LICENSE`.
