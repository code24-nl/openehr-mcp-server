# openEHR MCP Server (PHP)

A PHP 8.4 [Model Context Protocol (MCP) Server](https://modelcontextprotocol.io/docs/getting-started/intro) that connects AI assistants to openEHR REST APIs.

- Works with MCP clients such as Claude Desktop and LibreChat.ai
- Exposes tools for Templates, EHR, Compositions, Archetypes, and AQL
- Optional guided Prompts help orchestrate multi-step workflows

Reference: openEHR REST spec — https://specifications.openehr.org/releases/ITS-REST/development/overview.html

> Note: For production-grade EHR integrations, ensure your AI model and deployment meet your organization’s data privacy and compliance requirements.

## Features

- PHP 8.4; PSR-compliant codebase
- Attribute-based MCP tool discovery (via https://github.com/php-mcp/server)
- Docker images for production and development
- Transports: streamable HTTP and stdio
- Structured logging with Monolog
- Simple, environment-driven configuration

## Available MCP Elements

### Tools

CKM (Clinical Knowledge Manager)
- `ckm_archetype_list` — List archetypes from the CKM server
- `ckm_archetype_get` — Get a CKM archetype by its CID identifier

openEHR Type specification
- `openehr_type_specification_list` — List bundled openEHR Type specifications using namePattern (using `*` wildcard) and an optional keyword (filters by type specification content). Returns the type, description, directory, and relative file path
- `openehr_type_specification_get` — Retrieve an openEHR Type specification (as BMM JSON) by relative file path or by openEHR type name. Note: these are BMM type definitions, not JSON Schema

Template Definition
- `openehr_template_list` — List templates (supports ADL 1.4/ADL2 filter)
- `openehr_template_get` — Get a template by ID (format: json|xml)
- `openehr_template_upload` — Upload a template (format: json|xml)
- `openehr_template_example_get` — Get an example composition for a template (format: json|flat|xml)

Stored Query Definition
- `openehr_stored_query_upload` — Upload a stored AQL query (optional version)
- `openehr_stored_query_list` — List stored queries (name filter supports `namespace::name`)
- `openehr_stored_query_get` — Get a stored query definition by name and version

Query Execution
- `openehr_query_adhoc` — Execute AQL (supports offset/fetch)
- `openehr_stored_query_execute` — Execute stored AQL by name/version with parameters

EHR Management
- `openehr_ehr_create` — Create an EHR (optional subject id/namespace)
- `openehr_ehr_get` — Get EHR by `ehr_id`
- `openehr_ehr_get_by_subject` — Get EHR by subject id/namespace
- `openehr_ehr_status_get` — Get `EHR_STATUS` for an EHR
- `openehr_ehr_contribution_create` — Create a Contribution for an EHR
- `openehr_ehr_contribution_get` — Get a Contribution for an EHR

Composition Management
- `openehr_composition_create` — Create a composition (format: json|flat|xml)
- `openehr_composition_get` — Get a composition by UID (format: json|flat|xml)
- `openehr_composition_update` — Update a composition (If-Match with preceding version)
- `openehr_composition_delete` — Delete a composition (versioned delete)
- `openehr_composition_revision_history` — Get composition revision history

### Prompts

Optional prompts that guide AI assistants through common openEHR and CKM workflows using the tools above.
- `vital_sign_capture` — Capture vital signs: fetch a flat JSON example for a chosen template, populate values (use UCUM units), then create a COMPOSITION.
- `patient_assessment` — General clinical assessment: pick a template, optionally fetch an example, then record observations as a COMPOSITION.
- `medication_review` — Review or update medication lists: select a template, optionally fetch an example, then submit medication changes as a COMPOSITION.
- `aql_query_runner` — Craft and execute AQL queries (ad‑hoc or stored) and manage stored queries.
- `template_management` — Manage templates (list/get/upload) and fetch example compositions.
- `ehr_management` — Create/find EHRs and inspect EHR status; optionally manage contributions.
- `composition_management` — Create, get, update, delete COMPOSITIONs; view revision history; fetch template examples when needed.
- `ckm_archetype_explorer` — Explore CKM archetypes by listing and fetching definitions (ADL/XML/Mindmap) by CID.
- `openehr_type_specification_explorer` — Discover and fetch openEHR Type specifications (as BMM JSON) using openehr_type_specification_list and openehr_type_specification_get.

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
cp .env.example .env
# Edit .env as needed (see variables below)
docker compose up -d mcp
```

The server listens on port `8242` as streamable HTTP transport.

Alternatively, build the MCP server image and run it (as stdio) in Clause Desktop (see configuration below):

```bash
docker compose build mcp
```

3) Optional: Start EHRbase stack for testing purpose

```bash
docker compose --profile ehrbase up -d
```

EHRbase on http://localhost:8080.

## Development

Prerequisites
- Docker and Docker Compose

1) Start dev container

```bash
docker compose --profile dev up -d mcp-dev
```

2) Configure environment

```bash
cp .env.example .env
# Edit .env as needed (see variables below)
```

3) Install dependencies (inside container)

```bash
docker compose exec mcp-dev composer install
```

4) Run the MCP server (inside container)

```bash
docker compose exec mcp-dev php server.php --transport=stdio
# or
docker compose exec mcp-dev php server.php --transport=streamable-http
```

## Environment Variables

- `APP_ENV`: application environment (`development`/`production`). Default: `development`
- `LOG_LEVEL`: Monolog level (`debug`, `info`, `warning`, `error`, etc.). Default: `info`
- `OPENEHR_API_BASE_URL`: base URL for your openEHR REST server (e.g., EHRbase: `http://localhost:8080/ehrbase/rest/openehr`). This is how you switch between EHRbase and other openEHR servers.
- `CKM_API_BASE_URL`: base URL for the openEHR CKM REST API. Default: `https://ckm.openehr.org/ckm/rest`
- `HTTP_TIMEOUT`: HTTP client timeout in seconds (float). Default: `2.0`
- `HTTP_SSL_VERIFY`: set to `false` to disable verification or provide a CA bundle path. Default: `true`

Note: Authorization headers are not configured by default. If your openEHR server requires auth, extend `server.php` to add `Authorization` headers to Guzzle.

## Integrations (Claude Desktop and LibreChat)

### Claude Desktop mcpServers example

Stdio example (use Docker)
```json
{
  "mcpServers": {
    "openehr": {
      "command": "docker",
      "args": [
        "run", "-i", "--rm", "--network=host",
        "-e", "OPENEHR_API_BASE_URL=http://localhost:8080/ehrbase/rest/openehr",
        "-e", "CKM_API_BASE_URL=https://ckm.openehr.org/ckm/rest",
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
- The server is accessible at http://localhost:8242/mcp_openehr
- Run the LibreChat server (see https://github.com/LibreChat/librechat-server)
- Configure LibreChat to use the MCP server (see https://github.com/LibreChat/librechat-server/blob/main/docs/mcp.md)
- The server is compatible with LibreChat’s MCP integration. Example minimal server entry in LibreChat config (YAML):
```yaml
mcpServers:
    openehr-mcp-server:
        type: streamable-http
        url: http://host.docker.internal:8242/mcp_openehr
```

## Testing and QA

- Unit tests: `docker compose exec mcp-dev composer test` (PHPUnit 12)
- Test with coverage: `docker compose exec mcp-dev composer test:coverage`
- Static analysis: `docker compose exec mcp-dev composer check:phpstan`

## Project Structure

- `server.php`: MCP server entry point
- `src/`
  - `Tools/`: MCP Tools (Definition, EHR, Composition, Query)
  - `Prompts/`: MCP Prompts
  - `Helpers/`: Internal helpers (e.g., content type and ADL mapping)
  - `Client/`: Internal API clients
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
