## Quick context

This repository is a REMP monorepo containing multiple services (Beam, Campaign, Mailer, Sso, Web, Crm) and Docker infrastructure.
Agents should assume a Docker-first development flow: Go binaries for Beam are pre-built into images, PHP services are mounted from host, and services communicate via MySQL, Redis, Kafka, Elasticsearch and HTTP reverse proxy (Nginx).

## Where to run commands (high-level)
- Development: prefer `docker compose up` from repo root after running `make docker-build` (pre-builds Go artifacts used by images). See `Makefile` and `docker-compose.yml`.
- Tests: most PHP tests live per-service. Use `vendor/bin/phpunit` inside the service folder (or `make phpunit` at repo root which runs sub-services in correct order). Example: `cd Web/www/plugins/content-paste-analyzer && vendor/bin/phpunit`.
- Static analysis: `vendor/bin/phpstan` per project (some projects expose a `make phpstan` target).

## Repo layout & important files
- `Makefile` — top-level convenience targets including `docker-build`, `phpunit`, `composer-install`.
- `docker-compose.yml` — orchestrates nginx, mysql, redis, kafka, elasticsearch, beam go services and per-service PHP containers.
- `Web/` — WordPress site sources and plugins (e.g. `Web/www/plugins/content-paste-analyzer`).
- `Beam/go/cmd/*` — Go commands that must be built into tarballs (see `make docker-build`).

## Service boundaries & integration patterns
- Services are independent PHP apps (Laravel/other) mounted into containers. They communicate through shared infra:
  - Events: Kafka (zookeeper + kafka containers)
  - Storage: MySQL
  - Cache/search: Redis + Elasticsearch
  - HTTP: Nginx reverse proxy routes hosts (see `docker-compose.yml` service aliases)
- Beam has compiled Go binaries that are built via `make docker-build` then used by `docker compose` images.

## Project-specific conventions (do not invent)
- Docker env: repo uses `.env` for UID/GID mapping. If missing, containers default to `1000:1000`. When running locally, copy `.env.example` and set `UID`, `GID` to avoid permissions issues.
- XDebug is enabled by default in PHP images — set `docker-compose.override.yml` with correct `XDEBUG_CONFIG` to avoid timeouts.
- Composer/Yarn caches are often mounted via `docker-compose.override.yml` to speed installs (project recommends this after first run).

## WordPress plugin: content-paste-analyzer (concrete examples)
- Plugin path: `Web/www/plugins/content-paste-analyzer`.
- Key PHP classes: `ContentValidator`, `AdminNotice`, `PasteAdminPage`, `SuspectAdminPage`, `PasteDetector` (see plugin README).
- Important keys & patterns:
  - Post meta keys: `_cpa_dirty_html` (problematic post), `_cpa_pasted_html` (post where HTML was pasted).
  - `AdminNotice::render_notice()` includes templates from `src/Admin/templates/` (e.g. `admin-notice.php`).
  - Tests: `tests/` with HTML fixtures in `tests/articles/` prefixed `ok-` / `bad-`. Use `vendor/bin/phpunit` in plugin dir.

## Typical tasks for an agent and where to look
- Adding a feature to a plugin: update `src/` files under plugin folder, update `tests/` with new fixture and run plugin's `vendor/bin/phpunit`.
- Cross-service work: check README in each top-level service (Beam, Campaign, Mailer, Sso) for service-specific setup; use `docker compose exec <service> /bin/bash` to debug inside containers.
- Building Go components: run `make docker-build` from root (this prepares artifacts used by `docker compose build`).

## Quick commands (examples)
- Build go artifacts and start dev environment:
  - `make docker-build`
  - `docker compose up`
- Run plugin tests:
  - `cd Web/www/plugins/content-paste-analyzer && vendor/bin/phpunit`
- Run top-level phpunit for all services (runs migrations where required):
  - `make phpunit`

## Files to reference first when code-reading
- `Makefile`, `docker-compose.yml` (dev flow)
- `Web/www/plugins/content-paste-analyzer/README.md` and `src/` (plugin patterns)
- `Beam/README.md` and `Beam/go/cmd/*` (how go binaries are built)

## Limitations & assumptions
- This file documents discoverable patterns and commands from repository files. If environment-specific secrets or private services are needed (external CRM, SMTP), you'll need the user's `.env` or `docker-compose.override.yml` values.

If anything in these instructions is unclear or you want the agent to include extra examples (e.g. a walkthrough for adding a new plugin feature and test), tell me which area to expand. 
