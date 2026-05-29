# WordPress Automatic Image Hotlink Protection

Stops other sites from hotlinking the images in your uploads directory. On activation, adds a clean `.htaccess` block that returns `403 Forbidden` for image requests in `/wp-content/uploads/` from foreign domains — no configuration required. Apache (and Apache-compatible servers such as LiteSpeed) only.

[![WordPress Plugin](https://img.shields.io/badge/WordPress-6.0%2B-blue)](https://wordpress.org/plugins/wordpress-automatic-image-hotlink-protection/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4)](https://php.net/)
[![License: GPL v2](https://img.shields.io/badge/License-GPLv2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

## How It Works

On activation, the plugin calls WordPress's built-in `insert_with_markers()` to append hotlink-protection rewrite rules to your `.htaccess`. The rules are scoped to `/wp-content/uploads/`, so theme, plugin, and block-editor assets are never affected. Direct visits (empty Referer) and any subdomain of your own registrable domain (`www.`, `cdn.`, `shop.`, `staging.`) are always allowed; all other domains receive a `403` when requesting protected image files.

On non-Apache servers (nginx, Caddy, IIS) the plugin writes nothing — those servers ignore `.htaccess` — and shows an admin notice pointing you to server/edge configuration instead. If the `.htaccess` is not writable, the plugin surfaces a real failure notice rather than reporting false success.

On deactivation the rules are removed cleanly — no residue in `.htaccess`.

## Requirements

- WordPress 6.0+, PHP 7.4+
- Apache (or Apache-compatible, e.g. LiteSpeed) with `mod_rewrite` enabled. nginx, Caddy, and IIS do not read `.htaccess`.

## Installation

1. Upload to `/wp-content/plugins/wordpress-automatic-image-hotlink-protection/`.
2. Activate in **Plugins › Installed Plugins**.
3. Done. Check status any time at **Settings › Hotlink Protection** (server type, writability, and whether protection is active).

## Supported file types

`jpg`, `jpeg`, `png`, `gif`, `webp`, `avif`

`svg` and `ico` are intentionally excluded — favicons and SVG sprites are commonly embedded cross-origin for legitimate reasons.

## Support

[GitHub Issues](https://github.com/thisismyurl/wordpress-automatic-image-hotlink-protection/issues) · [WordPress.org forum](https://wordpress.org/support/plugin/wordpress-automatic-image-hotlink-protection/)

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).

---

**Author:** [Christopher Ross](https://thisismyurl.com) — WordPress specialist since 2007.  
**Sponsor:** https://github.com/sponsors/thisismyurl


---
*This project follows the [10 Core Pillars](PILLARS.md). Support quality work [here](https://github.com/sponsors/thisismyurl).*

