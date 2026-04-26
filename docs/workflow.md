# Workflow — Local → Staging → Production

This is the standard flow for any change to enjoy.hr. Never skip steps.

## The flow

```
[Local development]  →  [Git commit]  →  [Push to staging]  →  [Test on staging]  →  [Push to production]
       ↑                                                              |
       |                                                              |
       └──────────────────── If issues found, back to local ──────────┘
```

## Step 1 — Develop locally

Work happens in Local by Flywheel. The site is at `http://enjoyhr.local`.

The child theme — which is what we actually edit — lives in this repo at:
```
~/claude-projects/enjoy-hr/child-theme/
```

It's symlinked into Local's WordPress install at:
```
~/Local Sites/enjoyhr/app/public/wp-content/themes/jnews-child/
```

Edit the files in `~/claude-projects/enjoy-hr/child-theme/`. Reload `http://enjoyhr.local` in your browser. Iterate.

## Step 2 — Commit to Git

Once a change works locally:

```bash
cd ~/claude-projects/enjoy-hr
git add .
git status              # review what you're committing
git commit -m "Clear, imperative message describing the change"
git push origin feature/your-branch-name
```

Don't commit half-broken code. If a change is mid-way, use `git stash` or commit to a branch.

## Step 3 — Deploy to staging

Two ways:

**Option A — rsync (fastest, what Claude Code will use most often):**

```bash
rsync -avz --delete \
  --exclude='.git' \
  --exclude='.DS_Store' \
  ~/claude-projects/enjoy-hr/child-theme/ \
  enjoycroatia:domains/staging1.enjoy.hr/public_html/wp-content/themes/jnews-child/
```

**Option B — Git pull on server (if we set up a Git remote on the server later):**

```bash
ssh enjoycroatia "cd domains/staging1.enjoy.hr/public_html/wp-content/themes/jnews-child && git pull origin main"
```

We'll start with rsync. Git-on-server can come later if useful.

## Step 4 — Test on staging

Open `https://staging1.enjoy.hr` in browser. Test:

- [ ] Does the change look right?
- [ ] Does it work on mobile view (DevTools responsive mode)?
- [ ] Did anything else break? (homepage, article view, archives, search)
- [ ] Console errors in browser DevTools?
- [ ] Speed feels OK?

If anything is wrong → back to local, fix, recommit, redeploy to staging. Don't proceed.

## Step 5 — Deploy to production

**Only after staging passes all checks AND Marko approves.**

```bash
rsync -avz --delete \
  --exclude='.git' \
  --exclude='.DS_Store' \
  ~/claude-projects/enjoy-hr/child-theme/ \
  enjoycroatia:domains/enjoy.hr/public_html/wp-content/themes/jnews-child/
```

Immediately after deploying:
- [ ] Visit enjoy.hr — does it load?
- [ ] Spot-check a few pages
- [ ] Check `wp-admin` still loads
- [ ] Check error log: `ssh enjoycroatia "tail -50 domains/enjoy.hr/public_html/wp-content/debug.log"` (if WP_DEBUG_LOG enabled)

If something broke on production, **roll back immediately**:

```bash
cd ~/claude-projects/enjoy-hr
git log --oneline      # find the last known-good commit
git checkout <hash> -- child-theme/
# Now redeploy this version to production via rsync
```

## Things that bypass this workflow (with caution)

- **Plugin/theme updates** — done in wp-admin or via WP-CLI directly. Always on staging first.
- **Database changes** — never edited locally and synced (data drifts). Use WP-CLI on staging/production directly with explicit confirmation.
- **Media uploads** — happen via wp-admin on production. Don't try to sync `wp-content/uploads/` — it's huge and changes constantly.

## When local diverges from production

Production gets new content (posts, comments) constantly. Your local copy goes stale within hours.

**You don't need to keep local content fresh** — content edits happen on production. Local is for *code* development.

But every few weeks (or before a major change), refresh local from production:
1. Pull a fresh database export from production
2. Import to local
3. Run `wp search-replace 'enjoy.hr' 'enjoyhr.local'` on the local DB

Claude Code will help with this when needed.
