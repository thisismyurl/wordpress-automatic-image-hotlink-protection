# WordPress Automatic Image Hotlink Protection

Stops other sites from hotlinking your WordPress images. On activation, adds a clean `.htaccess` block that returns `403 Forbidden` for image requests from foreign domains — no configuration required.

[![WordPress Plugin](https://img.shields.io/badge/WordPress-6.0%2B-blue)](https://wordpress.org/plugins/wordpress-automatic-image-hotlink-protection/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4)](https://php.net/)
[![License: GPL v2](https://img.shields.io/badge/License-GPLv2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

## How It Works

On activation, the plugin calls WordPress's built-in `insert_with_markers()` to append hotlink-protection rewrite rules to your `.htaccess`. Direct visits (empty Referer) and your own domain are always allowed; all other domains receive a `403` when requesting image files.

On deactivation the rules are removed cleanly — no residue in `.htaccess`.

## Requirements

- WordPress 6.0+, PHP 7.4+
- Apache with `mod_rewrite` enabled (nginx sites don't use `.htaccess`)

## Installation

1. Upload to `/wp-content/plugins/wordpress-automatic-image-hotlink-protection/`.
2. Activate in **Plugins › Installed Plugins**.
3. Done. No settings page.

## Supported file types

`jpg`, `jpeg`, `png`, `gif`, `webp`, `svg`, `ico`

## Support

[GitHub Issues](https://github.com/thisismyurl/wordpress-automatic-image-hotlink-protection/issues) · [WordPress.org forum](https://wordpress.org/support/plugin/wordpress-automatic-image-hotlink-protection/)

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).

---

**Author:** [Christopher Ross](https://thisismyurl.com) — WordPress specialist since 2007.  
**Sponsor:** https://github.com/sponsors/thisismyurl


---
*This project follows the [10 Core Pillars](PILLARS.md). Support quality work [here](https://github.com/sponsors/thisismyurl).*

