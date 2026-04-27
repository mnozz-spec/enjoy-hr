# Site Audit — enjoy.hr

**Date:** 2026-04-26
**Audited by:** Claude Code with Marko Nožica
**Environment:** Local copy of production (enjoyhr.local) + production HTTP checks

---

## Executive summary

enjoy.hr is a pre-launch tourism content site with a sound technical foundation. WordPress 6.9.4 is current, PHP 8.2.30 is modern, the JNews parent theme is unmodified, and a child theme is now active. With 10 published posts and ~50 planned before launch the content pipeline is on track. There are no catastrophic problems, and the site is on a workable trajectory — but this audit uncovered issues in four areas that need attention before launch.

**Security** — A 1.8GB full-site backup was publicly accessible by direct URL for approximately two months (Feb 19 – Apr 26). The exposure has been fully remediated: backup deleted, salts rotated, admin password reset, user accounts audited, `wp-config.php` hardened to 600, and All-in-One WP Migration deactivated and deleted. No unfamiliar admin accounts found. Site confirmed loading correctly post-cleanup. Incident closed.

**SEO** — JNews and Rank Math are both outputting Open Graph, Twitter Card, and JSON-LD schema tags. JNews wins on first-occurrence, and its homepage `og:description` contains raw admin-UI markup. Anyone sharing the homepage URL on social gets a broken preview. One settings change in JNews Dashboard fixes this immediately.

**Performance** — Cannot be measured yet (PageSpeed Insights API quota not elevated). Plugin weight is a red flag: 76MB Elementor + 18MB Elementor Pro (both required — essential infrastructure) + 21MB Site Kit + 20MB WebP Express + 13MB video plugin is an unusually heavy baseline for a pre-launch site. Elementor and Elementor Pro cannot be removed: they power the homepage, all article layouts, header, footer, 404, archive, search results, and popup via Theme Builder.

**Accessibility** — Lighthouse scores 81/100 on local (meets the 80+ target). Five failures are all JNews theme defaults addressable with CSS overrides in the child theme and one `aria-label` content fix. No template copies required.

---

## Top 3 quick wins (do these first)

1. **Disable JNews Open Graph output** — One settings change stops the mangled social preview on the homepage. wp-admin → JNews Dashboard → Social → Open Graph → disable. Takes 2 minutes, fixes the most visible pre-launch SEO issue.

2. ~~**Remove FakerPress and All-in-One WP Migration**~~ — ✓ All-in-One WP Migration deactivated and deleted 2026-04-26. ✓ FakerPress deleted from staging + production 2026-04-26 — 14MB reclaimed.

3. ~~**Remove Elementor**~~ — Elementor + Elementor Pro are **essential infrastructure**. Both reactivated 2026-04-26. See Lessons Learned for full incident and methodology correction. Biggest performance lever is now elsewhere (Site Kit, video plugin).

---

## Quick wins (low effort, high value)

1. **Disable JNews Open Graph output** — Fixes mangled social previews. wp-admin → JNews Dashboard → Social → Open Graph → disable. 2 minutes.
2. ~~**Remove FakerPress**~~ — ✓ Done 2026-04-26. Deleted from staging + production. 14MB reclaimed.
3. ~~**Update overdue plugins**~~ — ✓ Done 2026-04-26. Mailchimp for WP 4.12.2, Rank Math 1.0.268, WP Super Cache 3.1.0. Elementor had no update available.
4. ~~**Override the hero gradient**~~ — ✓ Done 2026-04-26. Condé Nast aesthetic applied across hero, post cards (+ text-shadow), and category headers. Tagged v1.1.0.
5. **Delete 55 empty categories** — bloat sitemaps and nav menus. Consolidate alongside content production.
6. ~~**Delete the .wpress backup file**~~ — ✓ Done 2026-04-26.
7. ~~**Fix wp-config.php permissions**~~ — ✓ Done 2026-04-26. Changed to 600.
8. ~~**Salt rotation**~~ — ✓ Done 2026-04-26. All 8 keys replaced on production + local.
9. ~~**Remove All-in-One WP Migration**~~ — ✓ Done 2026-04-26. Deactivated and deleted.

---

## Recommended improvements (planned work)

1. **Gradient overlay overrides** — documented below; ready to implement in child theme.
2. **Fix duplicate OG/Twitter/Schema output** — disable JNews's meta output (see SEO section). One-time settings change, no code required.
3. ~~**Remove All-in-One WP Migration**~~ — ✓ Done 2026-04-26. Deactivated and deleted.
4. **Elementor + Elementor Pro — REQUIRED INFRASTRUCTURE, do not remove** — Both plugins are essential. Elementor (free) powers the homepage and all 10 published posts. Elementor Pro powers the entire site frame via Theme Builder: Header, Footer, Single Post template, Posts Archive, 404 Page, Search Results, and a Subscription Popup — all confirmed active `elementor_library` templates (IDs 253, 258, 242, 249, 246, 239, 233). Deletion of either plugin causes immediate full-site breakage. Confirmed by production incident 2026-04-26 — see Lessons Learned. Both reactivated and verified 2026-04-26.
3. **Consolidate category taxonomy** — 55 empty categories of 80+ total is excessive even pre-launch. Consolidate to 8–15 user-intent-based categories (geographic regions, trip types, practical/seasonal). Curate alongside content production over the next 2–4 weeks rather than bulk-deleting now.
4. **Evaluate videojs-html5-player** — 13MB active plugin. If no video embeds exist in posts, deactivate.
5. **Implement WebP for all existing images** — WebP Express is installed; verify it is converting and serving correctly.
6. **Accessibility CSS fixes** — All 5 Lighthouse failures are addressable in `jnews-child/style.css`:
   - Color contrast: increase contrast on `.jeg_post_category a` badge text
   - Touch targets: increase padding on small link elements
   - Link underline: add `text-decoration: underline` for in-content `article a` links
   - `<main>` landmark: add `role="main"` to the primary content wrapper (or copy the page template to child theme)
   - Instagram link: add `aria-label` to bare anchor wrapping Instagram embed image

---

## Watch-list (monitor, no immediate action)

1. ~~**jnews-social-login (6.8MB)**~~ — ✓ Deleted 2026-04-27. Never configured, zero social users. See Plugin evaluation below.
2. **google-site-kit (21MB)** — heavy plugin; once analytics are verified working, consider lighter alternatives (direct GA4 snippet via child theme).
3. **hello-elementor theme** — inactive but has update available; either update or delete.
4. **enjoy-croatia theme folder** — abandoned, should be deleted from `wp-content/themes/` once decision is made.

---

## Pending manual fix in wp-admin

**Social counter widget contains Jegtheme demo data** — the `jnews_social_counter` widget (present in the sidebar/footer widget area) was never updated after theme installation. It still references `jegtheme` as the Facebook page and `envato` as the Instagram account. Marko needs to either: (a) update the widget with enjoy.hr's actual social accounts, or (b) remove the widget entirely if no social follower counts are to be displayed. Fix via wp-admin → Appearance → Widgets. Non-blocking.

---

## Detailed findings

### Theme & plugins

**WordPress core:** 6.9.4 — current as of audit date.

**Theme stack:**
| Theme | Status | Version | Notes |
|---|---|---|---|
| jnews | Parent | 12.0.5 | Unmodified — confirmed by audit |
| jnews-child | **Active** | 1.0.0 | Created this session; Customizer settings migrated from parent |
| enjoy-croatia | Inactive | 1.0 | Abandoned leftover — pending deletion decision |
| hello-elementor | Inactive | 3.4.5 | Update available; unused |
| twentytwenty{three,four,five} | Inactive | various | Default WordPress themes |

**Active plugins:**

| Plugin | Version | Size | Update? | Flag |
|---|---|---|---|---|
| elementor | 4.0.1 | 76MB | **YES** | **REQUIRED** — homepage + all posts + Theme Builder; update pending |
| google-site-kit | 1.177.0 | 21MB | No | Heavy; verify GA4 is actually receiving data |
| webp-express | 0.25.14 | 20MB | No | Verify it is converting and serving correctly |
| elementor-pro | 4.0.3 | 18MB | No | **REQUIRED** — Theme Builder (header, footer, single post, archive, 404, search, popup); reactivated 2026-04-26 |
| videojs-html5-player | 1.1.13 | 13MB | No | Active but is video actually used? |
| seo-by-rank-math | 1.0.267 | 12MB | **YES** | Update pending |
| seo-by-rank-math-pro | 3.0.108 | 11MB | No | |
| jnews-essential | 12.0.4 | 8.9MB | No | Core JNews companion |
| jnews-social-login | 12.0.0 | 6.8MB | No | Verify if social login is offered |
| wp-super-cache | 3.0.3 | 3.8MB | **YES** | Update pending |
| all-in-one-wp-migration | 7.105 | 2.3MB | No | **Should be deactivated** post-migration |
| mailchimp-for-wp | 4.12.1 | 1.3MB | **YES** | Update pending |
| disable-comments | 2.7.0 | 856KB | No | |
| jnews-view-counter | 12.0.0 | 524KB | No | |
| jnews-weather | 12.0.1 | 144KB | No | |
| jnews-social-share | 12.0.0 | 132KB | No | |
| ajax-thumbnail-rebuild | 1.14 | 116KB | No | Utility; can deactivate when not in use |
| jnews-meta-header | 12.0.1 | 100KB | No | |
| jnews-gallery | 12.0.1 | 104KB | No | |
| jnews-instagram | 12.0.0 | 104KB | No | |
| jnews-like | 12.0.0 | 84KB | No | |
| ads-txt | 1.4.6 | 68KB | No | |
| jnews-jsonld | 12.0.0 | 56KB | No | Schema/JSON-LD output |
| default-featured-image | 1.8.2 | 52KB | No | |
| jnews-breadcrumb | 12.0.0 | 20KB | No | |

**Inactive plugins (candidates for removal):**

| Plugin | Size | Verdict |
|---|---|---|
| fakerpress | 14MB | **Delete** — test data generator, no place on production |
| hello-elementor | — | Delete (or update if Elementor pages need it) |
| twentytwenty{three,four,five} | — | Delete all three |

**Database hygiene:**
- Autoloaded options: 154 entries, **5KB total** — healthy (nothing bloated)
- Transients: 19 — very clean
- No `debug.log` found (WP_DEBUG_LOG not enabled — fine for production)
- No leftover files (`info.php`, `test.php`, etc.)
- No default "Hello world" post

---

### Content structure

| Metric | Value |
|---|---|
| Published posts | 10 |
| Draft posts | 5 |
| Categories (total) | 80+ |
| Categories with ≥1 post | ~25 |
| **Empty categories** | **55** |
| Tags | 59 |
| Uncategorized posts | 0 |

**Post types in use:** Standard `post` and `page` only, plus Elementor/Rank Math/JNews internal types (templates, schemas, snippets). No custom post types for destinations or tourism-specific content yet.

**Key observation:** The taxonomy was pre-planned ambitiously — 80+ categories covering every region, activity type, and content format. 55 categories are currently empty. Even accounting for pre-launch state, this is excessive: empty categories appear in XML sitemaps and nav menus and dilute crawl efficiency. Recommend consolidating to 8–15 user-intent-based categories (geographic regions, trip types, practical/seasonal) alongside content production over the next 2–4 weeks, rather than bulk-deleting now.

**Drafts in pipeline:**
- What to Pack for Croatia in Summer
- Do I Need Cash in Croatia?
- Cash or Card in Croatia? (overlaps with above — possible duplicate)
- Best Day Trips from Dubrovnik
- Zagreb Airport (duplicate slug of a published post — worth checking)

---

### Gradient overlay analysis

This is the priority finding. Four distinct gradient overlays found across three JNews CSS files. All are overridable with standard CSS specificity in `jnews-child/style.css` — no PHP or template changes required.

#### 1. Featured post card overlay (homepage grid, post blocks)

The dark scrim that covers the bottom of large and medium thumbnail cards, making the post title readable.

- **File:** `jnews/assets/css/main.css`
- **Selector:** `.jeg_pl_lg_box .jeg_postblock_content, .jeg_pl_md_box .jeg_postblock_content`
- **Current value:** `linear-gradient(to bottom, rgba(0,0,0,0) 0, rgba(0,0,0,.65) 70%)`
- **Effect:** Transparent top → 65% black at 70% — moderately heavy

**Override CSS for child theme:**
```css
/* Lighten post card gradient overlay */
.jeg_pl_lg_box .jeg_postblock_content,
.jeg_pl_md_box .jeg_postblock_content {
    background: linear-gradient(to bottom, rgba(0,0,0,0) 0, rgba(0,0,0,.45) 100%);
}
```

---

#### 2. Hero section overlay (hero styles 1 & 2 — current site layout)

The heavy black fade on the main hero/featured image block on the homepage.

- **File:** `jnews/assets/css/jnewshero.css`
- **Selector:** `.jeg_hero_style_1 .jeg_thumb a>div:before, .jeg_hero_style_2 .jeg_thumb a>div:before`
- **Current value:** `linear-gradient(to bottom, rgba(0,0,0,0) 0, #000 65%, #000 100%)`
- **Effect:** Transparent top → solid black at 65% and holds solid to bottom — very heavy. The image is almost entirely obscured in the lower half.

**Override CSS for child theme:**
```css
/* Soften hero image overlay — reduce to translucent instead of solid black */
.jeg_hero_style_1 .jeg_thumb a>div:before,
.jeg_hero_style_2 .jeg_thumb a>div:before {
    background: linear-gradient(to bottom, rgba(0,0,0,0) 0, rgba(0,0,0,.6) 100%);
}
```

---

#### 3. Category page header overlay

The dark overlay on category banner images (visible when browsing a category archive with a header image set).

- **File:** `jnews/assets/css/pages.css`
- **Selector:** `.jeg_cat_overlay.dark .jeg_cat_bg:after`
- **Current value:** `linear-gradient(to bottom, rgba(0,0,0,.3) 0, rgba(0,0,0,.65) 100%)`
- **Effect:** 30% black at top → 65% at bottom — makes category images noticeably dark

**Override CSS for child theme:**
```css
/* Lighten category header overlay */
.jeg_cat_overlay.dark .jeg_cat_bg:after {
    background: linear-gradient(to bottom, rgba(0,0,0,.1) 0, rgba(0,0,0,.45) 100%);
}
```

---

#### 4. General background overlay utility

A utility class used in various widget/block contexts.

- **File:** `jnews/assets/css/main.css`
- **Selector:** `.jeg_bg_overlay`
- **Current value:** `linear-gradient(to bottom, #000, #434343)` — solid colours, not transparent
- **Note:** Only override if you see this class causing visual problems; it may not be in active use on the current layout.

---

#### How to apply all overrides at once

Add to `jnews-child/style.css` — no PHP changes needed, no template copies needed:

```css
/* ==============================================
   GRADIENT OVERLAY OVERRIDES
   Reduces JNews default heavy dark overlays
   Source: jnews/assets/css/main.css, jnewshero.css, pages.css
   ============================================== */

/* Featured post card thumbnails (homepage grid) */
.jeg_pl_lg_box .jeg_postblock_content,
.jeg_pl_md_box .jeg_postblock_content {
    background: linear-gradient(to bottom, rgba(0,0,0,0) 0, rgba(0,0,0,.45) 100%);
}

/* Hero section (styles 1 & 2) */
.jeg_hero_style_1 .jeg_thumb a>div:before,
.jeg_hero_style_2 .jeg_thumb a>div:before {
    background: linear-gradient(to bottom, rgba(0,0,0,0) 0, rgba(0,0,0,.6) 100%);
}

/* Category archive header */
.jeg_cat_overlay.dark .jeg_cat_bg:after {
    background: linear-gradient(to bottom, rgba(0,0,0,.1) 0, rgba(0,0,0,.45) 100%);
}
```

The exact opacity values (`.45`, `.6`) are starting points — adjust to taste in Local, then deploy to staging.

---

### Performance

PageSpeed Insights API unavailable during this audit — the Google Cloud project associated with `$PAGESPEED_API_KEY` has `quota_limit_value: 0` (default, never elevated). To enable future runs: Google Cloud Console → APIs & Services → PageSpeed Insights API → Quotas → request increase for "Queries per day".

Proxy indicators suggest performance risk: Elementor (76MB) + Google Site Kit (21MB) + WebP Express (20MB) + videojs-html5-player (13MB) is a heavy stack. Once Elementor and possibly video plugin are removed and PSI quota is active, run a baseline check.

**Target benchmarks (per project brief):** Lighthouse Performance 80+ mobile · LCP < 2.5s · CLS < 0.1

---

### SEO

**Crawling & indexing**

| Check | Result |
|---|---|
| robots.txt | ✓ Correct — allows Googlebot/Bingbot, blocks AI crawlers (ClaudeBot, GPTBot, Google-Extended, Amazonbot, etc.) |
| Sitemap | ✓ Rank Math generating at `/sitemap_index.xml` → post-sitemap.xml, page-sitemap.xml, footer-sitemap.xml |
| Canonical (homepage) | ✓ `https://enjoy.hr/` |
| Canonical (article) | ✓ `https://enjoy.hr/croatia-euro-payment-methods-exchange/` |
| robots meta | ✓ `follow, index` on all pages checked |
| hreflang | None — site is English-only; acceptable for monolingual content |

**Open Graph & Twitter Cards — critical issue**

Both JNews and Rank Math are outputting OG, Twitter Card, and JSON-LD schema tags. JNews's output appears **first** in the `<head>`; social crawlers and parsers use the first valid occurrence.

| Tag | JNews output (appears first) | Rank Math output | Winner |
|---|---|---|---|
| `og:title` (homepage) | "Home" | "Enjoy Croatia - Your Tour Guide to Croatia" | JNews ❌ |
| `og:description` (homepage) | Raw admin UI: *"edit post Money &amp;amp; Payments…"* | Proper marketing description | JNews ❌ |
| `twitter:card` (homepage) | `summary` | `summary_large_image` | JNews ❌ |
| `og:image` | JPEG | WebP (homepage) / JPEG (articles) | — |
| `twitter:site` | `""` (empty) | `""` (empty) | Neither — no X handle set |

The homepage `og:description` from JNews contains raw PHP/template output (`edit post`, HTML-encoded ampersands) — this is what Facebook, LinkedIn, and Twitter/X will show when the homepage URL is shared, until the JNews meta output is disabled.

**Fix:** In WordPress admin → JNews Dashboard → Social → Open Graph — disable JNews's own Open Graph and Twitter Card output. Rank Math's output is correct and complete.

**JSON-LD Schema**

Three separate `application/ld+json` blocks are output on every page:
1. Rank Math — `Person`/`Organization` with logo, sameAs, full graph ✓
2. JNews — `Organization` with `"name": ""` (empty) and `"logo": {"url": ""}` (empty) ❌
3. JNews — `WebSite` with `"name": ""` (empty) ❌

Fixing the duplicate meta output (above) will also resolve the empty-name schema blocks.

**Additional notes**
- OG image on articles is JPEG — WebP Express is installed but article featured images are not being served as WebP in OG tags. Verify WebP conversion is running on existing uploads.
- No `twitter:site` handle configured in Rank Math → Social → Twitter. Add when site has an active X account.

---

### Accessibility

**Lighthouse score: 81/100** — meets the 80+ mobile target. 17 checks pass, 5 fail. All failures are JNews theme defaults; none require template overrides — CSS and one content fix cover everything.

| Check | Score | Detail |
|---|---|---|
| Color contrast | ✗ | Category badge links (e.g. `.category-money-payments`) have insufficient foreground/background contrast. JNews uses low-opacity colored text on near-white badges. Override with higher-contrast palette in child theme. |
| `<main>` landmark | ✗ | Page has no `<main>` element — JNews wraps content in generic `<div>` containers. Add `role="main"` via JS or copy the JNews page template to child theme and add `<main>`. |
| Links without discernible name | ✗ | One Instagram embed link has no text or `aria-label` — just a bare `<a>` wrapping an image without alt text. Add `aria-label` to the Instagram widget or ensure images have alt text. |
| Links rely on color only | ✗ | Body text links use color but no underline or other visual indicator. Add `text-decoration: underline` for in-content links in child theme CSS. |
| Touch target size | ✗ | Category badge links and some post links are below 48×48px minimum touch target. Increase padding on `.jeg_post_category a` and similar in child theme CSS. |

All 5 failures are fixable with CSS overrides in `jnews-child/style.css` or minor content corrections — no PHP template copies required except possibly the `<main>` landmark. Re-run after fixes; score should reach 90+.

---

### Security incident — backup file exposure

**Severity:** High  
**Exposure window:** 2026-02-19 → 2026-04-26 (~2 months)  
**Status:** ✓ Fully resolved 2026-04-26 — all response actions complete, plugin removed, site verified

#### What happened

All-in-One WP Migration stores backup archives in `wp-content/ai1wm-backups/`. The plugin places an `.htaccess` in that directory, but it only disables directory listing — it does **not** block direct file access. A 1.8GB `.wpress` archive (`enjoy-hr-20260219-184056-4xq1e7ub90td.wpress`) created during the February migration was accessible by direct URL for approximately two months.

The archive contained the complete WordPress file system and a full database dump, including `wp-config.php` with database credentials and authentication keys/salts.

#### What is not known

There is no access log visibility at the shared hosting level. It cannot be determined whether anyone downloaded the file during the exposure window. The filename included a randomised token (`4xq1e7ub90td`) which provides some obscurity, but not security.

#### Response — completed 2026-04-26

| Action | Status |
|---|---|
| Backup file deleted from server | ✓ |
| WordPress authentication keys/salts rotated (production + local) | ✓ |
| Admin password reset | ✓ |
| Admin user accounts audited — no unfamiliar accounts found | ✓ |
| `wp-config.php` permissions hardened to 600 | ✓ |
| All-in-One WP Migration deactivated and deleted | ✓ |
| `ai1wm-backups/` folder confirmed empty / removed | ✓ |
| Site verified loading correctly post-cleanup | ✓ |

**Incident closed.**

#### Systemic note

All-in-One WP Migration stores backups in the web root with insufficient access controls on shared hosting. The plugin has been removed. If a backup plugin is needed in future, prefer one that writes to remote destinations (Google Drive, S3) rather than a local web-accessible directory — or use Hostinger's native infrastructure-level backups.

---

### Security & hygiene

| Check | Status | Notes |
|---|---|---|
| WordPress | 6.9.4 ✓ | Current |
| PHP | 8.2.30 ✓ | Modern |
| JNews parent | Unmodified ✓ | Confirmed by audit |
| debug.log | Not present ✓ | WP_DEBUG_LOG off |
| Leftover test files | None ✓ | No info.php, test.php, etc. |
| Hello World post | Not present ✓ | |
| wp-config.php permissions | ✓ 600 — fixed 2026-04-26 | Owner read/write only |
| wp-config.php salts | ✓ Rotated 2026-04-26 | All 8 keys replaced on production + local |
| Admin password | ✓ Reset 2026-04-26 | |
| Admin user accounts | ✓ Audited 2026-04-26 | No unfamiliar accounts |
| .htaccess permissions | 644 ✓ | Standard for Apache |
| wp-content/ permissions | 755 ✓ | Standard |
| uploads/ permissions | 755 ✓ | Standard |
| All-in-One WP Migration | ✓ Deleted 2026-04-26 | Removed as root cause of security incident |
| Backup file exposure | ✓ Resolved 2026-04-26 | See security incident section above |

---

## Lessons learned — 2026-04-26

### Incident: "Elementor unused" finding was incorrect — caused production outage

**What happened:** The audit declared Elementor "confirmed unused" based on file-level grep of plugin references. Marko acted on this finding and deleted Elementor + Elementor Pro from production on 2026-04-26. The site immediately broke: admin "edit post" links appeared publicly on every article card, card styling disappeared entirely, and OG/Twitter meta pulled the broken rendered content into `og:description`. Elementor (free) was reactivated, restoring the homepage. Investigation then confirmed Elementor Pro was also required — Theme Builder templates for header, footer, single post, archive, 404, search results, and a subscription popup were all active Pro features. Both plugins were reactivated and the site fully restored.

**Root cause of incorrect finding:** Plugin usage by page builders is stored in `wp_postmeta`, not in PHP files. The keys `_elementor_edit_mode = "builder"` and `_elementor_data` (the full layout JSON) live in the database. Scanning plugin folders and theme files for string references only catches code-level dependencies — it misses every page that was designed visually in the builder. Additionally, Elementor Pro's Theme Builder templates are stored as `elementor_library` custom post type entries in the database; they have no corresponding PHP files in the plugin or theme to grep.

**Correct methodology before declaring any page builder unused:**

1. **Check postmeta for builder edit mode on key pages:**
   ```bash
   wp db query "SELECT post_id, post_title FROM ec_postmeta pm JOIN ec_posts p ON pm.post_id = p.ID WHERE pm.meta_key = '_elementor_edit_mode' AND pm.meta_value = 'builder';" | grep -v revision
   ```
2. **Check for Elementor Pro Theme Builder templates** (these are always `elementor_library` post type):
   ```bash
   wp db query "SELECT post_id, post_title, post_status FROM ec_postmeta pm JOIN ec_posts p ON pm.post_id = p.ID WHERE pm.meta_key = '_elementor_edit_mode' AND pm.meta_value = 'builder' AND p.post_type = 'elementor_library' AND p.post_status = 'publish';"
   ```
3. **Visual inspection in incognito** — load homepage, a single post, a category archive, and 404 in a private browser window. If the layout depends on the plugin, removal will be immediately visible.
4. **Assume required until proven otherwise** — file-level grep is evidence of absence only for code-level dependencies. Treat absence of grep hits as inconclusive for page builders.

**Future audit rule:** Never declare a page builder plugin unused based on file scanning alone. Postmeta and custom post types are the authoritative source.

---

### Deployment retrospective — 2026-04-26

**Goal:** Deploy JNews child theme (OG meta fix) to production.

**What was completed:**
- JNews OG meta fix coded in `jnews-child/functions.php` and tested locally ✅
- Committed and pushed to GitHub ✅
- Staging deployment completed and fully verified (incognito check, OG tags, error log) ✅
- Production rsync (file-only, theme not activated) completed ✅

**What was interrupted:**
- Production theme activation (Step 4) was paused at approval gate
- Before approval was given, Marko deleted Elementor + Pro during manual plugin audit
- Site appeared broken; deployment was rolled back (rollback had no effect — child theme was never activated)
- Investigation confirmed Elementor deletion was the actual cause, not the deployment

**Outcome:**
- Production child theme files are on the server but the theme is NOT active
- The OG meta fix is NOT live on production
- Production is running JNews parent theme
- Elementor + Pro reactivated, site fully restored
- Deployment to be re-attempted in a future session (see `tasks/02-production-og-fix-deploy.md`)

---

### Current state as of end of 2026-04-26

| | Local | Staging | Production |
|---|---|---|---|
| Active theme | jnews-child | jnews-child | **jnews-child** ✅ |
| JNews OG fix | ✅ live | ✅ live | ✅ live (Task 02 complete) |
| theme_mods copied | ✅ | ✅ | ✅ done |
| jnews-child files | n/a | deployed | deployed and active |
| Elementor free | ✅ active | ✅ active | ✅ active |
| Elementor Pro | ✅ active | ✅ active | ✅ active (reactivated) |
| Security cleanup | ✅ done | n/a | ✅ done |

**Task 02 closed 2026-04-26.** All three environments now match: jnews-child active, Rank Math OG/Twitter/JSON-LD output clean, no JNews duplicate meta. Tagged v1.0.0.

---

## Open questions for Marko

10. **WP Super Cache — caching disabled on both environments.** `$cache_enabled = false` on both staging and production — every request is hitting PHP with no caching benefit. Three options: (a) enable WP Super Cache post-launch once real traffic exists to validate behaviour; (b) replace with **LiteSpeed Cache**, which is Hostinger-native — Hostinger servers return `x-turbo-charged-by: LiteSpeed` headers, meaning the server-level cache is already there and LiteSpeed Cache plugin unlocks it properly; (c) leave disabled and decide before launch. **Recommendation:** evaluate LiteSpeed Cache as a replacement before launch — Hostinger sites consistently perform better with it than WP Super Cache, and the server infrastructure already supports it.

1. ~~**Elementor Pro reactivation**~~ — ✓ Resolved 2026-04-26. Elementor Pro confirmed required: Theme Builder templates for header, footer, single post, archive, 404, search, and popup all active. Both Elementor free and Pro reactivated. Do not remove either.
2. **Video content:** Is videojs-html5-player actually used? Any posts with video embeds? If not, 13MB plugin can go.
3. **Social login:** Is jnews-social-login (6.8MB) actively offered to visitors? Any registered users via Facebook/Google?
4. **Category cleanup:** Are all 80+ categories intentional for a future content plan, or can we trim to ~20 active ones now?
5. **Cash/Card duplicate drafts:** Two drafts cover the same topic ("Do I Need Cash" and "Cash or Card"). Intentional A/B headline test, or one should be deleted?
6. **Gradient opacity preferences:** The override values above (`.45`, `.6`) are suggestions. What feel are you going for — barely-there scrim, or still a strong overlay but softer?
7. **Rank Math vs. Yoast:** You have Rank Math active. No Yoast. Was this a deliberate switch? Anything worth preserving from a previous Yoast setup?
8. **Twitter/X handle:** Once you have an active X account for enjoy.hr, add it in Rank Math → Social → Twitter so `twitter:site` is populated.
9. ~~**wp-config.php permissions, backup file, and AI1WM removal**~~ — ✓ All resolved 2026-04-26. Incident closed.
