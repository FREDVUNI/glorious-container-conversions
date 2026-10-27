# Glorious Container Conversions — website prototype

A standalone HTML, CSS and vanilla JavaScript prototype of the new website for
Glorious Container Conversions Uganda Limited, Kampala. It is built for client
design review: every page and navigation item works, but nothing is connected
to a backend.

## Run it locally

Open the folder through any static HTTP server (opening `index.html` directly
from the filesystem also works, but a server gives the truest result):

```bash
cd glorious-container-conversions
python3 -m http.server 8000
# then visit http://localhost:8000/
```

No build step, package manager or dependencies. The only external resource is
Google Fonts (Barlow Condensed, Barlow, IBM Plex Mono); if it is unreachable the
site falls back to system fonts.

## Structure

```
index.html                 Home
pages/about.html           Company, mission and vision, team, yard
pages/services.html        Ten services with in-page index, standard unit sizes
pages/projects.html        Filterable project gallery with lightbox (data in projects.js)
pages/blog.html            Blog index
pages/blog/*.html          Four full articles (text from the client's current site)
pages/contact.html         Contact details and quote-request form
assets/css/styles.css      Tokens, base, components, page layouts
assets/css/animations.css  Motion system and reduced-motion overrides
assets/css/responsive.css  Breakpoints (1700, 1180, 960, 640, 380) and print
assets/js/main.js          Loader, scroll reveals, header, counters, scroll-spy
assets/js/navigation.js    Mobile menu (focus trap, Escape, scroll lock)
assets/js/projects.js      Project data, filtering, search, lightbox
assets/js/forms.js         Client-side validation and feedback states
assets/images/             Project photography as WebP at 800 and 1600 px widths
assets/icons/              Client logo (trimmed from the current site) and favicon
```

## What is demonstrative (not connected)

| Feature | Prototype behaviour | Later phase |
|---|---|---|
| Quote-request form | Validates in the browser, then shows a clearly labelled "validated, not yet sent" state with the entered details and phone/email links. **No email or data is sent.** | Server-side processing, email delivery, spam protection |
| Project gallery | Rendered from a data array in `assets/js/projects.js` | CMS-managed projects |
| Blog | Four articles reproduced in full from the client's current site, as static pages | Article management in the CMS |
| Page loader | Shows once per browser session | Keep, drop, or tie to real asset loading |

The container configurator is out of scope and not included.

## Content and images needing client review

Project and team photographs come from the company's current website
(gloriouscontainerconversions.com) and social imagery hosted there. Where no
company photo fitted a section, a stock photograph from Unsplash (free licence,
no attribution required) is used and captioned "Stock photo" on the page. These
are temporary and should be replaced with company photography when available:

| File (assets/images/) | Used for | Source |
|---|---|---|
| u-design-01 | Process step "Design" | https://unsplash.com/photos/1765808376543-50701923a5c1 |
| u-weld-01, u-weld-02, u-weld-03 | Services cover, About, process "Fabrication" | https://unsplash.com/photos/1564182842834-681b7be6de4b, 1508942265659-11640804f715, 1508942397308-8f5d91c1f0f1 |
| u-truck-01, u-truck-02 | Coverage, Freight service | https://unsplash.com/photos/1700616270841-078da5600853, 1635434473843-bac9feff5e8e |
| u-port-02 | "What we do" statement | https://unsplash.com/photos/1617938568125-b55e59775632 |
| u-home-01, u-home-02 | Blog article covers | https://unsplash.com/photos/1745566589290-d678de04f990, 1628394029761-acc83a2a08a6 |
| u-interior-01 | Insulation article | https://unsplash.com/photos/1628394029749-ea3dcce4caa6 |
| u-cafe-01 | Services cover strip | https://unsplash.com/photos/1666648040183-957d990e4d1a |
| u-solar-01 | Computer laboratories service | https://unsplash.com/photos/1740825961434-e9287638592b |

Each ID above is the `photo-<id>` segment of the image URL; append it to
`https://images.unsplash.com/photo-` to view the original. Unused downloads
(u-design-02, u-truck-03, u-port-01, u-port-03, u-home-03, u-cafe-02) are kept
in the folder as alternates.

The following need confirmation before launch:

- **Projects marked "Client to confirm details"** in the gallery: the red 40ft
  home, blue residence, grey home, green residence, timber-clad kiosk and the
  compact red 20ft unit. Titles and specs were inferred from the photographs
  only. Locations and clients are not stated anywhere.
- **"Container offices"** on the Services page shows a hospital unit's fitted
  room; "Computer laboratories" uses a stock solar-installation photo.
- **Social links** in the footer: Facebook, X and YouTube are the accounts
  linked from the current site; WhatsApp uses the main phone number. No
  Instagram, LinkedIn or TikTok account could be found; add them if they exist.
- **Hero statistics**: "10+ years" and "48 hours to CAD drawings" come from the
  current site's About page; "7 countries by road" counts the countries listed
  there. No other numbers were invented.
- **Team**: names, roles and portraits are as published on the current site.
- **Testimonials**: none are included because none exist on the current site.
- Low-resolution originals (several project photos are 720–1080 px wide and
  some carry a watermark) will look soft on large screens. Higher-resolution
  originals from the client would improve the hero and featured-project slots.

## Accessibility and behaviour notes

- Works without JavaScript: all content and links are reachable; the gallery
  grid and form checks need scripts and say so.
- `prefers-reduced-motion` disables the loader, reveals, marquee and transitions.
- Keyboard: mobile menu and lightbox trap focus, Escape closes, arrow keys move
  through gallery photos, skip link to content.
- Tested in headless Chromium at 390, 820 and 1440 px widths with no console
  errors or horizontal overflow.

## Design direction

Industrial fabrication meets architectural drawing: warm primer-grey paper,
condensed display type, monospace "plate" labels styled like container ID
plates, measurement-line motifs, corrugation textures on dark bands, and the
client's teal and safety-yellow brand colours taken from the existing logo.
Reveals are wipes and rises rather than bounces; one easing family throughout.
