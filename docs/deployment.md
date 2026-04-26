# Deployment Guide

How to push changes from local to staging and production safely.

## Pre-deployment checklist

Before any deployment:

- [ ] Change tested locally and works
- [ ] Code committed to Git
- [ ] No uncommitted changes (`git status` clean)
- [ ] On the correct branch
- [ ] No debug code left in (`var_dump`, `console.log`, `error_log` calls for debugging)

## Standard deployment commands

### Deploy child theme to staging

```bash
rsync -avz --delete \
  --exclude='.git' \
  --exclude='.gitignore' \
  --exclude='node_modules' \
  --exclude='.DS_Store' \
  ~/claude-projects/enjoy-hr/child-theme/ \
  enjoycroatia:domains/staging1.enjoy.hr/public_html/wp-content/themes/jnews-child/
```

### Deploy child theme to production

```bash
rsync -avz --delete \
  --exclude='.git' \
  --exclude='.gitignore' \
  --exclude='node_modules' \
  --exclude='.DS_Store' \
  ~/claude-projects/enjoy-hr/child-theme/ \
  enjoycroatia:domains/enjoy.hr/public_html/wp-content/themes/jnews-child/
```

### Dry-run first (always recommended)

Add `-n` (or `--dry-run`) to see what *would* happen without actually doing it:

```bash
rsync -avzn --delete ...
```

Review the output. If anything looks wrong (deletions you didn't expect, files going to wrong places), stop.

## Post-deployment verification

After every production deployment:

1. **Open the site** — does it load?
2. **Open homepage, an article, a category page** — anything broken?
3. **Check browser DevTools console** — JS errors?
4. **Check error log:**
   ```bash
   ssh enjoycroatia "tail -50 domains/enjoy.hr/public_html/wp-content/debug.log 2>/dev/null || echo 'no debug log'"
   ```

## Rolling back

If a production deployment breaks something:

### Option 1 — Roll back via Git (preferred)

```bash
cd ~/claude-projects/enjoy-hr
git log --oneline -10                          # find last good commit
git checkout <good-commit-hash> -- child-theme/   # restore those files
# Now deploy this version to production
rsync -avz --delete ... (as above)
git checkout <branch>                          # return to working branch
```

### Option 2 — Roll back via Hostinger backup

hPanel has automated daily backups. Worst case, restore the whole site from backup. This is heavy-handed and may lose recent content edits.

## What to NOT deploy via rsync

- **`wp-config.php`** — environment-specific, never sync between environments
- **`uploads/` folder** — managed by WordPress, don't sync
- **The database** — never synced via file-level tools, use WP-CLI export/import
- **Plugins from `/wp-content/plugins/`** — install via wp-admin or WP-CLI on each environment
- **Parent themes** — installed/updated via wp-admin, not synced

## Database changes

Database changes (search-replace, schema changes, content imports) are NOT done via this deploy flow. They're done directly on each environment with explicit Marko approval each time:

```bash
ssh enjoycroatia
cd domains/staging1.enjoy.hr/public_html
wp search-replace 'old-string' 'new-string' --dry-run   # ALWAYS dry-run first
```

If dry-run looks good, run for real (without `--dry-run`).

## Plugin updates

Always staging first:

```bash
ssh enjoycroatia
cd domains/staging1.enjoy.hr/public_html
wp plugin update <plugin-slug> --dry-run
wp plugin update <plugin-slug>                # if dry-run good
```

Test thoroughly. Then on production:

```bash
cd domains/enjoy.hr/public_html
wp plugin update <plugin-slug>
```

## Theme (parent JNews) updates

JNews is a paid theme — updates come from JNews itself, usually via the Envato Market plugin or manual download.

Never update JNews on production without testing on staging first. Updates can break customizations and override files in unexpected ways.

## Emergency contacts

- **Hostinger support:** hPanel chat (24/7)
- **JNews support:** Envato support tickets via Themeforest account
- **DNS:** Managed in Hostinger hPanel
