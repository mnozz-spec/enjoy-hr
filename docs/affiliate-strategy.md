# Affiliate Strategy — enjoy.hr

Reference document for affiliate monetization. Captures decisions, compliance approach, link patterns, and operational rules. Read this before adding or modifying any affiliate link on the site.

Last updated: 2026-09-09
Owner: Marko Nozica (Aretis d.o.o.)

---

## 1. Scope and audience

- **Site language:** English only. All affiliate-bearing content, disclosures, and the standalone disclosure page are English.
- **Target audience:** English-speaking inbound tourists planning a trip to Croatia.
- **Company:** Aretis d.o.o., registered in Croatia / EU.
- **Hosting:** US servers (Hostinger), Cloudflare in front.
- **No Croatian-language content exists today.** Croatian disclosure wording is held in reserve in §9 for a future bilingual rollout — do not deploy it now.

- **Primary market: United States.** Confirmed 2026-09-09. The site is English-only and aimed first at US inbound travellers. This is what makes the Booking.com *North America* programme (§7.1) the correct one to be on.

Because readers are primarily US/UK/EU consumers and the company is EU-based, the program is built to satisfy the strictest of the three regimes (US FTC disclosure rules, UK ASA/CMA, and EU consumer-protection / UCPD).

---

## 2. Programs in use

| Program | Used for | Notes |
|---|---|---|
| **Booking.com North America** — reached via **CJ Affiliate**, not Booking's own partner programme | Accommodation (hotels, apartments, villas) | Primary revenue driver. 4% commission, **in-session conversion only** — there is no cookie window. See §7.1. |
| GetYourGuide Partner | Tours, day trips, activities, skip-the-line tickets | Strong fit for sightseeing articles |
| Viator (Tripadvisor) | Tours and experiences | Secondary to GetYourGuide; useful where GYG inventory is thin |

No other affiliate networks are in use. Do not add Amazon Associates, Skyscanner, or any other network without updating this document first.

---

## 3. Compliance framework

Three legal/policy regimes apply. The site complies with all three by adopting the strictest combined requirements.

### 3.1 US — FTC 16 CFR Part 255 ("Endorsement Guides")
- Material connection must be disclosed "clearly and conspicuously" near the relevant claim — not in a footer, not below the link.
- Disclosure must be in the same language as the content.
- Disclosure must be unavoidable on mobile (no behind-a-link, no expanding section).

### 3.2 UK — ASA/CMA guidance
- "Ad" / "affiliate" labelling expected up-front.
- "May earn a commission" language is acceptable when accurate.

### 3.3 EU — UCPD (Directive 2005/29/EC) + Croatian Consumer Protection Act
- Commercial communication must be identifiable as such.
- Hidden advertising is a prohibited practice.

### 3.4 Google Search Essentials (technical compliance)
- Every affiliate link must carry `rel="sponsored noopener"`. **Hard rule, no exceptions.**
- `nofollow` is acceptable as a fallback if `sponsored` is unavailable in a context, but `sponsored` is preferred and is what we use.
- `noopener` is added for security (prevents `window.opener` access on `target="_blank"` links).

---

## 4. Disclosure approach

Two layers, both deployed:

1. **Per-article disclosure notice** — rendered automatically at the top of any post flagged as containing affiliate links. Implemented as a PHP filter on `the_content` in `jnews-child/functions.php`, gated by a custom field (see §6). The notice appears above the article body so it is unmissable on mobile.

2. **Standalone disclosure page** — `/affiliate-disclosure/` linked from the site footer. Explains who we are, which programs we use, editorial independence, how to identify affiliate articles, and how to contact us with concerns.

The per-article notice carries the legal weight (proximity requirement). The standalone page provides depth and is what we point regulators / Google reviewers / readers to if questioned.

---

## 5. Disclosure wording (English — production)

**Per-article notice (top of post):**

> *This article contains affiliate links. If you book or buy through them, Enjoy Croatia may earn a small commission at no extra cost to you. We only recommend places and experiences we would suggest to a friend visiting Croatia.*

Rationale:
- "Affiliate links" — the FTC-recognised plain term.
- "Book or buy" — covers both Booking.com (accommodation) and GetYourGuide/Viator (experiences).
- "Enjoy Croatia may earn a small commission" — names the site, makes the material connection explicit.
- "At no extra cost to you" — accurate for all three programs and reassures readers.
- Final sentence — editorial-independence statement; supports trust and satisfies "honest review" expectations under UK ASA guidance.

**Standalone page wording** — drafted separately in Step 1 of the task; not duplicated here.

---

## 6. Implementation — custom field and PHP hook

**Custom field:** `has_affiliate_links` (boolean / checkbox).

- Set true on any post that contains one or more affiliate links.
- Used by the `the_content` filter to decide whether to prepend the disclosure notice.
- Field implementation method to be decided in Step 2 of the task (native WP custom fields, Rank Math, or ACF). Default preference: native WP custom fields if Gutenberg/Elementor handle them cleanly, to avoid adding a plugin.

**Default behavior — opt-in, not opt-out.** If the custom field is missing, empty, or any value other than truthy, the disclosure notice is NOT rendered. This is intentional: most posts on the site (e.g. "Croatia Tap Water", "Snakes in Croatia", general informational pieces) do not contain affiliate links, and an affiliate notice on those would be misleading. The notice appears only when an editor has explicitly flagged the post by setting `has_affiliate_links = true`. The hook treats the absence of the field as an explicit "no" — never as "maybe" or "fall back to checking content for affiliate URLs".

**Hook:** `enjoy_affiliate_disclosure()` in `jnews-child/functions.php`, registered on `the_content` at priority 20 (after JNews's own content filters but before output).

The hook MUST:
- Run only on single post views (`is_single()`).
- Run only when `has_affiliate_links` is truthy.
- Prepend a styled `<div class="affiliate-disclosure">…</div>` containing the §5 notice.
- Be idempotent — never prepend twice if the filter fires multiple times.

**CSS:** Styled in `jnews-child/style.css`. Subtle box (light background, thin left border, smaller italic text). Visible but does not compete with article hero / headline.

---

## 7. Link patterns

All affiliate URLs follow these templates. Use search-result URLs, not deep links to a specific property — search URLs survive longer (properties get delisted, renamed, change IDs) and let the reader see options rather than a single locked-in choice.

### 7.1 Booking.com (via CJ Affiliate)

enjoy.hr reaches Booking.com through the **CJ Affiliate** network, not through Booking's own partner programme. Our links are therefore CJ redirect URLs. **They are not `booking.com/...?aid=` URLs.** Any guide, blog post, or earlier revision of this document describing an `aid` parameter does not apply to us — ignore it.

```
https://www.{CJ_REDIRECT_DOMAIN}/click-101756772-{AID}?url={URL_ENCODED_BOOKING_URL}&sid=enjoyhr-{TRACKING_LABEL}
```

- `101756772` — the **promotional property ID (PID)** of the "Enjoy Croatia" property in CJ. Constant on every link. Not a secret; see §10.1.
- `{AID}` — CJ's ad/link ID for the Booking.com deep-link creative. Produced by the link generator, never hand-typed.
- `{CJ_REDIRECT_DOMAIN}` — CJ rotates several tracking domains (`anrdoezrs.net`, `dpbolvw.net`, `tkqlhce.com`, `jdoqocy.com`). Use whichever the generator emits and do not swap one for another.
- `url` — the **URL-encoded** Booking.com destination, e.g. `https%3A%2F%2Fwww.booking.com%2Fsearchresults.html%3Fss%3DDubrovnik`. Encoding is mandatory: an unencoded `&` inside this value silently truncates the destination.
- `sid` — CJ's sub-campaign parameter, and the only place `enjoyhr-{TRACKING_LABEL}` goes. **It replaces Booking's `label` parameter, which we no longer use.** CJ supports exactly one `sid` per link, which suits our one-label-per-article convention (§7.4).

**How to generate a link.** CJ dashboard → *Account* → *Link Tools* → drag the **CJ DEEP LINK** bookmarklet onto the browser bookmarks bar. Then open the Booking.com page you want and click the bookmarklet. Booking.com North America publishes **no creative links** in CJ's Links & Products catalogue, so the bookmarklet is the only route — there is nothing to copy out of a link search.

#### Programme facts (verified in the CJ dashboard, 2026-09-09)

| Field | Value |
|---|---|
| Advertiser | Booking.com North America (ID 7864295) |
| Term | Connected Trip Default |
| Accommodation commission | 4.00% |
| Attractions, airport taxis | 4.00% |
| Car rental | 3.80% pay-local, 6.00% pay-now |
| Flights | 2.00 USD per order |
| Currency | USD |
| Referral period | 1 day |
| Serviceable area | Canada, United States |

#### ⚠ In-session conversion only — no cookie window

Booking.com does not use cookie tracking on this programme. Commission is earned **only when the reader completes the booking in the same browser session as the click**. A reader who clicks today and books next week earns nothing. Two consequences bind our editorial choices:

1. **Place links at the point of booking intent**, never in the opening paragraphs. A link beside a "where to stay" recommendation can convert; a link in the introduction almost never will. This narrows, and partly overrides, the free-placement latitude in §8.
2. **Model revenue on same-session conversion only.** Do not forecast against typical 30-day-cookie affiliate benchmarks — they overstate earnings here by a wide margin.

`target="_blank"` (§8) helps rather than hurts: the reader's Booking session stays alive in its own tab while the article stays open behind it.

#### Programme policies that constrain editorial

Full terms live in CJ under *Partners → My Advertisers → Program Terms*. The ones that touch our work:

- No iframes embedding Booking.com content.
- No listing of voucher or discount codes. Booking issues none, and publishing them reverses commissions and can remove us from the programme.
- No bidding on "Booking.com" or variant brand terms in paid search, and no direct linking to Booking from ads.
- No domain names incorporating "Booking" or confusable variants.
- Sub-affiliate or sub-network arrangements need prior approval from both Booking.com and CJ.
- Social-media-only publishers are not accepted. Our site presence is what qualifies us.

**Audience fit.** Confirmed 2026-09-09 that enjoy.hr targets primarily US readers, matching this programme's Canada/United States serviceable area. If the audience mix later shifts materially toward Europe, re-check whether a Booking.com EMEA programme on CJ pays better and revise this section before rewriting links.

### 7.2 GetYourGuide

```
https://www.getyourguide.com/s/?q={QUERY}&partner_id={PARTNER_ID}&cmp=enjoyhr-{TRACKING_LABEL}
```

- `partner_id` — stored in password manager.
- `cmp` — campaign / sub-id parameter used for tracking. Same `enjoyhr-{TRACKING_LABEL}` pattern.
- `q` — search query (city, activity, or both).

### 7.3 Viator

```
https://www.viator.com/searchResults/all?text={QUERY}&pid={PARTNER_ID}&mcid={CAMPAIGN_ID}&medium=link&campaign=enjoyhr-{TRACKING_LABEL}
```

- `pid` — Viator partner ID, stored in password manager.
- `mcid` — Viator's media channel ID, stored in password manager.
- `campaign` — same `enjoyhr-{TRACKING_LABEL}` pattern.

### 7.4 Tracking label convention (decoupled from WordPress slug)

`{TRACKING_LABEL}` is a **short editorial identifier** chosen per article. It is intentionally **not** the WordPress URL slug, because the site's SEO-optimised slugs run 60–90 characters and exceed Booking.com's 64-char `label` cap (and similar limits on GetYourGuide / Viator).

**Rules:**

- ≤ 20 characters total
- Lowercase, kebab-case, alphanumeric + single hyphens
- Pattern: `{destination}-{theme}` — destination first so labels sort cleanly in partner dashboards, theme tag second for cohort grouping
- Mnemonic, not SEO-shaped
- **Stable**: does NOT change when the WordPress slug is tuned for SEO. Once published it is treated as a permanent identifier so historical reporting stays comparable

**Storage:**

- Stored on the post itself as a custom field (`_enjoy_affiliate_tracking_label`), set via the same metabox as the `has_affiliate_links` flag
- Validation enforced server-side in `jnews-child/functions.php` — invalid input rejected with an admin notice, existing value preserved
- Also recorded in §11 of this document as the editorial source of truth

The metabox UI greys out the tracking-label field when the `has_affiliate_links` checkbox is off — visual signal that the label only matters once an article is flagged as affiliate-bearing.

---

## 8. Anchor text and editorial integration

Two anchor-text modes, mixed within an article:

1. **Inline natural phrasing.** The link sits inside a sentence written for the reader, not for the affiliate program.
   - *"a few of the [boutique hotels inside the old walls](https://...) are walkable to everything"*
2. **Explicit recommendation block.** A short, clearly labelled callout: *"Where to stay:"*, *"Book this tour:"*, *"Tickets:"*.
   - Easier for skim-readers, higher CTR.

Both modes are acceptable. Avoid clickbait anchor text ("CLICK HERE", "BEST DEAL"). Avoid stuffing every paragraph with a link.

**Link density scales with article length.** The 2–4 figure is a midpoint, not a quota. Calibrate to the piece:

| Article length | Target affiliate links |
|---|---|
| Short (under ~800 words) | 1–2 |
| Medium (~800–1,500 words) | 2–4 |
| Long round-up / guide (1,500+ words) | 4–8, distributed across sections |

A short article with four affiliate links feels stuffed and erodes reader trust; a 3,000-word round-up with only two feels like a missed opportunity. When in doubt, fewer is safer — each link should earn its place in the prose.

Every link, regardless of mode:
- `rel="sponsored noopener"` — required.
- `target="_blank"` — recommended (readers keep the article open while browsing the partner site).
- Anchor text accurately describes the destination — no surprise redirects.

---

## 9. Reserved — Croatian disclosure wording (NOT for current deployment)

Held for future bilingual rollout. Do not add this to the site until a Croatian-language section exists.

> *Ovaj članak sadrži partnerske veze. Ako rezervirate ili kupite putem njih, Enjoy Croatia može zaraditi malu proviziju bez dodatnih troškova za vas. Preporučujemo samo mjesta i doživljaje koje bismo predložili prijatelju u posjetu Hrvatskoj.*

Notes:
- "rezervirate ili kupite" mirrors the English "book or buy".
- Croatian forms used throughout — *tisuću* not *hiljadu* etc. Match CLAUDE.md language rules.
- When deployed, the PHP hook must select wording by `get_locale()` or by post language meta — not hard-code.

---

## 10. Credentials and secrets

Earlier revisions of this document treated every affiliate identifier as a secret. That was wrong for the ones that have to appear inside a public URL, and the confusion actively blocked work — you cannot write a usable link template out of tokens you are forbidden to record. The distinction below replaces the old blanket rule.

### 10.1 Public identifiers — recorded here deliberately

| Identifier | Value | Why it is not a secret |
|---|---|---|
| CJ promotional property ID (PID) | `101756772` | Appears in the `href` of every affiliate link on the site; readable in any page's source |
| Booking.com CJ advertiser ID | `7864295` | Public programme identifier, listed in CJ's advertiser directory |
| CJ ad/link ID (AID) | per-link | Appears in the rendered link alongside the PID |

Anything that must survive into a rendered public URL falls in this category by definition. Record it, template with it, commit it.

### 10.2 Genuine secrets — never committed

- The CJ account login, and any CJ **Personal Access Token / API key**.
- Account recovery details for CJ and Booking.com.
- GetYourGuide `partner_id` and Viator `pid` / `mcid` — these are almost certainly URL-embedded too, and so probably belong in §10.1. Treat them as secret until confirmed against a real generated link, then move them.

These are **stored only in Marko's password manager**, never committed to this repo, and never written into any file under `~/claude-projects/enjoy-hr/`.

If a genuine credential is ever accidentally committed, treat it as exposed: rotate it in the relevant dashboard immediately, then `git filter-repo` to scrub history.

---

## 11. Articles in scope (initial rollout)

| # | Article | Post ID | WordPress slug | Tracking label | Programs to use |
|---|---|---|---|---|---|
| 1 | Dubrovnik for Two: A Romantic Guide to Croatia's Adriatic Gem | 2650 | `dubrovnik-for-two-a-romantic-guide-to-croatias-adriatic-gem` | `dubrovnik-couples` | Booking.com (where to stay), GetYourGuide (Old Town walking tour, Game of Thrones tour, day trips), Viator (fallback) |
| 2 | Most Romantic Istrian Towns for an Unforgettable Weekend Getaway in Istria, Croatia | 2753 | `most-romantic-istrian-towns-for-an-unforgettable-weekend-getaway-in-istria-croatia` | `istria-couples` | Booking.com (Rovinj, Motovun, Grožnjan stays), GetYourGuide (truffle hunting, wine tastings), Viator (fallback) |
| 3 | Quiet Islands in Croatia for Couples — Romantic, Crowd-Free Escapes on the Dalmatian Coast | 2657 | `quiet-islands-in-croatia-for-couples-romantic-crowd-free-escapes-on-the-dalmatian-coast` | `islands-couples` | Booking.com (island accommodation), GetYourGuide (ferry-linked day trips, kayaking) |

Tracking-label convention: `{destination}-{theme}`. The three initial articles share the `couples` theme tag. Future extensions follow the same shape — e.g. `dubrovnik-families`, `dubrovnik-foodie`, `istria-wine`. See §7.4 for the full rule.

Article-by-article link placement and anchor text drafted in Step 6 of the task — reviewed before any link is added.

---

## 12. Operational rules (summary)

- ✅ Every affiliate link carries `rel="sponsored noopener"` and `target="_blank"`.
- ✅ Every affiliated article has `has_affiliate_links = true` so the disclosure renders.
- ✅ Articles without the flag get NO disclosure notice — opt-in, not opt-out.
- ✅ Disclosure renders above the article body, never below.
- ✅ Link density scales with article length (see §8 table) — fewer when in doubt.
- ✅ Links use the search-result URL pattern with `enjoyhr-{TRACKING_LABEL}` in the `sid` (Booking via CJ) / `cmp` (GetYourGuide) / `campaign` (Viator) parameter. Tracking label is the editorial identifier from §7.4, NOT the WordPress slug.
- ✅ Booking.com links sit at the point of booking intent, because that programme converts in-session only (§7.1).
- ✅ Real *credentials* (logins, API tokens) stay in the password manager. URL-embedded identifiers such as the CJ property ID are public and live in §10.1.
- ✅ New affiliate program = update this document first.
- ❌ No affiliate links in headlines, hero captions, or featured-image alt text.
- ❌ No cloaked URLs (e.g. redirect through `/go/booking-dubrovnik`) — keep destinations transparent.
- ❌ No disclosure in a collapsed "read more" section or behind a footer link only.

---

## 13. How to add affiliate links to a future article

A 5-step checklist for editorial work. Cross-refer to the section noted in each step for the underlying detail and rationale.

Before you start, decide the tracking label following §7.4 — pattern `{destination}-{theme}`, ≤20 chars, kebab-case. Reuse an existing label if the article extends an existing cohort (e.g. another couples-focused Dubrovnik piece reuses `dubrovnik-couples`); coin a new one for a new cohort (e.g. `dubrovnik-families`).

1. **Identify natural link opportunities.** Scan the draft for hotels mentioned, tours suggested, experiences recommended. Match the density target in §8 (1–2 short / 2–4 medium / 4–8 long round-up). Skip anything that would feel forced — every link should earn its place in the prose.

2. **Generate affiliate URLs** for each opportunity using the partner link builders, with your chosen tracking label substituted into the `enjoyhr-{TRACKING_LABEL}` parameter (§7.1–§7.3):
   - Booking.com → **CJ Affiliate** → *Account* → *Link Tools* → the **CJ DEEP LINK** bookmarklet, clicked while on the target Booking.com page. CJ's link catalogue holds no Booking creatives, so the bookmarklet is the only route (§7.1).
   - GetYourGuide Partner → *Tools* → *Link Builder*
   - Viator Partner → *Tools* → *Link Builder*

   Pull real partner IDs from your password manager — never from the repo (§10). Verify the tracking label survived in the generated URL; some link builders silently drop custom parameters.

3. **Paste the links into article content** via the Gutenberg link tool or the HTML/code view. Every link must carry **both** `rel="sponsored noopener"` and `target="_blank"` (§12). Anchor text follows §8 — inline natural phrasing and/or explicit "Where to stay:" / "Book this tour:" callouts. Mix both modes within an article.

4. **Flag the post.** In the post editor sidebar, open the **Affiliate disclosure** metabox and check **This post contains affiliate links**. The tracking-label field below becomes enabled.

5. **Enter the tracking label and save.** Type the label from the preamble step (e.g. `dubrovnik-couples`), then click **Update**. On the next page load, the disclosure notice will appear at the top of the article, and the "Last modified" stamp inside the metabox will record the change for audit purposes.

If the label is rejected on save (invalid characters or too long), a red admin notice appears explaining why. Fix the label, save again. The checkbox state and content edits are saved either way — only the label is held back on validation failure.

---

## 14. Review cadence

- Re-read this document before adding affiliate links to any new article.
- Re-evaluate program list and disclosure wording once per year, or when:
  - A program changes its terms of service
  - FTC / EU UCPD guidance updates materially
  - The site adds a Croatian-language section (triggers §9 deployment)
