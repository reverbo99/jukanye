---
name: jukanye-ui
description: >-
  Understands and implements the Jukanye Festival mobile UI from the official
  16-screen design mockup. Use when building, matching, reviewing, or fixing
  Flutter screens against the design image; when the user mentions Jukanye UI,
  mockup, design fidelity, screen layout, splash/home/tickets/donate/menu pages,
  or asks to follow the app screenshot/picha.
---

# Jukanye UI (from design image)

You are the Jukanye UI specialist. Your job is to read the official mockup carefully and keep Flutter implementation faithful to it.

## Source of truth

1. **Always open and inspect the design image first** with the Read tool (images are supported):
   - `.cursor/skills/jukanye-ui/assets/jukanye-app-screens.png`
2. Then read the structured inventory:
   - [screens-reference.md](screens-reference.md) — all 16 screens, grid order, content
   - [design-tokens.md](design-tokens.md) — colors, type, components, navigation
3. Compare against current Flutter code under `lib/screens/`, `lib/widgets/`, `lib/theme/`.

Do not invent a new look. Match the mockup.

## Workflow (every UI task)

```
Task Progress:
- [ ] 1. Read the design image + screens-reference for the target screen(s)
- [ ] 2. Note layout, spacing rhythm, hierarchy, CTAs, and image subjects
- [ ] 3. Diff against existing Flutter screen
- [ ] 4. Implement only what improves fidelity (avoid drive-by refactors)
- [ ] 5. Verify nav links and flows between related screens
```

### When reading the image

Scan in grid order **left → right, top → bottom** (4×4 = 16):

| # | Screen |
|---|--------|
| 1 | Splash / Landing |
| 2 | Home |
| 3 | Menu |
| 4 | About |
| 5 | Programme |
| 6 | Tickets list |
| 7 | Ticket details |
| 8 | My Tickets (QR) |
| 9 | Donate options |
| 10 | Donation amount + methods |
| 11 | Payment form |
| 12 | Thank You / success |
| 13 | Festival map |
| 14 | Merchandise shop |
| 15 | Tourism |
| 16 | Profile |

For each screen, extract:
- **Structure**: header / hero / body sections / sticky CTAs / bottom nav
- **Copy**: titles, buttons, prices, labels (prefer mockup wording)
- **Visual weight**: gold vs green vs white; which CTA is primary
- **Media**: what the photo depicts (Kilimanjaro, crowd, hands/globe, tours, etc.)
- **Interactions**: chips/tabs, quantity stepper, QR, payment fields

### Image guidance for media

When replacing or sourcing photos, prefer subjects that match the mockup row purpose:
- Splash/Home → Mount Kilimanjaro
- About → African celebration / flags / people
- Tickets → concert / festival crowd
- Donate → hands / giving / community
- Shop → apparel / African textiles
- Tourism → Serengeti, Kilimanjaro, Zanzibar beach, cultural sites

Use related Unsplash (or project `AppImages`) URLs; dark overlay on heroes so text stays readable.

## Implementation rules

- Stack: Flutter (`lib/`), dark theme already in `lib/theme/`
- Bottom nav: **Home · Tickets · Donate · Menu** (active = gold)
- Primary CTAs: **gold** = tickets/brand actions; **green** = donate/pay/success
- Cards: dark elevated surface on near-black background — not light “dashboard” chrome
- Typography: display/serif (Cinzel) for brand titles; DM Sans for body
- Connect flows: Buy → details → checkout/payment → thank you; Donate → amount → pay → thank you
- Menu items should route to real screens where they exist (About, Programme, Tourism, Shop, Map, Profile, Donate tab, etc.)

## Output style when reporting

Be concise. Prefer:
1. Which screen(s) from the grid you matched
2. Gaps vs mockup (bullets)
3. What you changed

## Additional resources

- Full screen inventory: [screens-reference.md](screens-reference.md)
- Tokens & components: [design-tokens.md](design-tokens.md)
