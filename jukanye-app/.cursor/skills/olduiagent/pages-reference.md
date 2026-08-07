# Old site pages reference

Canonical registry: `old/admin/ncsitebuilder/index.php` → `$pages`.

Home page id (router `$home_page`): `a19fb429797b0069f950acd7424ca5e8` (empty alias = site root `/` and `/sw/`).
Marketing “Homeb” page exists separately for `/Homeb/` and `/sw/Mwanzo/`.

## Page map

| # | EN alias | SW alias | File / id | Notes |
|---|----------|----------|-----------|--------|
| 1 | `Homeb` | `Mwanzo` | `a188dd9eef5300bb9a9e9122025694a7.php` | Primary marketed home with countdown, slideshows, menu |
| 2 | *(empty → `/`)* | *(empty → `/sw/`)* | `a19fb429797b0069f950acd7424ca5e8.php` | Configured home page id |
| 3 | `About-Us` | `Shughuli-Zetu` | `a188dd9eef5301da18cbe22b97624cf4.php` | About / activities |
| 4 | `Award-Nominees` | `Waliopendekezwa-kupewa-Tuzo` | `a188dd9eef5303f88376178327db5a99.php` | Awards |
| 5 | `Schedule` | `Schedule` | `a19884133bfb00abd9131acdd9d24f77.php` | Programme |
| 6 | `Event-Products` | `Bidhaa-za-Tamasha` | `a19884067143001d08c2a82208f5bda8.php` | Merch / products |
| 7 | `Donate` | `Changia` | `a1987b9f84e2009fae9fb77f3f909e24.php` | Donate |
| 8 | `Sponsors` | `Wadhamini` | `a1987be9833000975f822114d0eef4fc.php` | Sponsors |
| 9 | `Register` | `Jisajiri` | `a1987baa102c006b81a9671b5040cb01.php` | Has site form + ties to registration flow |
| 10 | `Download` | `Pakua` | `a198811b90a8008abf158ea105e233e2.php` | Upload / Download |
| 11 | `Contacts` | `Mawasiliano` | `a188dd9eef53020e3326fc90d8aab24d.php` | Contacts |
| 12 | `Unlisted` | `Unlisted` | `a198900350f300a37ae9158159156524.php` | Unlisted / utility |

Each page stylesheet: `old/admin/ncsitebuilder/css/<same-id>-bundle.css`.

Public URLs (production pattern):
- EN: `https://jukanye.com/<EN-alias>/`
- SW: `https://jukanye.com/sw/<SW-alias>/`

## Main navigation (from Homeb menu)

Order as rendered on Homeb:

1. Home → `{{base_url}}`
2. Sponsors → `Sponsors/`
3. Donate → `Donate/`
4. Register → `Register/`
5. Upload / Download → `Download/`
6. Award Nominees → `Award-Nominees/`
7. Schedule → `Schedule/`
8. Event Products → `Event-Products/`
9. About Us → `About-Us/`
10. Contacts → `Contacts/`

Plus language switcher: **English** | **Kiswahili**.

## Homeb content patterns

Inspect `a188dd9eef5300bb9a9e9122025694a7.php` for:

- Mobile hamburger menu (`wb-menu-mobile`)
- Countdown timer (`data-plugin="countdown"`) — display font DM Serif Display, accent `#dfc91b`
- Hero / partner slideshows via `GalleryLib`
- Section blocks with Font Awesome icons in teal `#0ca3a6`
- PayPal / BuyNow plugin embeds (check `$pluginData` JSON in page)
- Dual-language duplicate HTML (EN first, then SW copy)

SEO default description:
> JUKANYE International Festival — Celebrating African Liberation, Legacy, and Unity

## Register form (site builder)

On page id `a1987baa102c006b81a9671b5040cb01`, form in `$forms` emails:

- To: `jukanyefestival@gmail.com`
- From: `no-reply@jukanye.com`
- Subject: `Enquire from the web page`

Known fields (EN / SW labels): Name/Jina, Second Name/Jina la Pili, Organization, Country, Phone/Simu, plus additional fields later in `$forms` — always re-read `index.php` `$forms` before claiming a full field list.

## How to find copy / media

1. Grep the hash PHP for visible headings (`wb-stl-heading`, `<h1`–`<h3`, `<p class="wb-stl-`).
2. Grep for `gallery/` or `gallery_gen/` image paths.
3. For Swahili-only work, search near the second `<html lang=` / later half of the same PHP file.
