# Task 07 — Affiliate disclosure reckoning (follow-up)

**Status:** Deferred. Do NOT start incidentally — schedule its own session.

## Why this exists

The affiliate-disclosure code (metabox + tracking-label validation + `the_content` notice filter — see `enjoy_affiliate_*` in `jnews-child/functions.php`) was implemented in a prior session, deployed to staging via working-tree rsync, and ALSO ended up on production (presumed via a later rsync of the dirty working tree to production). It was **never committed to git** and never formally reviewed.

As of 2026-05-22 it is **live but dormant** on both environments:

| Environment | Code present? | Posts with `_enjoy_has_affiliate_links` postmeta |
|---|---|---|
| Production | ✅ in `functions.php` (uncommitted) | 0 |
| Staging | ✅ in `functions.php` (uncommitted) | (presumed 0 — verify in session) |
| Local working tree | ✅ + `style.css` + `docs/affiliate-strategy.md` + `tasks/07-affiliate-link-plan.md` (all uncommitted) | n/a |

No reader has seen a disclosure notice. The metabox is registered (it renders in the post editor sidebar) but no editor has used it.

## What this task needs to do

1. **Independent code review** of the affiliate block in `jnews-child/functions.php` and the related `.affiliate-disclosure` CSS in `jnews-child/style.css`. Treat it like third-party code: nonce/capability checks, sanitisation, escaping, idempotency, FTC 16 CFR 255 compliance text. Also check `docs/affiliate-strategy.md` against the implementation.
2. **Decide explicitly:** commit-and-keep, OR deliberately remove.
3. If keep: stage and commit (`functions.php` affiliate block + `style.css` block + `docs/affiliate-strategy.md` + `tasks/07-affiliate-link-plan.md` + `docs/audit-findings.md` Privacy-Policy note), tag a release, then deploy to both staging and production via the standard rsync flow. After deploy, git divergence resolves itself — production's live `functions.php` will equal the new tag exactly.
4. If remove: take fresh DB backups of both envs, surgically delete the affiliate block from the live `functions.php` on each env (mirror of the v1.3.0 Option-B in-place patch pattern but inverted), then discard the working-tree changes locally. Verify post-removal that no PHP fatal triggers and no orphaned references in `style.css` etc.
5. Either way: clear the ⚠️ DEPLOY TRIPWIRE callout in `CLAUDE.md` once divergence is resolved.

## Pre-flight checks before starting

- Take fresh DB backups of BOTH staging and production (using `mysqldump` per the deployment-doc workaround for the broken `wp db export`).
- Re-verify the postmeta count on both envs: `SELECT COUNT(*) FROM ec_postmeta WHERE meta_key = '_enjoy_has_affiliate_links'`. If any rows exist now that didn't on 2026-05-22, the code has been used in the interim — that changes the calculus.
- Confirm both envs' `functions.php` md5s match the local working-tree `functions.php` (or note where they diverge).

## Why "deferred" not "delete now"

Code-review + git surgery + dual-environment patching + decision on a compliance feature is a meaningful workload. Bundling it into a different deploy is exactly the scope creep that breaks deploys. Keep it isolated.

## Related artefacts in the repo

- `jnews-child/functions.php` (working-tree, uncommitted) — affiliate block lines (~37–294 currently)
- `jnews-child/style.css` (working-tree, uncommitted) — `.affiliate-disclosure` CSS (~32 lines)
- `docs/affiliate-strategy.md` (untracked) — design doc
- `tasks/07-affiliate-link-plan.md` (untracked) — original task brief
- `docs/audit-findings.md` (working-tree, uncommitted) — 2-line Privacy Policy note added during Task 07 scoping (this one is unrelated to the affiliate code; consider committing it on its own if the affiliate work itself is dropped)
- Pre-v1.3.0 server backup: `domains/enjoy.hr/public_html/wp-content/themes/jnews-child/functions.php.bak-pre-advads-20260522-0151` (contains the affiliate code but no ad hook — useful reference for the exact pre-tonight state)
