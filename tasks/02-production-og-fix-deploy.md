# Task 02 — Complete Production Deployment: JNews OG Meta Fix

**Status:** Ready to execute (blocked 2026-04-26 by unrelated Elementor incident)  
**Branch:** `main` (commit with OG fix already merged)  
**Estimated time:** 20 minutes  
**Risk:** Low — child theme files already on server, tested on staging

---

## Context

The JNews OG meta fix was developed and tested locally, deployed to staging (verified working), and rsync'd to production — but theme activation was never completed. Production is currently running the JNews parent theme. The jnews-child files sit in `wp-content/themes/jnews-child/` on production, inactive and harmless.

**What the fix does:** Removes JNews's duplicate OG/Twitter/JSON-LD meta output (which overwrites Rank Math's correct output and poisons `og:description` with raw admin markup like "edit post").

**Staging verification status:** Fully verified. OG tags correct, layout intact, no PHP errors, incognito confirmed.

---

## Pre-flight checklist (do before touching production)

- [ ] Choose a low-traffic window (weekday morning or late night)
- [ ] Confirm Elementor + Elementor Pro are both active on production:
  ```bash
  ssh enjoyhr "wp --path=domains/enjoy.hr/public_html plugin list --status=active --format=csv" | grep elementor
  ```
- [ ] Confirm `jnews-child` files are still on production (should be — never deleted):
  ```bash
  ssh enjoyhr "ls -la domains/enjoy.hr/public_html/wp-content/themes/jnews-child/"
  ```
- [ ] Take a fresh DB snapshot before any DB change:
  ```bash
  ssh enjoyhr "wp --path=domains/enjoy.hr/public_html db export /tmp/prod-pre-deploy-$(date +%Y%m%d).sql && echo done"
  # Then scp it locally
  scp -P 65002 -i ~/.ssh/hostinger_enjoycroatia \
    u320042257@92.112.187.42:/tmp/prod-pre-deploy-$(date +%Y%m%d).sql \
    ~/claude-projects/enjoy-hr/backups/
  ```
- [ ] Confirm git is clean and on main with the OG fix committed:
  ```bash
  git log --oneline -5
  git status
  ```

---

## Deployment steps

### Step 1 — Dry-run rsync (verify file list)

```bash
rsync -avzn --delete \
  --exclude='.git' \
  --exclude='.gitignore' \
  --exclude='.DS_Store' \
  -e 'ssh -i ~/.ssh/hostinger_enjoycroatia -p 65002' \
  ~/claude-projects/enjoy-hr/jnews-child/ \
  u320042257@92.112.187.42:domains/enjoy.hr/public_html/wp-content/themes/jnews-child/
```

Review output. If no unexpected deletions, proceed.

### Step 2 — Live rsync

```bash
rsync -avz --delete \
  --exclude='.git' \
  --exclude='.gitignore' \
  --exclude='.DS_Store' \
  -e 'ssh -i ~/.ssh/hostinger_enjoycroatia -p 65002' \
  ~/claude-projects/enjoy-hr/jnews-child/ \
  u320042257@92.112.187.42:domains/enjoy.hr/public_html/wp-content/themes/jnews-child/
```

### Step 3 — Copy theme_mods from jnews → jnews-child in production DB

Table prefix on production is `ec_` (confirmed from wp-config.php). Write SQL to local temp file, scp, execute, delete both copies.

```bash
cat > /tmp/copy_theme_mods_prod.sql << 'EOF'
INSERT INTO ec_options (option_name, option_value, autoload)
SELECT 'theme_mods_jnews-child', option_value, autoload
FROM ec_options
WHERE option_name = 'theme_mods_jnews'
ON DUPLICATE KEY UPDATE option_value = VALUES(option_value);
EOF

scp -P 65002 -i ~/.ssh/hostinger_enjoycroatia \
  /tmp/copy_theme_mods_prod.sql \
  u320042257@92.112.187.42:/tmp/copy_theme_mods_prod.sql

ssh -i ~/.ssh/hostinger_enjoycroatia -p 65002 u320042257@92.112.187.42 \
  "wp --path=domains/enjoy.hr/public_html db query < /tmp/copy_theme_mods_prod.sql \
   && rm /tmp/copy_theme_mods_prod.sql"

rm /tmp/copy_theme_mods_prod.sql
```

**PAUSE — verify before proceeding:**
```bash
ssh enjoyhr "wp --path=domains/enjoy.hr/public_html db query \
  'SELECT option_name FROM ec_options WHERE option_name LIKE \"theme_mods_%\";'"
```
Expected: both `theme_mods_jnews` and `theme_mods_jnews-child` present.

### Step 4 — Activate jnews-child on production

**Get Marko's explicit approval before running this.**

```bash
ssh -i ~/.ssh/hostinger_enjoycroatia -p 65002 u320042257@92.112.187.42 \
  "wp --path=domains/enjoy.hr/public_html theme activate jnews-child"
```

Expected output: `Success: Switched to 'JNews Child' theme.`

### Step 5 — Purge Cloudflare cache

DNS is on Cloudflare. After theme activation, purge the cache so visitors get fresh HTML (not cached parent-theme pages):

- Log in to Cloudflare dashboard → enjoy.hr zone → Caching → Purge Everything
- Or via API if Cloudflare API token is available

### Step 6 — Verify

```bash
# HTTP status (origin, no cache)
curl -sI --resolve enjoy.hr:443:92.112.187.42 \
  -H "Cache-Control: no-cache" "https://enjoy.hr/?bust=$(date +%s)"

# Active theme confirms jnews-child
ssh enjoyhr "wp --path=domains/enjoy.hr/public_html option get stylesheet"

# OG tags — should show Rank Math output, not JNews garbage
curl -s --resolve enjoy.hr:443:92.112.187.42 \
  -H "Cache-Control: no-cache" "https://enjoy.hr/?bust=$(date +%s)" \
  | grep -E 'property="og:|name="twitter:'

# Error log
ssh enjoyhr "tail -50 domains/enjoy.hr/public_html/wp-content/debug.log 2>/dev/null || echo 'no debug log'"
```

**Manual checks (incognito browser):**
- [ ] https://enjoy.hr/ — homepage loads, layout intact, no "edit post" text visible
- [ ] Open any article — single post template renders correctly
- [ ] Check https://opengraph.xyz — paste https://enjoy.hr/ and verify og:title and og:description look correct (should show site name and real description, not "edit post")
- [ ] Check category archive page — Elementor Pro archive template renders
- [ ] Check 404 page — Elementor Pro 404 template renders

---

## Rollback plan

If anything looks wrong after activation:

```bash
# Reactivate JNews parent immediately
ssh enjoyhr "wp --path=domains/enjoy.hr/public_html theme activate jnews"

# Verify
ssh enjoyhr "wp --path=domains/enjoy.hr/public_html option get stylesheet"
curl -sI --resolve enjoy.hr:443:92.112.187.42 "https://enjoy.hr/"
```

The child theme files remain on the server — they're harmless while inactive. No DB change to roll back (theme_mods copy is additive, doesn't overwrite anything). Cloudflare will re-cache the parent theme output within minutes.

---

## Post-deployment

After successful deployment and Marko's sign-off:

```bash
# Tag the release
git tag -a v1.0.0 -m "First production deployment: JNews child theme + OG meta fix"
git push origin v1.0.0
```

Update `docs/audit-findings.md`:
- Mark OG fix as live on production in the current state table
- Close out the deployment retrospective

---

## Notes

- Do NOT run `wp search-replace` on production without separate explicit approval
- Do NOT touch wp-config.php
- Elementor + Elementor Pro must remain active throughout — do not deactivate during deploy
- The DB backup taken in pre-flight covers the rollback case for DB changes
