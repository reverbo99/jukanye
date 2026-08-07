# Jukanye design tokens

Extracted from the official mockup. Prefer these over reinventing the theme.

## Color

| Role | Approx hex | Usage |
|------|------------|--------|
| Background | `#0B0B0B` / near black | Scaffold |
| Surface / card | `#161616`–`#1A1A1A` | Cards, list rows |
| Elevated | `#1E1E1E` | Inputs, chips bg |
| Gold | `#C9A227` / `#D4AF37` | Brand, active nav, primary ticket CTAs |
| Gold light | `#E0C15A` | Secondary highlights |
| Green | `#1F6B3A` / `#2D5A27` | Donate, Pay Now, success |
| Green light | `#2E8B57` | Success icon accents |
| Text primary | `#F5F5F5` | Titles, body emphasis |
| Text secondary | `#B0B0B0` | Supporting copy |
| Text muted | `#7A7A7A` | Captions, inactive nav |
| Border | `#2A2A2A` | Dividers, outlines |

## Typography

- **Display / brand / section titles:** Cinzel (serif), semibold–bold, gold or white
- **UI / body / lists:** DM Sans (sans), regular–semibold
- Button labels: uppercase or strong weight, tracked slightly

## Layout patterns

- Mobile frame width mindset (~390 logical px)
- Section padding ~16–20 horizontal
- Cards: 12–16 radius, no heavy multi-shadow “AI chrome”
- Hero images: edge-aware inside screen; dark gradient overlay for readability
- Bottom nav height compact; icons + short labels; active = gold

## Components

| Component | Spec |
|-----------|------|
| Primary button (ticket/brand) | Gold fill, dark text |
| Primary button (money/success) | Green fill, white text |
| Secondary | Outline / ghost on dark |
| Amount / filter chips | Dark bg; selected = gold fill + dark text |
| List row | Dark card or divider list; gold leading icon; chevron |
| Progress bar | Gold fill on dark track |
| QR block | White square behind QR on dark ticket card |
| Quantity stepper | − value + on elevated chips |

## Flutter map (existing)

| Concern | Path |
|---------|------|
| Colors | `lib/theme/app_colors.dart` |
| Theme | `lib/theme/app_theme.dart` |
| Image URLs | `lib/data/app_images.dart` |
| Content/data | `lib/data/app_data.dart` |
| Bottom nav | `lib/widgets/app_bottom_nav.dart` |
| Buttons | `lib/widgets/app_button.dart` |
| Screens | `lib/screens/*.dart` |
| Shell / routing | `lib/navigation/main_shell.dart` |

When fixing fidelity, prefer editing these files over creating a parallel design system.
