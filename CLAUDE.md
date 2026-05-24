# CLAUDE.md — APLINE Simple Benefit Icons for PrestaShop 9

Instructions for AI assistants working on this module. Read this first.
For everything not stated here, read the source — it is small and the
comments only explain non-obvious **why**.

---

## 1. Module identity (naming contract)

Every name below MUST stay in sync. PrestaShop's installer rejects the zip
with *"This file doesn't seem to be a valid zip module"* if folder ≠ main
file ≠ class ≠ `$this->name`. Any rename is a release-breaking change
(requires uninstall → reinstall on every shop already using the module),
not a cleanup.

| Element | Value |
|---|---|
| Folder, main `.php` file, PHP class, `$this->name` | `apline_simple_benefit_icons` |
| Display name | `APLINE Simple Benefit Icons for PrestaShop 9` |
| ObjectModel class / file | `AplineSimpleBenefitIconsItem` / `classes/AplineSimpleBenefitIconsItem.php` |
| Admin controller class / file | `AdminAplineSimpleBenefitIconsItemController` / `controllers/admin/AdminAplineSimpleBenefitIconsItemController.php` |
| Translation domain | `Modules.Aplinesimplebenefiticons.Admin` |
| DB table / primary key | `asbi_item` / `id_asbi_item` |
| Configuration keys | `ASBI_HOOK`, `ASBI_TEXT_COLOR` |
| Submit token | `submitAsbiConfig` |
| Uploaded-file prefix (in `views/img/`) | `asbi_` |
| Stylesheet handle | `apline-simple-benefit-icons` |
| Front CSS root / row helpers | `.apline-simple-benefit-icons` / `.asbi-row` / `.asbi-icon[-entity]` / `.asbi-text` |
| Smarty widget name | `apline_simple_benefit_icons` |

The translation domain follows the PrestaShop convention
`Modules.<ucfirst(strtolower(str_replace('_','',$name)))>.Admin`.

---

## 2. Hard invariants — do not break

### 2.1 Crash-safety
- Every hook method (`hook*`) and `renderWidget` MUST be wrapped in
  `try { ... } catch (\Throwable $e) { PrestaShopLogger::addLog(...); return ''; }`.
  A rendering or data error returns an empty block, **never** a 500.
- `install()` calls `installDb`, `installConfiguration`, `installHooks`,
  `installTab` and rolls back via `uninstall()` on any failure. Do not
  short-circuit this — a half-installed module is worse than a refused install.
- `uninstall()` must be idempotent (`DROP TABLE IF EXISTS`, guarded
  `Tab::getIdFromClassName` lookup, `@unlink`). Re-running it on an
  already-uninstalled module must not error.

### 2.2 License attribution (LICENSE.md, Custom Attribution License v1.0)
- `renderAplineFooter()` and `renderLikeBox()` MUST stay on:
  - the module configuration page (`getContent()`)
  - the rows management list (`renderList()` in the AdminController)
- The attribution is rendered server-side as plain HTML with the APLINE
  link to `https://apline.pl` and `font-size: 12px` minimum. Do not move
  it behind a feature flag, hide it via CSS, or refactor it to a Smarty
  block that could be themed away.

### 2.3 Image-upload hardening (security)
In the AdminController's `handleSubmission`, uploads MUST verify:
- extension is one of `ALLOWED_EXT` (`jpg`, `jpeg`, `png`, `webp`),
- byte size ≤ `MAX_IMG_BYTES` (2 MB),
- `getimagesize()` returns a real MIME from `ALLOWED_MIME`,
- when available, `ImageManager::isRealImage()` confirms the file.

Do **not** trust `$_FILES['image_file']['type']` alone — clients can
forge it. The combined check blocks executables renamed with an image
extension.

---

## 3. Build & ship — installable zip

Two trap-doors that already burned us once:
1. PrestaShop checks `<folder>/<folder>.php` exists. Folder = main file
   name = class name = `$this->name`.
2. The zip MUST use **forward-slash** entry names. PowerShell 5.1's
   `Compress-Archive` writes `\` on Windows and PrestaShop rejects it.

Use this exact PowerShell command (matches our known-good build):

```powershell
$base = "D:\Projekty\APLINE Modules for PrestaShop 9\APLINE Product Benefit Icons"
$src  = Join-Path $base "apline_simple_benefit_icons"
$zip  = Join-Path $base "apline_simple_benefit_icons.zip"

if (Test-Path $zip) { Remove-Item $zip -Force }

Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

$rootName = Split-Path $src -Leaf
$stream   = [System.IO.File]::Create($zip)
$archive  = New-Object System.IO.Compression.ZipArchive($stream, [System.IO.Compression.ZipArchiveMode]::Create)
try {
    Get-ChildItem -Path $src -Recurse -File | ForEach-Object {
        $rel       = $_.FullName.Substring($src.Length + 1) -replace '\\', '/'
        $entryName = $rootName + '/' + $rel
        $entry     = $archive.CreateEntry($entryName, [System.IO.Compression.CompressionLevel]::Optimal)
        $s = $entry.Open()
        try {
            $bytes = [System.IO.File]::ReadAllBytes($_.FullName)
            $s.Write($bytes, 0, $bytes.Length)
        } finally { $s.Dispose() }
    }
} finally {
    $archive.Dispose()
    $stream.Dispose()
}
```

Verify the result before shipping:

```powershell
Add-Type -AssemblyName System.IO.Compression.FileSystem
$a = [System.IO.Compression.ZipFile]::OpenRead($zip)
$a.Entries | Select-Object FullName, Length | Format-Table -AutoSize
$a.Dispose()
```

Every `FullName` must start with `apline_simple_benefit_icons/` (forward
slash) and there must be exactly one entry at
`apline_simple_benefit_icons/apline_simple_benefit_icons.php`.

---

## 4. Manual test plan (run after every non-trivial change)

There are no automated tests yet. The smoke test below catches every
regression we have hit so far.

### 4.1 Install / uninstall
1. Upload `apline_simple_benefit_icons.zip` via Back Office →
   *Modules → Module Manager → Upload a module*.
2. Confirm install succeeds and 3 demo rows appear under
   *Configure → Manage rows*.
3. Verify the front-end block renders on a product page (default hook
   is `displayProductAdditionalInfo`).
4. Uninstall from Module Manager. Verify:
   - the `asbi_item` table is dropped (`SHOW TABLES LIKE 'ps_asbi_item'`)
   - `ASBI_HOOK` and `ASBI_TEXT_COLOR` removed from `ps_configuration`
   - `views/img/asbi_*` files removed
   - the hidden admin Tab is gone (no orphan in `ps_tab`)

### 4.2 Row management
5. Add a row with an **image** (`.png` ≤ 2 MB). Confirm it renders.
6. Add a row with an **icon** (paste `1F69A`). Confirm the 🚚 glyph
   renders next to the text.
7. Add a row with a **URL** + *new tab* on. Confirm the whole row is
   a link with `target="_blank" rel="noopener noreferrer"`.
8. Drag-and-drop to reorder. Reload — order persists.
9. Toggle *Displayed* off on a row. Verify it vanishes from the front.

### 4.3 Validation (must REJECT, never silently truncate)
10. Text > 255 chars → form re-opens with an error, nothing saved.
11. Invalid URL (`not-a-url`) → form error.
12. Icon `zzz` (not hex / not entity) → form error.
13. Upload a renamed `.exe` with `.png` extension → form error
    ("not a valid image"), no file written to `views/img/`.
14. Upload a 3 MB file → form error ("too large").

### 4.4 Configuration form
15. Switch *Display location* to *Left column*, save. Verify the block
    moves to the left column and disappears from the product page.
16. Change *Text color*, save. Verify the inline `style="color:..."` on
    `.asbi-text` matches.
17. Configure block also reachable via Smarty:
    `{widget name='apline_simple_benefit_icons'}` in any theme template.

### 4.5 Crash-safety
18. Manually break the table (`RENAME TABLE ps_asbi_item TO ps_asbi_item_x`)
    and reload a product page. The page must render normally with an
    empty block (no 500). Restore the table after.

---

## 5. Do not change without consulting Arek

Each of these has a real cost on shops that already installed the module:

- **DB schema** (`asbi_item` columns, indexes, primary key name) —
  needs a versioned upgrade script in `upgrade/install-<old>-<new>.php`,
  not an in-place rename. No upgrade infrastructure exists yet.
- **Configuration key names** (`ASBI_HOOK`, `ASBI_TEXT_COLOR`) —
  renaming silently loses every shop's saved settings.
- **Uploaded-file prefix** (`asbi_`) — `deleteUploadedFiles()` greps by
  this prefix; changing it strands or wipes existing user uploads.
- **Hook names** in `getAvailableHooks()` — these are written into
  `ps_hook_module`; renaming a key orphans the registration.
- **`renderAplineFooter` / `renderLikeBox` visibility** — license guard,
  see §2.2.
- **PrestaShop version compatibility** (`ps_versions_compliancy`) —
  raising the floor blocks legitimate installs; lowering it requires
  retesting against the older runtime.

For any of these, surface the cost in plain English and wait for an
explicit "yes" before editing.

---

## 6. Code layout (quick reference)

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
(directory-listing guard). They contain nothing module-specific — leave
them alone.
