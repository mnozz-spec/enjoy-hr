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
│   └── 01-site-audit.md
├── docs/                      # Project documentation
│   ├── workflow.md            # Local → staging → production
│   ├── deployment.md          # How to deploy changes
│   └── audit-findings.md      # Created during first audit
├── enjoy-croatia/               # The JNews child theme (Git-tracked source)
│   ├── style.css
│   ├── functions.php
│   ├── screenshot.png
│   └── ...
└── snippets/                  # Standalone PHP snippets (reference)
```

The `enjoy-croatia/` folder is symlinked into Local's WordPress install:

```
~/Local Sites/enjoyhr/app/public/wp-content/themes/enjoy-croatia  →  ~/claude-projects/enjoy-hr/enjoy-croatia
```

You edit the source in `~/claude-projects/enjoy-hr/enjoy-croatia/` and WordPress reads it through the symlink.

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

1. **Develop locally** in Local by Flywheel — edit files in `~/claude-projects/enjoy-hr/enjoy-croatia/`, view at `http://enjoyhr.local`
2. **Commit to Git** (feature branch)
3. **Push child theme to staging** via rsync — test there at `https://staging1.enjoy.hr`
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
- Plugin/theme deactivation on production — confirm
- Direct database queries (`wp db query`) — confirm and explain

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
