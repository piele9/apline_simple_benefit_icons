# CLAUDE.md — APLINE Simple Benefit Icons (module-specific)

Module-specific notes for AI assistants. **Read the workspace-level
`CLAUDE.md` first** — that one has the naming contract template, hard
invariants (crash-safety, license, image-upload hardening), the
build/ship recipe, install pitfalls, and the family-wide guards. This
file only adds what is unique to this module.

> **Workspace-level rules**: `../../CLAUDE.md` (two levels up:
> `APLINE Modules for PrestaShop 9/CLAUDE.md`). When this module is
> cloned standalone (e.g. from GitHub), that file is not available —
> assume the SIMPLE family conventions and ask Arek to confirm if in
> doubt.

---

## 1. Concrete name mapping

For the universal template in `../../CLAUDE.md` §2, this module uses:

| Template placeholder | Concrete value |
|---|---|
| `<friendly>` | `Benefit Icons` |
| `<feature>` | `benefit_icons` |
| `<CamelFeature>` | `BenefitIcons` |
| `<feature-with-dashes>` | `benefit-icons` |
| `<abbrev>` | `asbi` |
| `<ABBREV>` | `ASBI` |
| `<Camelabbrev>` | `Asbi` |
| `<Ucfirst>` | `Aplinesimplebenefiticons` |

Which gives:

- Folder / main `.php` / class / `$this->name` → `apline_simple_benefit_icons`
- Display name → `APLINE Simple Benefit Icons for PrestaShop 9`
- ObjectModel → `AplineSimpleBenefitIconsItem` (file: `classes/AplineSimpleBenefitIconsItem.php`)
- AdminController → `AdminAplineSimpleBenefitIconsItemController` (file under `controllers/admin/`)
- Translation domain → `Modules.Aplinesimplebenefiticons.Admin`
- DB table / PK → `asbi_item` / `id_asbi_item`
- Configuration keys → `ASBI_HOOK`, `ASBI_TEXT_COLOR`
- Submit token → `submitAsbiConfig`
- Uploaded-file prefix → `asbi_`
- Stylesheet handle → `apline-simple-benefit-icons`
- Front CSS root / helpers → `.apline-simple-benefit-icons` / `.asbi-row` / `.asbi-icon[-entity]` / `.asbi-text`
- Smarty widget → `apline_simple_benefit_icons`
- Public repo → `https://github.com/piele9/apline_simple_benefit_icons`

---

## 2. Module-specific behavior

- **Hooks the block can render on** (defined in
  `getAvailableHooks()`):
  - `displayProductAdditionalInfo` — *Product page (reassurance area)* — **default**
  - `displayLeftColumn` — *Left column*
  - `displayRightColumn` — *Right column*
  - `displayFooterProduct` — *Product page footer*
- **Always-registered hook**: `actionFrontControllerSetMedia` (registers
  the front stylesheet).
- **Row identity field** — `text` is the only required column on
  `asbi_item`. `image` and `icon` are mutually exclusive in practice
  (template picks `image` first, falls back to `icon`); validation
  permits either alone, both, or neither (then only `text` renders).
- **Icon field accepts**: bare unicode hex (e.g. `1F69A`) or an HTML
  entity (e.g. `&#x1F69A;`). Normalized via
  `apline_simple_benefit_icons::normalizeIcon()` which adds the
  `&#x...;` wrapping if needed and validates against a whitelist regex.
- **Demo data**: `install()` seeds 3 icon rows (🚚 Free delivery,
  ⏱ Same day shipping, ↩ 30 day return). These are real `INSERT`s
  into `asbi_item` — uninstalling deletes them along with everything else.

---

## 3. Module-specific test plan

Run after every non-trivial change. There are no automated tests yet.

### 3.1 Install / uninstall
1. Upload `apline_simple_benefit_icons.zip` via BO → *Modules → Module
   Manager → Upload a module*.
2. Confirm install succeeds and 3 demo rows appear under
   *Configure → Manage rows*.
3. Verify the front-end block renders on a product page (default hook
   is `displayProductAdditionalInfo`).
4. Uninstall from Module Manager. Verify:
   - the `asbi_item` table is dropped
     (`SHOW TABLES LIKE 'ps_asbi_item'`)
   - `ASBI_HOOK` and `ASBI_TEXT_COLOR` removed from `ps_configuration`
   - `views/img/asbi_*` files removed
   - the hidden admin Tab is gone (no orphan in `ps_tab` for
     `AdminAplineSimpleBenefitIconsItem`)

### 3.2 Row management
5. Add a row with an **image** (`.png` ≤ 2 MB). Confirm it renders.
6. Add a row with an **icon** (paste `1F69A`). Confirm the 🚚 glyph
   renders next to the text.
7. Add a row with a **URL** + *new tab* on. Confirm the whole row is
   a link with `target="_blank" rel="noopener noreferrer"`.
8. Drag-and-drop to reorder. Reload — order persists.
9. Toggle *Displayed* off on a row. Verify it vanishes from the front.

### 3.3 Validation (must REJECT, never silently truncate)
10. Text > 255 chars → form re-opens with an error, nothing saved.
11. Invalid URL (`not-a-url`) → form error.
12. Icon `zzz` (not hex / not entity) → form error.
13. Upload a renamed `.exe` with `.png` extension → form error ("not a
    valid image"), no file written to `views/img/`.
14. Upload a 3 MB file → form error ("too large").

### 3.4 Configuration form
15. Switch *Display location* to *Left column*, save. Verify the block
    moves to the left column and disappears from the product page.
16. Change *Text color*, save. Verify the inline `style="color:..."` on
    `.asbi-text` matches.
17. Configure block also reachable via Smarty:
    `{widget name='apline_simple_benefit_icons'}` in any theme
    template.

### 3.5 Crash-safety
18. Manually break the table
    (`RENAME TABLE ps_asbi_item TO ps_asbi_item_x`) and reload a
    product page. The page must render normally with an empty block
    (no 500). Restore the table after.

---

## 4. Code layout

```
apline_simple_benefit_icons/
├── apline_simple_benefit_icons.php         Module class. install/uninstall,
│                                            hooks, getContent, widget API,
│                                            attribution renderers.
├── classes/
│   └── AplineSimpleBenefitIconsItem.php    ObjectModel for `asbi_item`.
├── controllers/admin/
│   └── AdminAplineSimpleBenefitIconsItemController.php
│                                            Rows list, edit form, image
│                                            upload validation, drag&drop
│                                            position ajax.
├── views/
│   ├── css/front.css                       Front-end styles.
│   ├── img/                                Uploaded images (prefixed asbi_).
│   └── templates/
│       ├── admin/configure.tpl             "Manage rows" entry in getContent.
│       └── hook/block.tpl                  Front-end render.
├── docs/                                   Screenshots used by README.
├── README.md                               User-facing docs.
├── CLAUDE.md                               This file.
├── CHANGELOG.md                            Versioned history.
└── LICENSE.md                              Custom Attribution License v1.0.
```

All `index.php` files in subfolders are PrestaShop security stubs
(directory-listing guard). They contain nothing module-specific.
