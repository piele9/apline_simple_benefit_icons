# APLINE Simple Benefit Icons for PrestaShop 9

A lightweight, distributable PrestaShop **9.0.x** module that displays a
configurable block of **benefit rows** (image **or** HTML‑entity icon + text,
with an optional clickable link) on the product page — similar to the native
*Customer Reassurance* block, but simpler, single‑language and dependency‑free
(classic PrestaShop API, no front build step, no Vue/webpack).

> Created by **[APLINE](https://apline.pl)** — custom PrestaShop development,
> performance optimization and integrations.

---

## ✨ Features

- ✅ Rows of **image** *or* **icon** (unicode hex / HTML entity) + **text**
- ✅ Optional **URL** — the whole row becomes a link, with a per‑row
  *open in new tab* switch (`target="_blank" rel="noopener noreferrer"`)
- ✅ **Drag & drop** ordering, enable/disable per row
- ✅ Configurable display location: product reassurance area, left/right
  column, product footer — or anywhere via `{widget name='apline_simple_benefit_icons'}`
- ✅ Strict, English‑only validation:
  - required fields, 255‑char limit (rejected, never silently truncated)
  - URL format check
  - image upload hardened: **JPG / PNG / WEBP only**, real MIME inspection
    (not just the extension), **2 MB** size cap → blocks disguised executables
  - `alt` required when an image is set (auto‑filled from the file name)
- ✅ **Crash‑safe**: a rendering/data error yields an empty block, never a 500;
  a failed install rolls back to a clean state
- ✅ No DRM, no telemetry
- ✅ Public GitHub, custom attribution license, modifiable
- ✅ Released for **PrestaShop 9**

## 📦 Requirements

- PrestaShop **9.0.x** (tested on 9.0; not supported on 1.7 / 8.x — the
  module's `ps_versions_compliancy` blocks installation outside 9.0.x)
- PHP compatible with your PrestaShop 9 install
- Writable `views/img/` directory (for image uploads)

> Always test on a staging copy of your shop before installing on
> production. The module is crash-safe by design (a render error yields
> an empty block, never a 500), but every shop's theme and module mix
> is different.

## 🚀 Installation

**Via Back Office**

1. Zip the `apline_simple_benefit_icons/` folder (the archive must contain the
   folder at its root, with forward‑slash paths). The folder name, the main
   `.php` file name and the PHP class name MUST all be `apline_simple_benefit_icons`
   — otherwise PrestaShop refuses the zip with "This file doesn't seem to be a
   valid zip module".
2. *Modules → Upload a module* → select the ZIP → install.

**Via FTP**

1. Upload the `apline_simple_benefit_icons/` folder to `modules/`.
2. *Modules* → find **APLINE Simple Benefit Icons for PrestaShop 9** → Install.

On install, a demo set of 3 icon rows is created so you can see it working
immediately.

## 🧹 Uninstall

**From Back Office** (recommended): *Modules → Module Manager → find
**APLINE Simple Benefit Icons for PrestaShop 9** → Uninstall*.

Uninstall is **destructive and idempotent**:

- the `ps_asbi_item` table is dropped — all rows are deleted
- all uploaded images in `views/img/asbi_*` are removed from disk
- the `ASBI_HOOK` and `ASBI_TEXT_COLOR` configuration entries are removed
- the hidden admin tab (`AdminAplineSimpleBenefitIconsItem`) is removed
- module hook registrations are unregistered

If you want to keep your rows, **back up the `ps_asbi_item` table and the
`views/img/` folder before uninstalling**. There is no built-in export.

Deleting the `apline_simple_benefit_icons/` folder via FTP without
running the BO Uninstall first leaves orphan rows in `ps_configuration`,
`ps_tab`, `ps_hook_module` and the `ps_asbi_item` table behind — clean
those manually if you go that route.

## ⚙️ Usage

1. *Modules* → configure the module: pick the **display location** and the
   **text color**.
2. **Manage rows** → add/edit rows (image or icon + text, optional URL,
   *new tab* and *displayed* switches), reorder by drag & drop.
3. Optionally embed anywhere in your theme:

   ```smarty
   {widget name='apline_simple_benefit_icons'}
   ```

### Icon field

Instead of uploading an image you can enter an icon as:

- a bare unicode hex code, e.g. `1F69A` (🚚), or
- an HTML entity, e.g. `&#x1F69A;`

The value is validated against a whitelist and rendered as the corresponding
glyph next to the text.

## 🖼️ Screenshots

**Module configuration page** — pick the display location and the text color:

![Module configuration page](docs/config.png)

**Rows management** — drag & drop ordering, enable/disable per row:

![Rows management list](docs/rows.png)

**Front-end** — the rendered block on the product page:

![Block on the product page](docs/front.png)

## 🛠️ Troubleshooting

### "This file doesn't seem to be a valid zip module"

PrestaShop's installer requires that the **folder name**, the **main
`.php` file name** and the **PHP class name** all match — and the zip
must contain that folder at its root with **forward-slash** paths.

- Re-download the official zip from the GitHub repository's
  *Releases* page; do not rezip the source folder with Windows Explorer
  (it sometimes writes `\` separators that PrestaShop rejects).
- If you must rebuild the zip yourself, on PowerShell 5.1 avoid
  `Compress-Archive` — see the build recipe in [CLAUDE.md](CLAUDE.md).

### Block does not appear on the product page

- *Modules → APLINE Simple Benefit Icons → Configure* — make sure the
  **Display location** dropdown is set to where you expect (default:
  *Product page (reassurance area)*).
- Make sure at least one row has the **Displayed** switch on.
- Some themes strip the `displayProductAdditionalInfo` hook. Try
  *Left column* or *Product page footer* instead, or embed the block
  manually with `{widget name='apline_simple_benefit_icons'}` in your
  theme template.
- Clear the PrestaShop cache (*Advanced Parameters → Performance →
  Clear cache*).

### Image upload fails / silent rejection

- The upload folder `modules/apline_simple_benefit_icons/views/img/`
  must be writable by PHP. A red warning on the configuration page
  signals it is not — fix the permissions (`chmod 0775` on Linux).
- Only **JPG / PNG / WEBP** files up to **2 MB** are accepted. The
  module inspects the real file content, not just the extension —
  renamed executables will be rejected as "not a valid image".
- The `Alt` field is required when an image is set (auto-filled from
  the file name if you leave it empty).

If none of the above explains your issue, open a GitHub Issue with
your PrestaShop version, PHP version, theme name, and the relevant
lines from `var/logs/`.

## 📝 License

Custom Attribution License v1.0 — see [LICENSE.md](LICENSE.md).

You may use, modify, distribute and ship this module commercially and in
client projects. You may **not** remove or hide the APLINE attribution link
on the module configuration page. The attribution must stay visible, link to
<https://apline.pl>, and use a readable font size (≥ 12px).

## 🏢 About APLINE

Need custom PrestaShop development, performance optimization or integrations?

→ **[APLINE.PL](https://apline.pl)**
