# Refreshing Local from Production

Run this when your local copy has gone stale (roughly every few weeks, or before a major change).
Local is for code development — you don't need to keep content in sync constantly.

## Prerequisites

- Local by Flywheel running with `enjoyhr` site started
- SSH access via `ssh enjoyhr` working
- WP-CLI installed via Homebrew (`brew install wp-cli`)

## One-time setup (already done — skip on repeat runs)

Local's MySQL does not accept TCP connections from `127.0.0.1` by default. This was fixed once:

```bash
MYSQL_SOCK="$HOME/Library/Application Support/Local/run/oYGNnL-sq/mysql/mysqld.sock"
MYSQL_BIN="$HOME/Library/Application Support/Local/lightning-services/mysql-8.0.35+4/bin/darwin-arm64/bin"
"$MYSQL_BIN/mysql" --socket="$MYSQL_SOCK" -u root -proot -e \
  "CREATE USER IF NOT EXISTS 'root'@'127.0.0.1' IDENTIFIED BY 'root'; GRANT ALL ON *.* TO 'root'@'127.0.0.1' WITH GRANT OPTION; FLUSH PRIVILEGES;"
```

Also already done in `wp-config.php` on local:
- `DB_HOST` set to `127.0.0.1:10003` (Local's MySQL TCP port)
- `$table_prefix` set to `ec_` (production uses this prefix, not `wp_`)

## Step 1 — Export the production database

WP-CLI `db export` does not work on Hostinger shared hosting (mysqldump permission issue).
Use phpMyAdmin instead:

1. Log in to hPanel → Databases → phpMyAdmin
2. Click **Enter phpMyAdmin** next to `u320042257_ctAnI`
3. Click the **Export** tab → Format: SQL → click **Export**
4. Save the downloaded file (e.g. `u320042257_ctAnI.sql`) to `~/Downloads/`

## Step 2 — Rsync production files to local

```bash
rsync -avz --progress \
  --exclude='staging1/' \
  --exclude='wp-config.php' \
  --exclude='wp-content/cache/' \
  --exclude='wp-content/upgrade/' \
  --exclude='wp-content/upgrade-temp-backup/' \
  --exclude='wp-content/maintenance' \
  --exclude='wp-content/advanced-cache.php' \
  --exclude='wp-content/ai1wm-backups/' \
  --exclude='wp-content/webp-express/' \
  --exclude='.DS_Store' \
  -e 'ssh -i ~/.ssh/hostinger_enjoycroatia -p 65002' \
  u320042257@92.112.187.42:domains/enjoy.hr/public_html/ \
  "$HOME/Local Sites/enjoyhr/app/public/"
```

This transfers ~3.6GB — allow several minutes.

## Step 3 — Import the database

Drop and recreate the local DB (avoids table conflicts on re-import):

```bash
MYSQL_SOCK="$HOME/Library/Application Support/Local/run/oYGNnL-sq/mysql/mysqld.sock"
MYSQL_BIN="$HOME/Library/Application Support/Local/lightning-services/mysql-8.0.35+4/bin/darwin-arm64/bin"

"$MYSQL_BIN/mysql" --socket="$MYSQL_SOCK" -u root -proot \
  -e "DROP DATABASE local; CREATE DATABASE local CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

"$MYSQL_BIN/mysql" --socket="$MYSQL_SOCK" -u root -proot local \
  < ~/Downloads/u320042257_ctAnI.sql
```

## Step 4 — URL search-replace

Set up the PATH helper, then run both replacements:

```bash
MYSQL_BIN="$HOME/Library/Application Support/Local/lightning-services/mysql-8.0.35+4/bin/darwin-arm64/bin"
WP="PATH=\"$MYSQL_BIN:$PATH\" wp --path=\"$HOME/Local Sites/enjoyhr/app/public\""

# Dry-run first — review counts before committing
eval "$WP search-replace 'https://enjoy.hr' 'http://enjoyhr.local' --dry-run"
eval "$WP search-replace 'enjoy.hr' 'enjoyhr.local' --dry-run"

# Real run
eval "$WP search-replace 'https://enjoy.hr' 'http://enjoyhr.local'"
eval "$WP search-replace 'enjoy.hr' 'enjoyhr.local'"
```

## Step 5 — Flush cache

```bash
MYSQL_BIN="$HOME/Library/Application Support/Local/lightning-services/mysql-8.0.35+4/bin/darwin-arm64/bin"
PATH="$MYSQL_BIN:$PATH" wp cache flush --path="$HOME/Local Sites/enjoyhr/app/public"
rm -rf "$HOME/Local Sites/enjoyhr/app/public/wp-content/cache/"
```

## Step 6 — Verify

Open `http://enjoyhr.local` — should load with current production content.

## Notes

- Local's MySQL TCP port is `10003` and the socket is at:
  `~/Library/Application Support/Local/run/oYGNnL-sq/mysql/mysqld.sock`
- Local's MySQL binary: `~/Library/Application Support/Local/lightning-services/mysql-8.0.35+4/bin/darwin-arm64/bin/mysql`
- Production table prefix is `ec_` — `wp-config.php` on local must keep `$table_prefix = 'ec_';`
- `wp-config.php` is excluded from rsync so local settings are preserved
- The `enjoy-croatia/` folder exists in `wp-content/themes/` on production but is **abandoned** — it's a leftover from a previous site setup and is not the active theme. Decision pending: delete, archive, or ignore. Do not treat it as the child theme.
