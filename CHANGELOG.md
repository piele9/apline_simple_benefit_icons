# Changelog

All notable changes to **APLINE Simple Benefit Icons for PrestaShop 9** will be
documented in this file. Format based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/)
and adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] – 2026-05-20

Initial public release.

### Added
- Configurable block of **benefit rows** (image **or** HTML‑entity icon + text,
  optional clickable link) for PrestaShop **9.0.x**.
- Configurable display location: product reassurance area, left/right column,
  product footer — or anywhere via `{widget name='apline_simple_benefit_icons'}`.
- Per‑row *open in new tab* switch (`target="_blank" rel="noopener noreferrer"`).
- Drag & drop ordering, enable/disable per row.
- Strict English‑only validation: required fields, 255‑char limit (rejected,
  never silently truncated), URL format check, icon whitelist (unicode hex /
  HTML entity), `alt` required when an image is set (auto‑filled from file name).
- Hardened image upload: JPG / PNG / WEBP only, real MIME inspection (not just
  the extension), 2 MB size cap → blocks disguised executables.
- Crash‑safe hooks and `WidgetInterface` rendering (`try/catch` → empty block,
  never a 500).
- Failed install rolls back to a clean state; uninstall is idempotent.
- *Back to configuration* breadcrumb button from the rows management list.
- APLINE attribution block on the configuration page **and** under the rows
  list, with a "Like this module?" call to action linking to https://apline.pl.
- Custom Attribution License v1.0 ([LICENSE.md](LICENSE.md)).

### Naming convention (SIMPLE family)
This module is the first of an **APLINE SIMPLE** family of PrestaShop modules.
The convention is:

- folder / main `.php` file / PHP class / `$this->name`: `apline_simple_<feature>` (all four MUST match — otherwise the back-office upload rejects the zip)
- DB table: `<abbrev>_item` (here: `asbi_item`)
- Configuration keys: `<ABBREV>_*` (here: `ASBI_*`)
- Translation domain: `Modules.Aplinesimple<feature>.Admin` (underscores stripped, ucfirst — here: `Modules.Aplinesimplebenefiticons.Admin`)
- Repository: `apline-simple-<feature>-prestashop`
