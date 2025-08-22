# Contributing to openehr-mcp-server

Thank you for your interest in contributing! This document explains how to set up your environment, propose changes, and follow our conventions so that we can review and merge your work efficiently.

If you are new to the project, also check guidelines.md for broader engineering practices used in this repository.


## Table of contents
- Code of Conduct
- Getting help and asking questions
- Project setup (local)
- Running tests
- Code style and static analysis
- Commit messages and pull requests
- Branching, issues, and release notes
- Security


## Code of Conduct
Please be respectful and constructive. By participating, you agree to uphold a professional and inclusive environment. If you encounter unacceptable behavior, contact the maintainers privately via the repository’s security/contact channels.


## Getting help and asking questions
- For usage questions, open a GitHub Discussion (if enabled) or a Question issue with a minimal reproducible example.
- For bugs, open an Issue and include: expected behavior, actual behavior, steps to reproduce, environment details, and logs if relevant.
- For feature requests, explain the use‑case and proposed API/UX.


## Project setup (local)
Prerequisites:
- PHP 8.2+
- Composer
- Docker (optional) for running dependencies

Clone and install:
1. git clone <your-fork-url>
2. cd openehr-mcp-server
3. composer install

Using Docker (optional):
- docker-compose up -d

Run the MCP server locally:
- php server.php


## Running tests
- Unit/integration tests: vendor\bin\phpunit
- Run a subset: vendor\bin\phpunit --filter SomeTest
- Generate a coverage report (if Xdebug or PCOV installed):
  - vendor\bin\phpunit --coverage-text


## Code style and static analysis
- Lint/format: we follow PSR-12. You can use PHP CS Fixer or a compatible formatter in your IDE.
- Static analysis: if phpstan configuration is present, run: vendor\bin\phpstan analyse
- Keep functions and classes small and focused. Add phpdoc where types are not obvious.
- Include tests for new behavior and for regressions.


## Commit messages and pull requests
- Use conventional commits when possible (feat:, fix:, docs:, refactor:, test:, chore:).
- Write descriptive titles and include context in the body: what, why, how, and risks.
- One logical change per PR. Large changes can be split into smaller PRs.
- Run the full test suite and static analysis locally before pushing.
- Link related issues using GitHub keywords (Fixes #123).

PR checklist:
- Tests added/updated
- Docs updated (README/docs/) if needed
- No debug code or leftover comments
- All checks (CI) pass


## Branching, issues, and release notes
- Default branch: main
- Create feature branches from main: feature/short-description or fix/short-description
- We follow SemVer for releases and maintain a CHANGELOG.md (Keep a Changelog format recommended).


## Security
Do not open public issues for security vulnerabilities. Instead, please report privately using GitHub’s security advisories or the contact method listed in SECURITY.md if present. If not available, email the maintainers.

Thank you for contributing!