# CLAUDE.md

This file is read automatically by Claude Code at the start of each session. It contains conventions and context for this project.

## Project: Enjoy Croatia (enjoy.hr)

WordPress tourism content site running JNews theme on Hostinger. See `PROJECT_PROMPT.md` for full project context.

## Repository structure

```
~/claude-projects/enjoy-hr/
├── PROJECT_PROMPT.md          # Master project prompt (read first)
├── CLAUDE.md                  # This file — conventions
├── README.md                  # Setup instructions for Marko
├── SETUP_CHECKLIST.md         # Step-by-step initial setup
├── tasks/                     # Task briefs for specific work
│   ├── 01-site-audit.md       # ✅ Complete
│   └── 02-production-og-fix-deploy.md  # ✅ Complete
├── docs/                      # Project documentation
│   ├── workflow.md            # Local → staging → production
│   ├── deployment.md          # How to deploy changes (exact rsync/WP-CLI commands)
│   └── audit-findings.md      # Full audit — findings, lessons learned, current state
├── jnews-child/               # The JNews child theme (Git-tracked source)
│   ├── style.css              # Theme declaration header
│   ├── functions.php          # Parent stylesheet enqueue + JNews OG meta fix
│   ├── screenshot.png
│   └── ...
└── snippets/                  # Standalone PHP snippets (reference)
```

The `jnews-child/` folder is symlinked into Local's WordPress install:

```
~/Local Sites/enjoyhr/app/public/wp-content/themes/jnews-child  →  ~/claude-projects/enjoy-hr/jnews-child
```

You edit the source in `~/claude-projects/enjoy-hr/jnews-child/` and WordPress reads it through the symlink.

## Code conventions

### PHP
- WordPress Coding Standards (WPCS)
- 4 spaces, no tabs
- Function prefix: `enjoy_` (e.g., `enjoy_register_destination_post_type`)
- Always escape output (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`)
- Always sanitize input (`sanitize_text_field`, `wp_unslash`, etc.)
- Use WordPress hooks, not direct calls
- Never query the database directly when a WP function exists

### CSS
- Mobile-first
- Use CSS custom properties for colors/spacing where it makes sense
- Comment overrides with the original JNews selector being overridden
- Keep specificity as low as possible — avoid `!important` unless overriding inline styles from JNews
- Group rules by component / page area

### JavaScript
- Vanilla JS preferred for small additions
- Enqueue properly via `wp_enqueue_script`
- No jQuery unless interacting with existing JNews jQuery code

### File naming
- PHP: `kebab-case.php` (e.g., `custom-post-types.php`)
- CSS: `kebab-case.css`
- Function names: `snake_case` with `enjoy_` prefix

## Git conventions

- **Branch naming:** `feature/short-description`, `fix/short-description`, `audit/short-description`
- **Commit messages:** Imperative, present tense. First line ≤ 60 chars. Body explains why if non-obvious.
  - Good: `Add custom post type for Croatian destinations`
  - Bad: `updated stuff`
- **One logical change per commit.**
- **Never commit:** `.env` files, credentials, `wp-config.php`, database dumps, `node_modules`, build artifacts
- **`.gitignore`** is set up for WordPress development — respect it

## Deployment workflow

1. **Develop locally** in Local by Flywheel — edit files in `~/claude-projects/enjoy-hr/jnews-child/`, view at `http://enjoyhr.local`
2. **Commit to Git** (feature branch)
3. **Push child theme to staging** via rsync — test there at `https://stagin1.enjoy.hr` *(note: subdomain typo is intentional — leave as-is)*
4. **Verify on staging**
5. **Push to production** only after staging verification + Marko's approval
6. **Tag releases** for significant deployments

Detailed steps in `docs/deployment.md`.

## Tools you should use

- **WP-CLI** for any WordPress administration task — it's faster and safer than wp-admin clicking
- **rsync** for deploying files between environments
- **Git** for everything code-related
- **`grep`/`rg`** to find things in the JNews parent theme before deciding how to override

## Tools you should NOT use without confirmation

- `rm -rf` — confirm path with Marko first
- `wp db drop`, `wp db reset` — confirm
- `wp search-replace` against production — confirm
- Plugin/theme deactivation or deletion on production — confirm
- Direct database queries (`wp db query`) — confirm and explain

## Plugins that must NEVER be deactivated or deleted

- **elementor** — homepage and all 10 published posts are Elementor-built; removing it breaks the site immediately
- **elementor-pro** — Theme Builder controls header, footer, single post template, archive, 404, search results, and subscription popup; removing it collapses the entire site frame
- **seo-by-rank-math** / **seo-by-rank-math-pro** — all SEO meta, schema, and sitemaps
- **jnews-essential** — core JNews companion; required for theme function

Lesson learned 2026-04-26: Elementor was incorrectly assessed as unused (file-grep cannot detect page builder postmeta usage). Deletion caused a production outage. See `docs/audit-findings.md` → Lessons Learned.

## When you encounter the parent JNews theme

- **Don't edit it.** Read it to understand structure.
- **Templates can be overridden** by copying to the child theme with the same path
- **Functions can be overridden** via hooks/filters JNews exposes — search for `apply_filters` and `do_action` in the parent
- **Styles can be overridden** with higher specificity in the child theme stylesheet
- If JNews provides no hook/filter for what you need, document the limitation and propose an approach (custom plugin, JavaScript, etc.) before patching the parent

## Language

- **Code, comments, commits, documentation:** English
- **User-facing strings on the site:** Croatian (or English where appropriate for tourism audience)
- **Croatian variant:** Use Croatian forms only — not Serbian or Bosnian. "spremljeno" not "sačuvano", "tisuću" not "hiljadu", etc.

## Performance budget (rough targets)

- Lighthouse Performance: 80+ on mobile (current state TBD by audit)
- LCP: < 2.5s on mobile
- CLS: < 0.1
- No new external scripts without justification
- Images: WebP where possible, lazy-loaded, properly sized

## Things to flag to Marko proactively

- Plugin or theme update available with security implications
- Performance regressions
- Broken canonical URLs or hreflang tags
- Missing alt text on widely-used images
- PHP errors or warnings in `error_log`
- Unused plugins consuming resources
- Plugins that duplicate functionality

---

## Current site state (updated 2026-04-26)

**Read this at the start of every session.**

### Active theme per environment

| Environment | Active theme | OG meta fix live? | Notes |
|---|---|---|---|
| Local (enjoyhr.local) | jnews-child | ✅ Yes | Symlinked from repo |
| Staging (stagin1.enjoy.hr) | jnews-child | ✅ Yes | Verified working |
| Production (enjoy.hr) | **jnews-child** | ✅ Yes | Task 02 complete — activated 2026-04-26, tagged v1.0.0 |

### What the jnews-child theme does

`functions.php` contains two things:
1. Enqueues the JNews parent stylesheet
2. Removes JNews's duplicate OG/Twitter/JSON-LD meta output via `remove_action('wp_head', ...)` on the `wp` hook at priority 20 — this fixes `og:description` being poisoned with raw "edit post" admin markup

### Open tasks

No open deployment tasks. Next work items come from the audit findings in `docs/audit-findings.md` — see "Recommended improvements" and "Open questions for Marko".

### Audit (Task 01) — closed | Deployment (Task 02) — closed

Findings are in `docs/audit-findings.md`. Key things to know:
- Security incident (backup exposure) fully remediated
- Elementor + Elementor Pro: required infrastructure — Theme Builder powers header, footer, single post, archive, 404, search, popup
- Plugin "unused" assessment methodology corrected — always check postmeta and `elementor_library` CPT before declaring a page builder unused
- OG/Twitter meta fix live on all three environments as of 2026-04-26

### Known production DB state

- Table prefix: `ec_` (non-standard)
- `theme_mods_jnews-child` row exists — copied from `theme_mods_jnews` during Task 02 deploy (2026-04-26)

### SSH / deployment quick reference

```bash
# SSH alias
ssh enjoyhr
# = ssh -i ~/.ssh/hostinger_enjoycroatia -p 65002 u320042257@92.112.187.42

# Production WP path
domains/enjoy.hr/public_html/

# Staging WP path
domains/enjoy.hr/public_html/stagin1/

# Production DB: u320042257_ctAnI  (user: u320042257_YYtz5)
# Staging DB:    u320042257_Lmk1i  (user: u320042257_029ST)
```
