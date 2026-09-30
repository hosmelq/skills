# Start the Local Database and Record Its Port

Use PostgreSQL with a loopback-only dynamic port, readiness checks and separate application/testing URLs. Run from the repository root with both env files present. `--force` bypasses the ready-service shortcut; it does not force container recreation.

```yaml
services:
  postgresql:
    environment:
      POSTGRES_DB: exampleapp
      POSTGRES_HOST_AUTH_METHOD: trust
      POSTGRES_USER: postgres
    healthcheck:
      interval: 10s
      retries: 5
      test: ['CMD-SHELL', 'pg_isready -h 127.0.0.1 -U postgres -d exampleapp_testing']
      timeout: 5s
    image: postgres:18@sha256:5a5a84b19854a9ffaa54082c166ff4ec27473a361e496e5ea167f298f2da9722
    ports:
      - '127.0.0.1:${COMPOSE_DB_PORT:-0}:5432'
    volumes:
      - exampleapp-postgresql:/var/lib/postgresql
      - ./infrastructure/docker/postgresql/init.sql:/docker-entrypoint-initdb.d/init.sql:ro

volumes:
  exampleapp-postgresql:
```

`infrastructure/docker/postgresql/init.sql`

```sql
CREATE DATABASE exampleapp_testing;
```

```bash
#!/usr/bin/env bash
set -euo pipefail

force=false

while [ "$#" -gt 0 ]; do
  case "$1" in
    --force)
      force=true
      shift
      ;;
    *)
      echo "Usage: $0 [--force]" >&2
      exit 1
      ;;
  esac
done

for env_file in .env .env.testing; do
  if [ ! -f "${env_file}" ]; then
    echo "Missing ${env_file}: run 'mise run setup:install' first" >&2
    exit 1
  fi
done

if [ "${force}" = false ]; then
  configured="$(docker compose config --services | sort)"
  ready="$(docker compose ps --format '{{.Service}} {{.State}} {{.Health}}' | awk '$2 == "running" && ($3 == "" || $3 == "healthy") { print $1 }' | sort)"

  if [ "${configured}" = "${ready}" ]; then
    exit 0
  fi
fi

docker compose up --detach --wait

database_port="$(docker compose port postgresql 5432 | sed 's/.*://')"

env_assignments=(
  "COMPOSE_DB_PORT=${database_port}"
  "DB_URL=postgres://postgres@127.0.0.1:${database_port}/exampleapp"
)

env_contents="$(IFS='|'; sed -E "/^(${env_assignments[*]%%=*})=/d" .env)"
printf '%s\n' "${env_contents}" "${env_assignments[@]}" >.env

testing_assignments=(
  "DB_URL=postgres://postgres@127.0.0.1:${database_port}/exampleapp_testing"
)

testing_contents="$(IFS='|'; sed -E "/^(${testing_assignments[*]%%=*})=/d" .env.testing)"
printf '%s\n' "${testing_contents}" "${testing_assignments[@]}" >.env.testing
```

`orca.yaml`

```yaml
scripts:
  archive: |
    mise run services:destroy
  setup: |
    export COMPOSE_DB_PORT=0

    mise run setup
setupAgentStartupPolicy: wait-for-setup
```

The ready-service shortcut leaves env files untouched; `--force` resynchronizes them. Initialization SQL runs on a fresh data volume; health does not prove migrations ran. Keep `APP_URL` in the project’s env configuration. Env writes are not atomic. Orca archive removes the owned database volume.
