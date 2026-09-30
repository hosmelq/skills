# Configure Backend Toolchain and Setup Tasks

Use Mise to install PHP build prerequisites and separate dependency installation, agent setup, local services and migrations. Preserve ordered run arrays. Node and Nub here serve the agent-skill installer; match tool versions to the inspected environment.

```toml
min_version = "2026.9.15"

[bootstrap.packages]
# Libraries PHP is compiled against. jdx/vfox-php, the backend that builds it,
# only resolves Homebrew prefixes on macOS; on Linux it probes standard system
# paths, so that platform needs distribution packages rather than brew ones.

# Linux
"apt:autoconf" = { os = "linux", version = "latest" }
"apt:bison" = { os = "linux", version = "latest" }
"apt:build-essential" = { os = "linux", version = "latest" }
"apt:libbz2-dev" = { os = "linux", version = "latest" }
"apt:libcurl4-openssl-dev" = { os = "linux", version = "latest" }
"apt:libfreetype6-dev" = { os = "linux", version = "latest" }
"apt:libgd-dev" = { os = "linux", version = "latest" }
"apt:libgmp-dev" = { os = "linux", version = "latest" }
"apt:libicu-dev" = { os = "linux", version = "latest" }
"apt:libjpeg-dev" = { os = "linux", version = "latest" }
"apt:libonig-dev" = { os = "linux", version = "latest" }
"apt:libpng-dev" = { os = "linux", version = "latest" }
"apt:libpq-dev" = { os = "linux", version = "latest" }
"apt:libreadline-dev" = { os = "linux", version = "latest" }
"apt:libsodium-dev" = { os = "linux", version = "latest" }
"apt:libsqlite3-dev" = { os = "linux", version = "latest" }
"apt:libssl-dev" = { os = "linux", version = "latest" }
"apt:libwebp-dev" = { os = "linux", version = "latest" }
"apt:libxml2-dev" = { os = "linux", version = "latest" }
"apt:libzip-dev" = { os = "linux", version = "latest" }
"apt:pkg-config" = { os = "linux", version = "latest" }
"apt:re2c" = { os = "linux", version = "latest" }
"apt:zlib1g-dev" = { os = "linux", version = "latest" }

# macOS
"brew:autoconf" = { os = "macos", version = "latest" }
"brew:bison" = { os = "macos", version = "latest" }
"brew:bzip2" = { os = "macos", version = "latest" }
"brew:curl" = { os = "macos", version = "latest" }
"brew:freetype" = { os = "macos", version = "latest" }
"brew:gettext" = { os = "macos", version = "latest" }
"brew:gmp" = { os = "macos", version = "latest" }
"brew:icu4c@78" = { os = "macos", version = "latest" }
"brew:jpeg-turbo" = { os = "macos", version = "latest" }
"brew:libiconv" = { os = "macos", version = "latest" }
"brew:libpng" = { os = "macos", version = "latest" }
"brew:libpq" = { os = "macos", version = "latest" }
"brew:libsodium" = { os = "macos", version = "latest" }
"brew:libxml2" = { os = "macos", version = "latest" }
"brew:libzip" = { os = "macos", version = "latest" }
"brew:oniguruma" = { os = "macos", version = "latest" }
"brew:openssl@3" = { os = "macos", version = "latest" }
"brew:pkgconf" = { os = "macos", version = "latest" }
"brew:readline" = { os = "macos", version = "latest" }
"brew:re2c" = { os = "macos", version = "latest" }
"brew:sqlite" = { os = "macos", version = "latest" }
"brew:webp" = { os = "macos", version = "latest" }
"brew:zlib" = { os = "macos", version = "latest" }

[env]
_.file = { path = "{{ env.HOME }}/.config/exampleapp/env", redact = true }

[hooks]
enter = "mise bootstrap"

[settings]
lockfile = true

[tasks."db:fresh"]
depends = ["services:start"]
description = "Recreates and seeds the database."
run = "php artisan migrate:fresh --seed"

[tasks.dev]
depends = ["services:start"]
description = "Starts the development environment."
run = "php artisan dev"

[tasks."services:destroy"]
description = "Stops local Docker services and removes their data."
run = "docker compose down --remove-orphans --volumes"

[tasks."services:start"]
description = "Starts local Docker services."
run = "scripts/services-start"

[tasks."services:stop"]
description = "Stops local Docker services."
run = "docker compose down --remove-orphans"

[tasks.setup]
depends = ["setup:install"]
description = "Sets up the project for local development."
run = ["mise run setup:agent", "mise run services:start", "php artisan migrate --force"]

[tasks."setup:agent"]
description = "Installs agent guidelines and skills."
run = [
  "php artisan boost:install --ansi --guidelines --skills --no-interaction",
  "nubx -y skills experimental_install",
]

[tasks."setup:install"]
description = "Installs dependencies and prepares environment files."
run = [
  "composer install",
  "php -r \"file_exists('.env') || copy('.env.example', '.env');\"",
  "php -r \"file_exists('.env.testing') || copy('.env.testing.example', '.env.testing');\"",
  "grep -q '^APP_KEY=.' .env || php artisan key:generate",
  "grep -q '^APP_KEY=.' .env.testing || php artisan key:generate --env=testing",
]

[tasks.signoff]
description = "Signs off the current pull request."
run = "scripts/signoff"

[tools]
node = "26.10.0"
nub = "0.9.5"
php = { install_env = { PHP_EXTRA_CONFIGURE_OPTIONS = "--with-zip" }, version = "8.5" }
```

Add required application fixture preparation after env/key setup. `db:fresh` recreates data; `services:destroy` removes volumes. The check task used by signoff is shown in [signoff](10-signoff.md).

[Mise tasks](https://mise.jdx.dev/tasks/) · [Bootstrap](https://mise.jdx.dev/bootstrap.html)
