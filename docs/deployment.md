# Deployment Guide

How to push changes from local to staging and production safely.

## Environments

### Production
- **URL:** https://enjoy.hr
- **Path on server:** `domains/enjoy.hr/public_html/`
- **DB:** `u320042257_ctAnI` / user `u320042257_YYtz5`
- **Table prefix:** `ec_`
- **DNS:** Cloudflare (not Hostinger)

### Staging
- **URL:** https://stagin1.enjoy.hr *(note: subdomain has a typo — "stagin1" not "staging1" — leave as-is)*
- **Path on server:** `domains/enjoy.hr/public_html/stagin1/` *(subdirectory of production's public_html, not a separate domain folder)*
- **DB:** `u320042257_Lmk1i` / user `u320042257_029ST` — **own database, separate from production**
- **Table prefix:** `ec_`
- **DNS:** Cloudflare
- **Note:** Staging shares the production hosting plan. File and DB isolation are at the WordPress/application level, not OS level. One user account owns both environments.

### Local (development)
- **URL:** http://enjoyhr.local
- **Path:** `~/Local Sites/enjoyhr/app/public/`
- **Child theme source:** `~/claude-projects/enjoy-hr/jnews-child/` (symlinked into Local)

---

## Pre-deployment checklist

Before any deployment:

- [ ] Change tested locally and works
- [ ] Code committed to Git
- [ ] No uncommitted changes (`git status` clean)
- [ ] On the correct branch
- [ ] No debug code left in (`var_dump`, `console.log`, unintended `error_log` calls)

---

## Standard deployment commands

### SSH connection

All SSH/rsync uses:
- Key: `~/.ssh/hostinger_enjoycroatia`
- Port: `65002`
- User: `u320042257`
- Host: `92.112.187.42`
- Alias: `enjoyhr` (configured in `~/.ssh/config`)

### Step 1 — Dry-run rsync to staging (always do this first)

```bash
rsync -avzn --delete \
  --exclude='.git' \
  --exclude='.gitignore' \
  --exclude='.DS_Store' \
  -e 'ssh -i ~/.ssh/hostinger_enjoycroatia -p 65002' \
  ~/claude-projects/enjoy-hr/jnews-child/ \
  u320042257@92.112.187.42:domains/enjoy.hr/public_html/stagin1/wp-content/themes/jnews-child/
```

Review output. If file list and deletions look correct, proceed to live rsync.

### Step 2 — Live rsync to staging

Same command without `-n`:

```bash
rsync -avz --delete \
  --exclude='.git' \
  --exclude='.gitignore' \
  --exclude='.DS_Store' \
  -e 'ssh -i ~/.ssh/hostinger_enjoycroatia -p 65002' \
  ~/claude-projects/enjoy-hr/jnews-child/ \
  u320042257@92.112.187.42:domains/enjoy.hr/public_html/stagin1/wp-content/themes/jnews-child/
```

### Step 3 — Activate jnews-child on staging

```bash
ssh -i ~/.ssh/hostinger_enjoycroatia -p 65002 u320042257@92.112.187.42 \
  "wp --path=domains/enjoy.hr/public_html/stagin1 theme activate jnews-child"
```

### Step 4 — Copy theme_mods from jnews → jnews-child on staging

Write SQL to a temp file locally, scp to server, execute via `mysql` directly (not `wp db query` — see DB backup note below), delete both copies:

```bash
cat > /tmp/copy_theme_mods.sql << 'EOF'
INSERT INTO ec_options (option_name, option_value, autoload)
SELECT 'theme_mods_jnews-child', option_value, autoload
FROM ec_options
WHERE option_name = 'theme_mods_jnews'
ON DUPLICATE KEY UPDATE option_value = VALUES(option_value);
EOF

scp -P 65002 -i ~/.ssh/hostinger_enjoycroatia \
  /tmp/copy_theme_mods.sql \
  u320042257@92.112.187.42:/tmp/copy_theme_mods.sql

ssh -i ~/.ssh/hostinger_enjoycroatia -p 65002 u320042257@92.112.187.42 \
  "mysql -h 127.0.0.1 -u u320042257_029ST -p'<staging_db_password>' u320042257_Lmk1i \
   < /tmp/copy_theme_mods.sql && rm /tmp/copy_theme_mods.sql"

rm /tmp/copy_theme_mods.sql
```

### Deploy child theme to production

Only after staging verified and Marko approves:

```bash
# Dry-run first
rsync -avzn --delete \
  --exclude='.git' \
  --exclude='.gitignore' \
  --exclude='.DS_Store' \
  -e 'ssh -i ~/.ssh/hostinger_enjoycroatia -p 65002' \
  ~/claude-projects/enjoy-hr/jnews-child/ \
  u320042257@92.112.187.42:domains/enjoy.hr/public_html/wp-content/themes/jnews-child/

# Live rsync (after dry-run approved)
rsync -avz --delete \
  --exclude='.git' \
  --exclude='.gitignore' \
  --exclude='.DS_Store' \
  -e 'ssh -i ~/.ssh/hostinger_enjoycroatia -p 65002' \
  ~/claude-projects/enjoy-hr/jnews-child/ \
  u320042257@92.112.187.42:domains/enjoy.hr/public_html/wp-content/themes/jnews-child/
```

---

## Post-deployment verification

After every deployment to staging or production:

1. **Check the site loads** — `curl -sI https://stagin1.enjoy.hr/`
2. **Check OG tags** — `curl -s https://stagin1.enjoy.hr/ | grep -E 'property="og:|name="twitter:'`
3. **Open homepage, an article, a category page** — anything broken?
4. **Check error log:**
   ```bash
   ssh -i ~/.ssh/hostinger_enjoycroatia -p 65002 u320042257@92.112.187.42 \
     "tail -50 domains/enjoy.hr/public_html/stagin1/wp-content/debug.log 2>/dev/null || echo 'no debug log'"
   ```

---

## Rolling back

If a deployment breaks something:

### Option 1 — Roll back via Git (preferred)

```bash
cd ~/claude-projects/enjoy-hr
git log --oneline -10                              # find last good commit
git checkout <good-commit-hash> -- jnews-child/   # restore those files
rsync -avz --delete ...                           # deploy the restored version
git checkout main                                  # return to main branch
```

### Option 2 — Roll back via Hostinger backup

hPanel has automated daily backups. Worst case, restore the whole site from backup.

---

## What NOT to deploy via rsync

- **`wp-config.php`** — environment-specific, never sync between environments
- **`uploads/`** — managed by WordPress, don't sync
- **The database** — never synced via file tools; use WP-CLI export/import
- **Plugins** — install via wp-admin or WP-CLI on each environment separately
- **Parent themes** — updated via wp-admin, not synced

---

## Database changes

Done directly on each environment with explicit Marko approval:

```bash
ssh -i ~/.ssh/hostinger_enjoycroatia -p 65002 u320042257@92.112.187.42
cd domains/enjoy.hr/public_html/stagin1
wp search-replace 'old-string' 'new-string' --dry-run   # ALWAYS dry-run first
```

---

## Plugin updates

Staging first, then production:

```bash
# On staging
ssh enjoyhr "wp --path=domains/enjoy.hr/public_html/stagin1 plugin update <slug>"

# On production (after staging verified)
ssh enjoyhr "wp --path=domains/enjoy.hr/public_html plugin update <slug>"
```

---

## Database backup — known issue

**`wp db export` is silently broken on this Hostinger account.** It exits 0 but writes no file and produces zero bytes to stdout. `wp db check` and `wp db query` work; the export wrapper does not (likely a Hostinger restriction on the mysqldump subprocess).

Always use `mysqldump` directly for DB backups:

```bash
# Production backup
ssh enjoyhr "mysqldump -h 127.0.0.1 -u u320042257_YYtz5 -p'<password>' u320042257_ctAnI > ~/backup-$(date +%Y%m%d).sql"
scp -P 65002 -i ~/.ssh/hostinger_enjoycroatia \
  u320042257@92.112.187.42:~/backup-$(date +%Y%m%d).sql \
  ~/claude-projects/enjoy-hr/backups/
ssh enjoyhr "rm ~/backup-$(date +%Y%m%d).sql"
```

Retrieve the password on demand via `ssh enjoyhr "wp --path=domains/enjoy.hr/public_html config get DB_PASSWORD"`.

---

## Emergency contacts

- **Hostinger support:** hPanel chat (24/7)
- **JNews support:** Envato support tickets via Themeforest account
- **DNS:** Cloudflare dashboard
