# Archived — 2026-05-02

This repository is archived. Read-only.

## Why

The `.htaccess` rules this plugin wrote on activation were structurally broken — they forced HTTPS and HTTP simultaneously and rewrote all requests through `index.php`, which can produce a redirect loop on any site with an existing `.htaccess`. The plugin was inherited rather than authored, and the cost of a clean rewrite outweighed the value of keeping it in the catalogue.

## What to do instead

Hotlink protection in 2026 is best handled at the edge or in your web server config:

- **Cloudflare:** [Hotlink Protection](https://developers.cloudflare.com/waf/tools/scrape-shield/hotlink-protection/) (one-click, recommended for most sites).
- **Nginx:** match `Referer` in a `location ~* \.(jpe?g|png|gif|webp)$` block.
- **Apache:** a properly written `RewriteCond %{HTTP_REFERER}` + `RewriteRule .*\.(jpe?g|png|gif|webp)$ - [F]` pair, scoped to the upload directory only.

— Christopher Ross / [thisismyurl.com](https://thisismyurl.com/)
