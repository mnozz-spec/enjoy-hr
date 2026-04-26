# Task 01 — Site Audit & Inventory

## Goal

Produce a baseline understanding of the current state of enjoy.hr so we can prioritize improvements based on data, not guesses.

## Why this first

Marko's first ask: *"Analyze the site and tell me what you see and what things we can work on."* You can't propose meaningful changes without knowing what exists.

## Output

A single document: `docs/audit-findings.md`. Structured as below.

## What to audit

### 1. Theme & plugin inventory

Run on staging (or local once available):

```bash
wp theme list
wp plugin list
wp core version
```

Document:
- WordPress version
- JNews version + last update
- Whether a child theme exists, is active, or needs to be created
- All active plugins, version, last update, purpose
- Any inactive plugins (candidates for removal)
- Any plugins overdue for updates
- Any plugins flagged as abandoned by WordPress.org

### 2. Theme structure & customization points

Inspect the JNews parent theme structure:

```
domains/enjoy.hr/public_html/wp-content/themes/jnews/
```

Document:
- Where the gradient overlays Marko mentioned likely live (search CSS for `linear-gradient`, `background-image`)
- Available filters/hooks JNews exposes (search for `apply_filters` and `do_action` in the parent)
- Template files most likely to be overridden in the child theme

### 3. Performance baseline

- PageSpeed Insights for homepage and 1 article page (mobile + desktop)
- Note Core Web Vitals: LCP, CLS, INP
- Identify obvious offenders: large images, render-blocking resources, unused CSS, third-party scripts

### 4. SEO state

- Active SEO plugin (Yoast / Rank Math / SEOPress / other)?
- XML sitemap present and valid?
- Schema markup present (Article schema, breadcrumbs)?
- hreflang tags if multilingual?
- Robots.txt sane?
- Canonical URLs working?

### 5. Accessibility quick check

- Run axe DevTools or Lighthouse Accessibility on homepage
- Note major issues (color contrast, missing alt text, heading hierarchy)
- Don't audit every page — just flag patterns

### 6. Security & hygiene

- WP version current?
- PHP version (already known: 8.2.30 — fine)
- File permissions sane?
- `wp-config.php` not world-readable?
- Any obvious leftovers (`info.php`, `phpinfo()`, default `Hello world` post)?
- Any error log entries: `tail -100 wp-content/debug.log` (if exists)

### 7. Content structure

- Post types in use (just `post` and `page`, or custom?)
- Number of published posts
- Categories and tags structure
- Any obvious organizational issues (orphan posts, uncategorized content)

### 8. The gradient question (Marko's specific concern)

Find the gradient overlays Marko mentioned. Likely candidates:
- Featured image overlays in JNews block layouts
- Hero section gradients
- Card hover states

For each, document:
- Selector
- Current gradient values
- File location in JNews parent
- How a child theme would override it

## Format of `docs/audit-findings.md`

```markdown
# Site Audit — enjoy.hr

**Date:** [YYYY-MM-DD]
**Audited by:** Claude Code with Marko Nožica
**Environment:** staging1.enjoy.hr (or local copy)

## Executive summary

[2-3 paragraphs. What's the overall state? What's healthy? What needs attention? What's urgent?]

## Quick wins (low effort, high value)

1. ...
2. ...
3. ...

## Recommended improvements (planned work)

1. ...
2. ...

## Watch-list (monitor, no immediate action)

1. ...

## Detailed findings

### Theme & plugins
[...]

### Performance
[...]

### SEO
[...]

[etc]

## Gradient overlay analysis

[Selectors, files, override approach]

## Open questions for Marko

1. ...
2. ...
```

## Constraints during the audit

- **Read-only operations.** No changes during audit. Just gather information.
- **Use staging or local.** Don't run audit tools that hit production hard (PageSpeed Insights against production is fine — that's just an HTTP request).
- **Don't generate fake numbers.** If you can't measure something, say so. Don't invent Lighthouse scores.

## Done when

- [ ] `docs/audit-findings.md` exists, populated
- [ ] All 8 audit areas covered
- [ ] At least 3 concrete "quick wins" identified
- [ ] Gradient locations identified with proposed override approach
- [ ] Marko has reviewed and discussed findings
- [ ] Top 3 priorities for next work agreed
