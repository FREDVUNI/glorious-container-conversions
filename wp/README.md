# Glorious Container Conversions — WordPress

The production site for Glorious Container Conversions Uganda Limited, built
from the approved prototype (the static HTML one folder up) as a custom
WordPress theme plus a plugin.

```
wp/
├── wp-content/
│   ├── themes/glorious/       presentation only — templates, the prototype's CSS/JS/photos
│   └── plugins/gcc-core/      everything else — projects, services, team, enquiries, settings, the quote form, emails, admin screens
├── link-local.sh              symlink both into a Local by Flywheel site
├── package.sh                 build the two upload zips into dist/
└── README.md
```

## Why a theme _and_ a plugin

The theme is how the site looks this year. The projects, the services, every
enquiry anyone has ever sent — those have to outlive the next redesign. They
live in the plugin, so switching theme loses nothing.

## Local development

1. In **Local**, create a site called `glorious` (PHP 8.1 or newer).
2. `./link-local.sh` — symlinks the theme and plugin into that site.
3. In wp-admin: **Plugins → activate Glorious Container Conversions**, then
   **Appearance → Themes → activate Glorious**.

Activating the plugin creates the five pages (Home, About, Services, Blog,
Contact), sets Home as the front page and Blog as the posts page, switches on
pretty permalinks, builds the primary menu (with Work, the project archive,
in the middle), and seeds the content from the prototype: 13 projects in 7
categories, 10 services, 3 team members and the 4 articles. Photos are
bundled with the theme until the client uploads their own.

## What the client manages from wp-admin

Everything under the **GCC** menu:

| Screen       | What it is                                                                                                                             |
| ------------ | -------------------------------------------------------------------------------------------------------------------------------------- |
| Dashboard    | New enquiries, this month's count, won jobs, a 12-month bar chart, what needs attention, where every enquiry stands, projects still marked "to confirm" |
| Projects     | The Work page and lightbox. Reference, photos (drag to reorder), specs, features, card size, home-page slot, "details to confirm" flag, category |
| Services     | The Services page, in menu order. Bullets, link text, which quote type the link pre-selects, home-page "What we build" flag and tags   |
| Team         | Name, role, portrait, optional quote. Shown on About                                                                                   |
| Testimonials | Name, who they are, the quote, optional project. Shown on the home page when any exist                                                 |
| Enquiries    | Every quote request, with status (New → Contacted → Quoted → Won/Lost), internal notes and a CSV export. Never created by hand         |
| Settings     | Notifications, company details, site status, sharing, social links, every line of home-page copy, hero photos, hero figures, process steps, unit sizes, About and other page intros, reCAPTCHA |

The blog uses WordPress's own **Posts**. Featured image, categories and the
excerpt drive the cards; a "Details" box adds reading time and a cover caption.
`[gcc_image name="home-blue-01" alt="…" caption="…"]` drops a bundled photo
into an article.

Page prose is editable in the normal page editor where it is prose (About's
"The company" text, Services and Contact intros via Settings). The structured
sections are built from the records and Settings.

## The quote form

Posts to `admin-post.php`. Every submission passes a nonce, a honeypot, a
per-address rate limit (6/minute) and — once both keys are saved in Settings —
reCAPTCHA v2. A rejected submission returns to the form with the message and
everything the visitor typed. The office is emailed (Reply-To set to the
sender), and the sender gets an acknowledgement unless that is switched off
in Settings. `?type=home` pre-selects a project type; `?project=<id>` records
which project the visitor was looking at.

Mail goes through `wp_mail()`. On shared hosting install an SMTP plugin (WP
Mail SMTP or similar) so the notifications reliably arrive.

## Photos

The prototype's photographs ship inside the theme (`assets/images/`, WebP at
800 and 1600 px). Every record and image setting falls back to them, so the
site is complete on first activation; once a project has photos chosen in
its gallery, or a service a featured image, or a setting an image, the
bundled one is ignored. The Unsplash stock photos are listed in the
prototype's README and should be replaced with company photography.

## Deploying

Copy `wp-content/themes/glorious` and `wp-content/plugins/gcc-core` into the
host's `wp-content`, activate both, and fill in Settings. Set `WP_DEBUG` off.
Shared hosting is fine: nothing here needs a queue, cron or a build step.

## Zips for uploading

`./package.sh` writes `dist/glorious.zip` (theme) and `dist/gcc-core.zip`
(plugin), each unpacking to a single folder as wp-admin expects. Upload the
plugin first (Plugins → Add New → Upload) and activate it — that creates the
pages, the menu and the content — then the theme (Appearance → Themes →
Upload). `dist/` is not committed.
