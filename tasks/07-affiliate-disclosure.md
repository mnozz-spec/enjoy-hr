# Task 07 — Affiliate Disclosure

## Goal

Add legally required affiliate disclosure to the site and insert affiliate links into 2–3 existing articles.

## Background

Croatian and EU law (and Google's guidelines) require that affiliate links be disclosed clearly to readers. The disclosure must be:
- Visible before or near the affiliated content (not buried in a footer)
- Clearly written in the page language (Croatian for Croatian content)
- Accompanied by `rel="sponsored"` or `rel="nofollow"` on the links themselves

## Questions for Marko before starting

1. **Which affiliate programs?** (e.g. booking.com, GetYourGuide, Viator, Amazon, local Croatian services)
2. **Which 2–3 articles** should get affiliate links first?
3. **Disclosure language** — draft below, confirm or revise:
   - Croatian: *"Ovaj članak sadrži partnerske veze. Ako kupite putem naše veze, možemo zaraditi proviziju bez dodatnih troškova za vas."*
   - English (for international audience): *"This article contains affiliate links. If you purchase through our link, we may earn a commission at no extra cost to you."*
4. **Placement** — top of article, inline near the link, or both?
5. **Disclosure page** — do you want a standalone `/affiliate-disclosure/` page linked from the footer?

## Proposed implementation

### Option A — PHP hook in functions.php (automatic, per-post)

Add a function to `jnews-child/functions.php` that hooks into `the_content` and prepends a disclosure notice to any post that contains affiliate links (detected via a custom field or post tag).

Pros: automatic, consistent, easy to enable/disable per post  
Cons: requires tagging posts or checking content for affiliate URLs

### Option B — Elementor widget / manual insertion

Insert a reusable Elementor block (global widget) at the top of each article that has affiliate links. Marko inserts it manually when writing.

Pros: Marko controls it per post; no code needed  
Cons: manual, easy to forget

### Recommended: Option A with a `has_affiliate_links` custom field

- Add a checkbox custom field (via Rank Math's custom fields or a simple ACF-lite approach) called `has_affiliate_links`
- `functions.php` hook prepends the disclosure paragraph when the field is true
- Links get `rel="sponsored noopener"` — add a note in the editorial guide

### Standalone disclosure page

Create a `/affiliate-disclosure/` page in WordPress explaining:
- What affiliate links are
- Which programs enjoy.hr participates in
- How to identify them (disclosure notice at top of articles)

Link it from the footer (Elementor Pro footer template).

## Files to modify

| File | Change |
|---|---|
| `jnews-child/functions.php` | Add `enjoy_affiliate_disclosure()` hooked to `the_content` |
| `jnews-child/style.css` | Style the disclosure notice box |
| WordPress admin | Create `/affiliate-disclosure/` page; add footer link |
| 2–3 existing posts | Insert affiliate links + mark post with custom field |

## Deployment

Same as all tasks: local → staging → Marko review → production + Cloudflare purge.

## Definition of done

- [ ] Marko answers the questions above
- [ ] Disclosure notice appears automatically on tagged posts
- [ ] Affiliate links have `rel="sponsored noopener"`
- [ ] `/affiliate-disclosure/` page exists and is linked from footer
- [ ] 2–3 articles updated with affiliate links
- [ ] Deployed to production, Cloudflare purged
