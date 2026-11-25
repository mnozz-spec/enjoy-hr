# Task 07 — Affiliate link plan (initial rollout)

Concrete, per-article plan for inserting affiliate links into the three target posts. Reviewed BEFORE any links go into article content. Once approved, Step 7 substitutes real partner IDs and Step 9 deploys to production.

**Cross-references:**
- Strategy: `docs/affiliate-strategy.md`
- Task brief: `tasks/07-affiliate-disclosure.md`

Last updated: 2026-09-09.

---

## Step 5 — URL templates (placeholders only)

Three programs, three articles. Each article uses its own tracking label so revenue attribution is per-article in the partner dashboards.

### Tracking labels (from strategy §7.4 / §11)

| Article | Tracking label |
|---|---|
| Dubrovnik for Two (post 2650) | `dubrovnik-couples` |
| Most Romantic Istrian Towns (post 2753) | `istria-couples` |
| Quiet Islands in Croatia (post 2657) | `islands-couples` |

### Template patterns (substitute IDs in Step 7)

**Booking.com is reached through CJ Affiliate**, so its links are CJ redirect URLs rather than `booking.com/...?aid=` URLs. Full rationale and programme facts in strategy §7.1.

Two tokens remain outstanding for Booking:
- `{AID}` — CJ's ad/link ID for the Booking deep-link creative. **Not yet known.** Booking.com North America publishes no creatives in CJ's link catalogue, so it can only come from the CJ DEEP LINK bookmarklet. See the blocker in *Open decisions*.
- `{CJ_REDIRECT_DOMAIN}` — whichever CJ tracking domain the generator emits (`anrdoezrs.net`, `dpbolvw.net`, `tkqlhce.com`, `jdoqocy.com`). Do not guess or substitute.

The property ID `101756772` is already known and is public (strategy §10.1), so it is written literally into every template below. GetYourGuide's `{PARTNER_ID}` and Viator's `{PID}` / `{MCID}` still come from the password manager.

**Booking.com (via CJ):**
```
https://www.{CJ_REDIRECT_DOMAIN}/click-101756772-{AID}?url={URL_ENCODED_BOOKING_URL}&sid=enjoyhr-{TRACKING_LABEL}
```
The `url` value must be percent-encoded. Note `sid` replaces Booking's old `label` parameter throughout this plan.

**GetYourGuide:**
```
https://www.getyourguide.com/s/?q={QUERY}&partner_id={PARTNER_ID}&cmp=enjoyhr-{TRACKING_LABEL}
```

**Viator** (held in reserve as fallback — not used in this initial rollout):
```
https://www.viator.com/searchResults/all?text={QUERY}&pid={PID}&mcid={MCID}&medium=link&campaign=enjoyhr-{TRACKING_LABEL}
```

### Required link attributes on every `<a>` tag

Per strategy §12:
```html
<a href="..." rel="sponsored noopener" target="_blank">anchor text</a>
```

---

## Step 6 — Article integration plan

> **Placement constraint for Booking links.** The CJ Booking programme converts **in-session only** — no cookie window (strategy §7.1). Booking links must sit next to booking intent, which in practice means the accommodation or practical-notes sections. All six placements below already satisfy this; keep it in mind for any revision.

### Density summary

| Article | Word count | Density tier (§8) | Target range | Proposed |
|---|---|---|---|---|
| Dubrovnik for Two | ~1,050 | Medium | 2–4 | **3 links** |
| Romantic Istrian Towns | ~3,200 | Long round-up | 4–8 | **6 links** |
| Quiet Islands in Croatia | ~2,200 | Long round-up | 4–8 | **6 links** |
| **Total across rollout** |  |  |  | **15 links** |

Program split: ~5 Booking.com (accommodation), ~10 GetYourGuide (tours/experiences), 0 Viator (held in reserve — GYG has strong inventory for every experience we'd link). Roughly matches the §11 program-to-article matrix in the strategy doc.

---

### Article 1 — Dubrovnik for Two (post 2650)

Tracking label: `dubrovnik-couples`. First-person travel-narrative voice — anchor text should feel like a friend's recommendation, not a marketing callout.

#### 1.1 — Booking.com — Where to stay

**Section:** *A Few Practical Notes Before You Go* (final section)

**Surrounding prose (existing):**
> Stay just outside the Old Town walls if budget is a consideration — you lose nothing in terms of experience and gain considerably in terms of cost.

**Proposed change — append one sentence:**
> Stay just outside the Old Town walls if budget is a consideration — you lose nothing in terms of experience and gain considerably in terms of cost. [Search Dubrovnik stays](URL) to compare options inside the walls, just outside the gates, and along the coast toward Lapad.

- **Anchor text:** *Search Dubrovnik stays*
- **Style (§8):** Inline natural — link is part of a natural reading sentence
- **Program:** Booking.com
- **URL template:**
  ```
  https://www.{CJ_REDIRECT_DOMAIN}/click-101756772-{AID}?url=https%3A%2F%2Fwww.booking.com%2Fsearchresults.html%3Fss%3DDubrovnik&sid=enjoyhr-dubrovnik-couples

  # destination before encoding: https://www.booking.com/searchresults.html?ss=Dubrovnik
  ```

#### 1.2 — GetYourGuide — Sunset boat tour

**Section:** *Sail the Elaphiti Islands at Sunset*

**Surrounding prose (existing):**
> If there's one splurge worth making in Dubrovnik, it's a private sunset boat tour out to the Elaphiti Islands.

**Proposed change — turn the existing phrase into a link, no new text:**
> If there's one splurge worth making in Dubrovnik, it's [a private sunset boat tour out to the Elaphiti Islands](URL).

- **Anchor text:** *a private sunset boat tour out to the Elaphiti Islands*
- **Style:** Inline natural — the link is the existing sentence
- **Program:** GetYourGuide
- **URL template:**
  ```
  https://www.getyourguide.com/s/?q=Dubrovnik+Elaphiti+sunset+boat+tour&partner_id={PARTNER_ID}&cmp=enjoyhr-dubrovnik-couples
  ```

#### 1.3 — GetYourGuide — Pelješac wine tour

**Section:** *Drink Wine in the Croatian Countryside*

**Surrounding prose (existing):**
> Some tours even combine a sunset sail with a tasting, which feels almost comically perfect.

**Proposed change — append one sentence with explicit callout:**
> Some tours even combine a sunset sail with a tasting, which feels almost comically perfect. **Browse Pelješac wine tours from Dubrovnik:** [day-long vineyard trips](URL), most including transport from the Old Town and a stop at a family producer along the way.

- **Anchor text:** *day-long vineyard trips* (with the bolded "Browse Pelješac wine tours from Dubrovnik:" as a callout lead)
- **Style:** Explicit callout — "Where to..." pattern from §8
- **Program:** GetYourGuide
- **URL template:**
  ```
  https://www.getyourguide.com/s/?q=Dubrovnik+Peljesac+wine+tour&partner_id={PARTNER_ID}&cmp=enjoyhr-dubrovnik-couples
  ```

---

### Article 2 — Most Romantic Istrian Towns (post 2753)

Tracking label: `istria-couples`. Long guidebook-style article, more direct voice. Anchor text can lean explicit ("Browse X", "Book Y") more comfortably here.

#### 2.1 — Booking.com — Rovinj boutique hotels

**Section:** *Rovinj — the Most Picturesque Town in Istria* (the section's penultimate paragraph about Punta Corrente)

**Surrounding prose (existing):**
> Getting there: Rovinj is 90 minutes from Pula airport by car and about 2.5 hours from Trieste. It makes the ideal home base for a coastal Istrian weekend — stay two nights and use it as a base to explore the coast and nearby inland towns around the area.

**Proposed change — append one sentence:**
> Getting there: Rovinj is 90 minutes from Pula airport by car and about 2.5 hours from Trieste. It makes the ideal home base for a coastal Istrian weekend — stay two nights and use it as a base to explore the coast and nearby inland towns around the area. **Where to stay:** [boutique hotels and apartments in Rovinj's old town and harbour](URL) — the most romantic stays are inside the pastel old town itself.

- **Anchor text:** *boutique hotels and apartments in Rovinj's old town and harbour*
- **Style:** Explicit callout
- **Program:** Booking.com
- **URL template:**
  ```
  https://www.{CJ_REDIRECT_DOMAIN}/click-101756772-{AID}?url=https%3A%2F%2Fwww.booking.com%2Fsearchresults.html%3Fss%3DRovinj&sid=enjoyhr-istria-couples

  # destination before encoding: https://www.booking.com/searchresults.html?ss=Rovinj
  ```

#### 2.2 — GetYourGuide — Truffle hunting in Motovun

**Section:** *Motovun — Truffles, Vineyard Views, and Medieval Walls* (paragraph mentioning the truffle forest below the town)

**Surrounding prose (existing):**
> Motovun is what people mean when they describe Istria as looking like Tuscany. A medieval hilltop town rising 277 metres above the Mirna Valley, it sits at the centre of Istria's truffle country — the Motovun Forest below is one of the richest truffle-producing areas in Europe, yielding both black summer truffles and the rarer white truffle in autumn. The best time for truffle hunting in the Motovun forest is September through January.

**Proposed change — append one sentence:**
> The best time for truffle hunting in the Motovun forest is September through January. [Local outfitters run morning truffle hunts](URL) with a dog and a guide — usually followed by a tasting of the day's finds with local wine.

- **Anchor text:** *Local outfitters run morning truffle hunts*
- **Style:** Inline natural
- **Program:** GetYourGuide
- **URL template:**
  ```
  https://www.getyourguide.com/s/?q=Motovun+truffle+hunting&partner_id={PARTNER_ID}&cmp=enjoyhr-istria-couples
  ```

#### 2.3 — Booking.com — Motovun & Grožnjan inland B&Bs

**Section:** *Itinerary B — The Inland Weekend (Motovun or Grožnjan base)*

**Surrounding prose (existing):**
> Day 1: Arrive in Motovun. Check into a vineyard B&B.

**Proposed change — link the existing phrase:**
> Day 1: Arrive in Motovun. Check into [a vineyard B&B](URL).

- **Anchor text:** *a vineyard B&B*
- **Style:** Inline natural
- **Program:** Booking.com
- **URL template:**
  ```
  https://www.{CJ_REDIRECT_DOMAIN}/click-101756772-{AID}?url=https%3A%2F%2Fwww.booking.com%2Fsearchresults.html%3Fss%3DMotovun&sid=enjoyhr-istria-couples

  # destination before encoding: https://www.booking.com/searchresults.html?ss=Motovun
  ```

#### 2.4 — GetYourGuide — Lim Fjord boat tour from Vrsar

**Section:** *Vrsar — Casanova's Seaside Town on the Lim Fjord* (last paragraph before "Getting there:")

**Surrounding prose (existing):**
> Beyond its famous connection, Vrsar is simply beautiful: a little town of narrow streets, a Romanesque church tower visible for miles, and a harbour where oysters from the Lim Fjord are served with white wine at outdoor tables. The Lim Fjord itself — one of Istria's most scenic natural features — offers boat tours from the harbour that take about an hour.

**Proposed change — link the final phrase:**
> The Lim Fjord itself — one of Istria's most scenic natural features — offers [boat tours from the harbour](URL) that take about an hour.

- **Anchor text:** *boat tours from the harbour*
- **Style:** Inline natural
- **Program:** GetYourGuide
- **URL template:**
  ```
  https://www.getyourguide.com/s/?q=Vrsar+Lim+Fjord+boat+tour&partner_id={PARTNER_ID}&cmp=enjoyhr-istria-couples
  ```

#### 2.5 — Booking.com — Poreč seaside hotels

**Section:** *Poreč — Euphrasian Basilica, Seafood, and Venetian Old Town* (last paragraph before "Getting there:")

**Surrounding prose (existing):**
> Away from the basilica, Poreč offers long seaside promenades, excellent restaurants serving fresh Adriatic seafood, and easy ferry access to Brijuni National Park. Rovinj and Poreč together make the ideal coastal Istrian weekend — different in character but perfectly complementary.

**Proposed change — append one short sentence:**
> Rovinj and Poreč together make the ideal coastal Istrian weekend — different in character but perfectly complementary. [Seafront hotels in Poreč](URL) range from family-run guesthouses to larger Adriatic resorts a few minutes' walk from the old town.

- **Anchor text:** *Seafront hotels in Poreč*
- **Style:** Inline natural
- **Program:** Booking.com
- **URL template:**
  ```
  https://www.{CJ_REDIRECT_DOMAIN}/click-101756772-{AID}?url=https%3A%2F%2Fwww.booking.com%2Fsearchresults.html%3Fss%3DPorec&sid=enjoyhr-istria-couples

  # destination before encoding: https://www.booking.com/searchresults.html?ss=Porec
  ```

#### 2.6 — GetYourGuide — Wine tastings around the Mirna Valley

**Section:** *Local Cuisine, Olive Groves, and Practical Tips for Your Istrian Getaway* (paragraph on local wines)

**Surrounding prose (existing):**
> For wine, look for Malvazija Istarska (white, aromatic, slightly mineral) and Teran (red, earthy, excellent with meat and truffle dishes). Many vineyards around the area welcome visitors for tastings without appointment in the shoulder seasons.

**Proposed change — append one sentence:**
> Many vineyards around the area welcome visitors for tastings without appointment in the shoulder seasons. [Guided wine tours](URL) cover several producers in a half-day and are the easier option if you'd rather not drive.

- **Anchor text:** *Guided wine tours*
- **Style:** Inline natural
- **Program:** GetYourGuide
- **URL template:**
  ```
  https://www.getyourguide.com/s/?q=Istria+wine+tasting+tour&partner_id={PARTNER_ID}&cmp=enjoyhr-istria-couples
  ```

---

### Article 3 — Quiet Islands in Croatia (post 2657)

Tracking label: `islands-couples`. Travel-narrative voice. Mix of inline and callout style. Six links across seven island sections — Lastovo and Silba intentionally skipped (inventory on the partner platforms is thin enough that a search link would feel hollow; the article's vibe of "go really remote" reinforces the no-link choice on those two).

#### 3.1 — GetYourGuide — Blue Cave tour (Vis)

**Section:** *Vis — the Island That Time Forgot (Dalmatian Coast)* (paragraph on Stiniva and Biševo)

**Surrounding prose (existing):**
> And just off Vis, the island of Biševo offers the famous Blue Cave (Modra špilja): a sea cave where light filters through a submerged opening and turns the Adriatic an otherworldly electric blue.

**Proposed change — append one sentence:**
> And just off Vis, the island of Biševo offers the famous Blue Cave (Modra špilja): a sea cave where light filters through a submerged opening and turns the Adriatic an otherworldly electric blue. [Day trips to the Blue Cave](URL) run from both Split and Vis Town, typically combining Biševo with snorkelling stops at the outer islands.

- **Anchor text:** *Day trips to the Blue Cave*
- **Style:** Inline natural
- **Program:** GetYourGuide
- **URL template:**
  ```
  https://www.getyourguide.com/s/?q=Blue+Cave+Bisevo+Vis&partner_id={PARTNER_ID}&cmp=enjoyhr-islands-couples
  ```

#### 3.2 — Booking.com — Vis Town accommodation

**Section:** *Vis — the Island That Time Forgot* (the practical tip at the end of the Vis section)

**Surrounding prose (existing):**
> Practical tip: Jadrolinija runs a year-round catamaran from Split to Vis Town — the crossing takes about two hours. Private transfers are also available from Split marina for more flexibility. Vis is a classic island near Split and the easiest of the outer islands to reach independently.

**Proposed change — append one sentence:**
> Vis is a classic island near Split and the easiest of the outer islands to reach independently. **Where to stay:** [small hotels and apartments in Vis Town and Komiža](URL) — both make good bases, with Komiža slightly quieter and more atmospheric.

- **Anchor text:** *small hotels and apartments in Vis Town and Komiža*
- **Style:** Explicit callout
- **Program:** Booking.com
- **URL template:**
  ```
  https://www.{CJ_REDIRECT_DOMAIN}/click-101756772-{AID}?url=https%3A%2F%2Fwww.booking.com%2Fsearchresults.html%3Fss%3DVis%2BIsland&sid=enjoyhr-islands-couples

  # destination before encoding: https://www.booking.com/searchresults.html?ss=Vis+Island
  ```

#### 3.3 — GetYourGuide — Dugi Otok / Telašćica day tour

**Section:** *Dugi Otok — the Long Island of Northern Croatia* (the practical tip)

**Surrounding prose (existing):**
> Practical tip: Jadrolinija runs ferries from Zadar to Brbinj on Dugi Otok — the crossing takes around 1.5 hours. The island is located in northern Croatia and makes an excellent base for island-hopping along the Zadar archipelago, including nearby islands Iž, Ugljan, and the Kornati Islands.

**Proposed change — append one sentence:**
> The island is located in northern Croatia and makes an excellent base for island-hopping along the Zadar archipelago, including nearby islands Iž, Ugljan, and the Kornati Islands. If you'd rather skip the logistics, [day boat tours from Zadar](URL) cover Dugi Otok, Telašćica's cliffs, and the Kornati Islands in one trip.

- **Anchor text:** *day boat tours from Zadar*
- **Style:** Inline natural
- **Program:** GetYourGuide
- **URL template:**
  ```
  https://www.getyourguide.com/s/?q=Zadar+Kornati+Dugi+Otok+boat+tour&partner_id={PARTNER_ID}&cmp=enjoyhr-islands-couples
  ```

#### 3.4 — GetYourGuide — Mljet National Park day trip

**Section:** *Mljet — National Park, Saltwater Lakes, and Olive Groves* (first paragraph after the islet/monastery description)

**Surrounding prose (existing):**
> You reach it by boat. It's the kind of place that makes you feel briefly untethered from ordinary life.

**Proposed change — append one sentence:**
> You reach it by boat. It's the kind of place that makes you feel briefly untethered from ordinary life. [Day trips to Mljet from Dubrovnik](URL) include the catamaran, park entry, and the boat to the islet — the simplest way to see the lakes in one day.

- **Anchor text:** *Day trips to Mljet from Dubrovnik*
- **Style:** Inline natural
- **Program:** GetYourGuide
- **URL template:**
  ```
  https://www.getyourguide.com/s/?q=Mljet+National+Park+day+trip+Dubrovnik&partner_id={PARTNER_ID}&cmp=enjoyhr-islands-couples
  ```

#### 3.5 — Booking.com — Mljet accommodation

**Section:** *Mljet — National Park, Saltwater Lakes, and Olive Groves* (the practical tip)

**Surrounding prose (existing):**
> Practical tip: Jadrolinija's Split–Hvar–Korčula–Dubrovnik catamaran stops at Mljet's Polače harbour in summer. A ferry also runs from Prapratno on the Pelješac peninsula — a shorter crossing and a good option if you're visiting Croatia by car.

**Proposed change — append one sentence:**
> A ferry also runs from Prapratno on the Pelješac peninsula — a shorter crossing and a good option if you're visiting Croatia by car. **Where to stay on Mljet:** [small guesthouses and apartments around Polače and Pomena](URL), both inside or right next to the national park.

- **Anchor text:** *small guesthouses and apartments around Polače and Pomena*
- **Style:** Explicit callout
- **Program:** Booking.com
- **URL template:**
  ```
  https://www.{CJ_REDIRECT_DOMAIN}/click-101756772-{AID}?url=https%3A%2F%2Fwww.booking.com%2Fsearchresults.html%3Fss%3DMljet%2BIsland&sid=enjoyhr-islands-couples

  # destination before encoding: https://www.booking.com/searchresults.html?ss=Mljet+Island
  ```

#### 3.6 — GetYourGuide — Elafiti Islands day trip

**Section:** *The Elafiti Islands — Car-Free Coastal Villages Near Dubrovnik* (the paragraph on Lopud as a half-day option)

**Surrounding prose (existing):**
> Koločep, the closest to Dubrovnik, is an easy half-day trip when you need to be back in the city by evening. For couples visiting Croatia who are based along the Dalmatian coast in Dubrovnik, this is the simplest escape from the hustle — and one of the most rewarding day trips in the region.

**Proposed change — append one sentence:**
> For couples visiting Croatia who are based along the Dalmatian coast in Dubrovnik, this is the simplest escape from the hustle — and one of the most rewarding day trips in the region. [Three-island Elafiti tours](URL) typically take in Koločep, Lopud, and Šipan with swim stops and lunch on board.

- **Anchor text:** *Three-island Elafiti tours*
- **Style:** Inline natural
- **Program:** GetYourGuide
- **URL template:**
  ```
  https://www.getyourguide.com/s/?q=Elafiti+Islands+day+tour+Dubrovnik&partner_id={PARTNER_ID}&cmp=enjoyhr-islands-couples
  ```

---

## Implementation checklist (Step 7 onward)

For each link above, in Step 7:

0. **Booking only, once:** generate one link with the CJ DEEP LINK bookmarklet, read the `{AID}` and `{CJ_REDIRECT_DOMAIN}` out of it, and fill them into all six Booking templates. Until this is done the Booking links cannot be built at all.
1. Substitute the remaining partner IDs (GetYourGuide, Viator) from the password manager into the URL templates.
2. Verify the tracking-label substring (`enjoyhr-<label>`) is intact in the final URL, in `sid` for Booking and `cmp` / `campaign` for the others — some link builders strip custom params.
3. Manually click the URL once to confirm it lands on a sensible search results page (not 404, not blocked). **For Booking, confirm the CJ redirect actually reaches the search-results page** — some advertisers restrict deep linking to product pages only, which would force a rethink of the search-URL approach.
4. Update the post content via Gutenberg or `wp post update`, inserting the `<a>` tag with `rel="sponsored noopener" target="_blank"`.
5. Tick the **This post contains affiliate links** checkbox in the metabox.
6. Enter the article's tracking label.
7. Save.
8. Verify on the front-end that the disclosure notice renders and all new links open the right partner page in a new tab.

Do this on staging first (Step 8), then production after approval (Step 9). Cloudflare purge after the production update so the disclosure CSS is served fresh.

---

## Blocker

**The Booking.com `{AID}` and CJ redirect domain are still unknown**, so the six Booking links in this plan cannot be finalised. CJ's Links & Products catalogue returns zero results for Booking.com North America (checked 2026-09-09), which leaves the CJ DEEP LINK bookmarklet as the only source. Installing a browser bookmarklet is a manual drag-to-toolbar step that only Marko can perform: CJ dashboard → *Account* → *Link Tools*. Generate one link against any Booking.com search-results page and the two tokens can be read straight out of it.

The nine GetYourGuide links are unaffected and can proceed independently.

---

## Open decisions for review

Before Step 7, confirm or revise:

1. **Link count per article** — 3 / 6 / 6 = 15 total. Feels balanced against word count; happy to dial up or down.
2. **Anchor text wording** — every proposed phrase listed above. Edit any that feel off-tone for the article's voice.
3. **Skipped opportunities** — flagged sections where I deliberately did NOT add a link, in case any feel like missed revenue:
   - *Dubrovnik:* Dubrovnik Pass / Walls combo ticket (GYG has it, but the article already gives the practical advice; an extra link would feel stuffed in a medium piece).
   - *Istria:* Brijuni National Park boat tour from Poreč (could add but adds a 7th link), Meneghetti Wine Hotel direct Booking link (would lock to one property — strategy §12 forbids this).
   - *Islands:* Lastovo, Silba — partner inventory too thin to merit a search-result link; skipping aligns with the article's "really remote" tone.
4. **Booking destination strings** — used `Dubrovnik`, `Rovinj`, `Motovun`, `Porec`, `Vis+Island`, `Mljet+Island`. All confirmed as Booking-recognised destination queries. The `+Island` suffix on Vis and Mljet disambiguates from mainland places with the same name.
5. **Viator** — currently unused. We can rebalance one or two GYG slots to Viator for revenue diversification if you'd like — happy to suggest which.
