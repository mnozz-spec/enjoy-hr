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
│   ├── 01-site-audit.md                  # ✅ Complete
│   ├── 02-production-og-fix-deploy.md    # ✅ Complete
│   └── 07-affiliate-disclosure.md        # ⬅ Next task
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
/Volumes/External - 4TB/Local Sites/enjoyhr/app/public/wp-content/themes/jnews-child  →  ~/claude-projects/enjoy-hr/jnews-child
```

You edit the source in `~/claude-projects/enjoy-hr/jnews-child/` and WordPress reads it through the symlink.

**The Local site lives on the external drive "External - 4TB"** (moved from `~/Local Sites/enjoyhr` on 2026-11-16). The drive must be mounted before starting the site in Local. The site's MySQL data did not move — Local keeps it on the internal drive at `~/Library/Application Support/Local/run/oYGNnL-sq/mysql/data`. Pre-move Local registry backup: `~/Library/Application Support/Local/sites.json.bak-pre-external-move-20261116-1105`.

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
- ~~elementor-pro~~ — no longer required. Deactivated on both environments 2026-11-25 after its three used pieces (404 template, Contact form, popup) were replaced in the child theme. It never rendered the header, footer, single post, archive or search (JNews does). Plugin files are still installed; deleting them needs Marko's OK
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

## Current site state (updated 2026-05-22)

**Read this at the start of every session.**

### ⚠️ DEPLOY TRIPWIRE — read before any theme rsync

**Production and staging `jnews-child/functions.php` are DIVERGENT from git.** Both environments contain a working `enjoy_affiliate_*` block (the Task 07 affiliate disclosure code) that is in no tag. It was committed on 2026-11-25 on branch `feature/design-review-followup` (not yet merged to `main`, not tagged), so `main` and every tag up to `v1.3.0` still lack it. A naive `rsync --delete` of any tag (including `v1.3.0`) to either environment will **silently clobber the live affiliate code**.

Until the Task 07 reckoning is done (see `tasks/07-affiliate-reckoning-followup.md`), any theme rsync MUST either:
1. Use `--exclude=functions.php` (other theme files can rsync safely), OR
2. Commit the affiliate code to git first so the tag actually contains it, OR
3. Patch `functions.php` in place server-side instead of via rsync (Option B pattern used in the 2026-05-22 v1.3.0 deploy).

The code is currently dormant — 0 posts have `_enjoy_has_affiliate_links` postmeta on either environment — so clobbering it would not surface immediately, which makes this trap especially dangerous.

### 🧭 Design review follow-up (2026-11-25)

Full critique: `.impeccable/critique/2026-11-25T10-16-21Z__enjoy-hr.md` (18/40).

**Staging was re-cloned from production** (DB, uploads, plugins, parent theme, core 7.1.5) at 10:24, so staging now has all 36 posts. Pre-sync dumps: `backups/prod-backup-pre-nav-20261125-1024.sql`, `backups/staging-backup-pre-sync-20261125-1024.sql`.

**Applied to BOTH staging and production** via `snippets/nav-restructure-20261125.php` (`wp eval-file`, idempotent):
- Main menu 159 cut from 65 items to 19: Destinations (Dalmatia › Split, Dubrovnik; Istria; Zagreb; National Parks › Plitvice, Krka, Mljet), Getting Around, When to Go, Plan Your Trip (Itineraries, Visas & Entry, Where to Stay, Money & Payments), Stay Safe, Food & Drink. JNews mega-menu meta removed from kept items (plain dropdowns, same tree on mobile). Pre-change menu CSVs on the server: `~/backups/prod-menu159-pre-nav-20261125.csv`, `~/backups/prod-menu160-pre-nav-20261125.csv`.
- Category fixes: ferry posts 3344/3345 moved from duplicate "Getting Around Croatia" (463, now empty, kept) to "Getting Around" (257); 3320 added to 257; 3344 and 3105 added to Split (225), 3105 also to Zagreb (245); four older safety posts added to "Stay Safe in Croatia" (392).
- Footer menu 160: dead `#` links (About, Advertise, Privacy & Policy) removed, Contact added. Re-add About/Privacy once the pages exist (drafts in `docs/drafts/`, with TODOs for Marko).
- Theme mods: `jnews_mobile_truncate = false`, `jnews_single_show_post_date = true`.
- Rank Math: category archives now `index` (empty ones stay noindex via `noindex_empty_taxonomies`), title pattern without "Archives". **This closes the noindex investigation.**
- Sitemap 404 fixed with `wp rewrite flush` (rules had gone stale).
- Homepage (398) demo tagline replaced in `_elementor_data`.

**Child theme 1.5.0, deployed to staging AND production, committed on branch `feature/design-review-followup` (not merged to `main`, not tagged):** reading-measure/contrast/badge CSS appended to `style.css`, `inc/empty-states.php` + `assets/css/empty-states.css`, and helpful empty states in `404.php`, `category.php`, `archive.php`, `search.php`. Server-side theme backups: `~/backups/staging-jnews-child-pre-review-20261125-1045.tgz`, `~/backups/prod-jnews-child-pre-review-20261125-1033.tgz`. Production verified by page fetch plus DOM measurements at 375px (no screenshots at phone width).

Still open from the review: homepage restructure (Elementor), About/Privacy pages, author bios, table of contents, related posts.

### ✅ Elementor Pro removed from use — staging AND production (2026-11-25)

The site runs without an Elementor Pro licence (Elementor free stays and is required). `elementor-pro` is **installed but inactive on both environments**. Audit found only three Pro pieces in use; each now has a child-theme replacement, deployed to both environments and committed on branch `feature/design-review-followup` (2026-11-25, not merged or tagged):

| Pro piece | Replacement |
|---|---|
| Form widget on Contact page (276) | `[enjoy_contact_form]` shortcode — `jnews-child/inc/contact-form.php` (emails `admin_email`, honeypot + per-IP throttle, stores nothing) |
| Popup 233 | `jnews-child/inc/newsletter-popup.php` + `assets/js/newsletter-popup.js`, wraps `[mc4wp_form]`. **Off unless** `wp option update enjoy_newsletter_popup 1` |
| Theme Builder 404 template (246) | JNews 404 via `jnews-child/404.php` (+ "Back Home" button) |

Also moved the homepage's five `.vc_custom_*` rules from Pro page Custom CSS into `style.css`, and bumped the child theme `Version` to 1.4.0 (cache-busts `style.css`).

**State on both environments:** `elementor-pro` inactive; popup 233 draft; Contact page 276 uses the shortcode widget (original data in postmeta `_elementor_data_bak_pre_pro_removal`). `enjoy_newsletter_popup` is `1` on staging and **unset on production** (popup stays off there, as Marko asked in August).

**Backups:** DB dumps `backups/staging-backup-pre-pro-removal-20261125-1050.sql` and `backups/prod-backup-pre-pro-removal-20261125-1111.sql` (SHA-256 `b8a2253b…d1b400a9`); server-side theme file copies `*.bak-pre-pro-removal-20261125-1053` (staging) and `*.bak-pre-pro-removal-20261125-1111` (production).

**Rollback:** `wp plugin activate elementor-pro`, restore `_elementor_data` on post 276 from the backup meta, `wp elementor flush-css`. The Pro plugin files and all Elementor library templates are untouched.

The contact form is confirmed end to end on staging (real send reached the inbox). On production the reject paths are verified; a real send is still to be confirmed by Marko. Production `functions.php` now equals the local working tree (v1.3.0 + Task 07 affiliate block + two `require_once` lines) — the deploy tripwire below still applies to any tagged rsync.

Staging lags production: WP 6.9.12 vs 7.1.5, Elementor 4.0.1 vs 4.2.4, JNews 12.0.5 vs 12.0.12. Contact Form 7 was rejected as the form replacement — its current release needs PHP 8.3, production runs 8.2.

### DNS / mail (2026-11-25)

DNS for enjoy.hr is on **Cloudflare** (not Hostinger). Site mail goes out through Hostinger's `hsendmail` as `wordpress@<host>` with no DKIM, and was landing in spam. SPF changed on 2026-11-25:

- `enjoy.hr` TXT and new `stagin1.enjoy.hr` TXT: `v=spf1 include:_spf.google.com include:_spf.mail.hostinger.com ip4:92.112.187.42 ~all`
- Previous `enjoy.hr` value (rollback): `v=spf1 ip4:178.218.172.132 include:smtp-cluster.plusvps.com a mx include:mojsite.com include:_spf.google.com ~all` — the mojsite/plusVPS entries (old host) were dropped.

**The SPF change does not fix spam placement.** Headers read 2026-11-25 show Hostinger rewrites the envelope sender to `noreply@srv1619.main-hosting.eu` and relays via MailChannels with no DKIM, so SPF passes for Hostinger's domain only and nothing aligns with `enjoy.hr`. Setting PHPMailer `Sender` (`-f`) was tried on staging and is ignored by `hsendmail` — do not retry it. Gmail placement is therefore inconsistent (inbox / Promotions / spam). Real fixes: a Gmail filter on subject "Contact form:" (never spam), or authenticated SMTP with DKIM for enjoy.hr (Google Workspace DKIM is not yet enabled: no `google._domainkey.enjoy.hr`).

### ⏸ TEMPORARILY DISABLED — frontpage email popup (2026-08-26)

Elementor Pro popup **ID 233 "Subscription Popup"** (condition `include/singular/front_page`) is set to **draft on PRODUCTION only** at Marko's request. Staging still has it published. Its `_elementor_conditions` meta is intact, so re-enabling is a status flip + cache regen.

Re-enable:
```bash
ssh enjoyhr
cd domains/enjoy.hr/public_html
wp post update 233 --post_status=publish
wp eval "\ElementorPro\Modules\ThemeBuilder\Module::instance()->get_conditions_manager()->get_cache()->regenerate();"
wp cache flush; rm -rf wp-content/cache/supercache/enjoy.hr/*
```

**Gotcha discovered:** deleting the `elementor_pro_theme_builder_conditions` option does NOT trigger a rebuild — `Conditions_Cache::refresh()` just returns `[]`, which silently disables *every* Theme Builder template (it took out the 404 template 246 too). Always follow a cache delete with the `regenerate()` eval above. Full log: `backups/popup-233-state-pre-disable-20260826.txt`.

### ⏸ HIDDEN — Live Webcams menu section (2026-09-12)

The **Live Webcams** category tree (term 287 + 18 descendants, IDs 287–305, all 0 posts) was removed from **Main Navigation (menu 159)** on **both staging and production** at Marko's request — no webcam content exists yet. Only the 19 menu items were deleted (db_ids 1935, 1937–1956); the **categories themselves were kept deliberately** so the structure is ready when content arrives. Their archives already serve `noindex` (Rank Math empty-archive default) and are absent from the sitemap.

To restore: re-add the menu items under menu 159 (top-level item sat between "Inspiration" and "Blog & News"). The full pre-removal menu structure — titles, parents, positions — is in `backups/webcams-state-pre-removal-{prod,staging}-20260912-0830.txt`. New menu item IDs will differ from the originals.

### Active theme per environment

| Environment | Active theme | Accessibility | Ad rotation | Notes |
|---|---|---|---|---|
| Local (enjoyhr.local) | jnews-child | ✅ | n/a | Symlinked from repo |
| Staging (stagin1.enjoy.hr) | jnews-child | ✅ | ✅ Advanced Ads 2.0.21 live | `functions.php` = branch `feature/design-review-followup` (v1.3.0 + Task 07 affiliate block + three `inc/` requires) |
| Production (enjoy.hr) | **jnews-child** | ✅ | ✅ Advanced Ads 2.0.21 live | `functions.php` = branch `feature/design-review-followup` (v1.3.0 + Task 07 affiliate block + three `inc/` requires) |

### What the jnews-child theme does

**`functions.php`** — three committed (v1.3.0) things:
1. Enqueues the JNews parent stylesheet
2. Removes JNews's duplicate OG/Twitter/JSON-LD meta output via `remove_action('wp_head', ...)` on the `wp` hook at priority 20 — fixes `og:description` being poisoned with raw "edit post" admin markup
3. Hooks `the_ad_placement('below-header')` into JNews's `do_action('jnews_header_bottom_ads')` action — injects the Advanced Ads Manual Placement into the JNews header-bottom wrapper. Avoids needing the paid "Custom Position" add-on. Works because JNews skips its own callback on that action when `jnews_ads_header_bottom_enable = false` in the Customizer.

Plus the Task 07 affiliate-disclosure block (see tripwire above). Live on both environments, committed on branch `feature/design-review-followup`, absent from `main` and the v1.3.0 tag. The keep-or-remove decision (Task 07 reckoning) is still open.

**`style.css`** — two groups of overrides:
1. Gradient overlay overrides (Condé Nast aesthetic — photos breathe, minimal dark fade at bottom only)
2. Accessibility fixes: touch target size on category badges (min-height 44px) + link underlines in article body content

**Template overrides** (all add `role="main"` to `.jeg_content`):
- `template-builder.php` — homepage (Landing Page template)
- `index.php`, `category.php`, `archive.php`, `search.php`, `404.php`
- `single-custom-post-template.php`
- `fragment/post/single-post-1.php` — single post pages (template style 1)

### Git tags

| Tag | Description |
|---|---|
| v1.0.0 | First production deploy: JNews child theme + OG meta fix |
| v1.1.0 | Visual milestone: Condé Nast gradient overrides |
| v1.2.0 | Accessibility milestone: touch targets, link underlines, `<main>` landmark, Instagram widget removed |
| v1.3.0 | Advanced Ads rotation: child-theme hook injecting `the_ad_placement('below-header')` into JNews's `jnews_header_bottom_ads` action. Commit `4e2ede2`. Pushed to origin 2026-05-22. |

### Open tasks

**Task 07 — affiliate disclosure reckoning** (`tasks/07-affiliate-reckoning-followup.md`). The affiliate-disclosure code was implemented in a prior session, accidentally rsynced to both staging and production, and never committed to git. Currently LIVE-but-dormant on both environments (0 posts flagged). The reckoning is its own session: review the leaked code, decide commit-and-keep vs. deliberately remove, then resolve the git divergence on both envs with its own backup. Do not start until the deferred work is consciously scheduled — see the tripwire above for the deploy-side risk.

**Affiliate: Booking.com runs through CJ Affiliate, not Booking Partners** (established 2026-09-09). Do not reach for Booking's own `aid=` link format — it does not apply. Key facts, with full detail in `docs/affiliate-strategy.md` §7.1:

- CJ promotional property "Enjoy Croatia", **PID `101756772`** — public, appears in every link, safe to commit.
- Advertiser: **Booking.com North America**, ID `7864295`. 4% on accommodation, USD, serviceable area Canada + United States. Site targets US readers, so this is the right programme.
- **In-session conversion only — no cookie window.** Links must sit at booking intent, not in article intros. Do not model revenue on 30-day-cookie benchmarks.
- Sub-tracking uses CJ's `sid` parameter, not Booking's `label`.
- **Blocked on:** the CJ ad ID (`{AID}`) and redirect domain. Booking publishes no creatives in CJ's link catalogue, so these come only from the CJ DEEP LINK bookmarklet, which Marko must drag to his bookmarks bar (CJ → Account → Link Tools). One generated link unblocks all six Booking URLs in `tasks/07-affiliate-link-plan.md`.

**Also pending: noindex investigation** — User asked to remove noindex tags on 2026-05-19. Quick check found `blog_public = 1` (WordPress itself is NOT blocking search engines). Root cause not yet identified — Rank Math or another plugin may be adding noindex to specific page types. Start new session by curling the live site and inspecting `<meta name="robots">` tags on homepage, single post, and category pages.

### Closed tasks

| Task | Description |
|---|---|
| Task 01 | Site audit — findings in `docs/audit-findings.md` |
| Task 02 | Production OG fix deploy — jnews-child activated on production |
| Task 03 | Plugin updates (Mailchimp, Rank Math, WP Super Cache) |
| Task 04 | Gradient overrides — Condé Nast aesthetic |
| Task 05 | Plugin removal — videojs-html5-player + jnews-social-login deleted |
| Task 06 | Accessibility — Lighthouse fixes (touch targets, link underlines, landmark, Instagram widget removed) |
| Task 08 | Advanced Ads rotation deploy (2026-05-22) — plugin installed on staging + prod, 2 ad groups (Below Header Rotation / Right Sidebar Rotation, random), Manual Placement `below-header` (slug, NOT `below_header`), child-theme hook `enjoy_inject_below_header_ad` shipped as v1.3.0. Production deployed via Option B (in-place patch) to preserve uncommitted Task 07 affiliate code. |

### Key facts established by previous sessions

- Security incident (backup exposure) fully remediated
- Elementor (free): required — homepage and posts are built with it. Elementor Pro: only the 404 template, Contact form and popup depended on it, now replaced and Pro deactivated (2026-11-25; the earlier claim that Theme Builder powers header/footer/single/archive/search was wrong)
- Plugin "unused" assessment methodology corrected — always check postmeta and `elementor_library` CPT before declaring a page builder unused
- OG/Twitter meta fix live on all three environments as of 2026-04-26
- Accessibility fix 1 (badge color contrast) intentionally skipped — white-on-coral is deliberate design choice, WCAG AA tradeoff accepted

### Known service accounts and management plugins

**`manage-system-user` (user ID 5, administrator)** — legitimate service account created automatically by the **Elementor Manage plugin** (plugin slug: `manage`, version 1.0.4, author: Elementor.com). This plugin connects the site to Elementor's remote multi-site management dashboard. The account has no email by design; meta keys `_manage_system_user = yes` are the plugin's own markers. Investigated and confirmed legitimate 2026-04-27. Do not delete this user or the plugin unless Marko decides to disconnect from Elementor Manage.

### Known production DB state

- Table prefix: `ec_` (non-standard)
- `theme_mods_jnews-child` row exists — copied from `theme_mods_jnews` during Task 02 deploy (2026-04-26)

### Verified sidebar configuration (2026-05-22)

Both environments use the same JNews sidebar wiring (confirmed via `wp option get theme_mods_jnews-child`):

| theme_mod | Prod | Staging |
|---|---|---|
| `jnews_single_sidebar` | `home-3` | `home-3` |
| `jnews_index_sidebar` | `home` | `home` |
| `jnews_ads_header_bottom_enable` | `false` | `false` |

Advanced Ads widget (`advads_ad_widget` → `item_id: group_337`, Right Sidebar Rotation) is wired in **both** `home-3` AND `default-sidebar` on each environment, so the sidebar ad renders regardless of which sidebar the active template pulls. Old static `jnews_module_element_ads` widgets have been removed from `home` and `default-sidebar` on both envs but **remain present in the `contact` sidebar** (Contact page only — out of scope so far).

### Backups

| What | Where | Notes |
|---|---|---|
| Pre-v1.3.0 prod DB | `~/claude-projects/enjoy-hr/backups/prod-backup-pre-advads-20260522-0111.sql` | 14M, SHA-256 `a5a5554a9a9b635a6e07e56321857694142fb8cf378780e46977b2fb95440013` |
| Pre-v1.3.0 prod `functions.php` | `domains/enjoy.hr/public_html/wp-content/themes/jnews-child/functions.php.bak-pre-advads-20260522-0151` (on server) | 13,714 bytes, the version with affiliate code but no ad hook |
| Pre-webcam-menu-removal prod DB | `~/claude-projects/enjoy-hr/backups/prod-backup-pre-webcam-menu-20260912-0831.sql` | 18M, SHA-256 `e979abd109873daaf0993e0ca7e30d90d97bd9c64e57932cfc3124bcc0ab8bef` |
| Pre-webcam-menu-removal menu + term state | `~/claude-projects/enjoy-hr/backups/webcams-state-pre-removal-{prod,staging}-20260912-0830.txt` | Full menu 159 CSV + the 19 webcam terms, per environment |

Convention: pre-deploy file backups on the server are named `<filename>.bak-pre-<task>-$(date +%Y%m%d-%H%M)`. DB backups go to the local `backups/` dir (gitignored).

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
