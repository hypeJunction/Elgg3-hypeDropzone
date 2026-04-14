# hypedropzone — Architecture (Elgg 4.x)

## Summary

Drag-and-drop file upload plugin for Elgg. Ships three action controllers
for direct and chunked uploads, a custom `object.file_chunk` entity type
for reassembling chunks, a `DropzoneService` DI service that processes
incoming uploads, and an `input/dropzone` view that renders the client
widget backed by npm-asset dropzone 5.9.x.

Target version: **Elgg 4.x** (PHP 7.4+).

## Directory layout

```
hypedropzone/
├── classes/hypeJunction/
│   ├── DropzoneService.php         # DI service — handleUploads($request) entry point
│   └── Dropzone/
│       ├── FileChunk.php           # ElggFile subclass, object.file_chunk subtype
│       ├── UploadAction.php        # Direct upload action controller
│       ├── ChunkUploadAction.php   # Chunked upload receiver
│       └── ChunkAssembleAction.php # Chunk reassembly controller
├── views/default/
│   ├── input/dropzone.php          # Widget view — renders the dropzone <div> with data-attrs
│   └── dropzone/
│       ├── dropzone.css            # Plugin-local dropzone styles
│       └── (lib.js mapped to vendor/npm-asset/dropzone/.../dropzone-amd-module.min.js)
├── languages/
├── composer.json                   # Sole metadata source
├── elgg-plugin.php                 # Runtime config: entities, actions, settings, views, view_extensions
└── elgg-services.php               # DI container definition: 'dropzone' → DropzoneService
```

## elgg-plugin.php

| Key | Contents |
|-----|----------|
| `plugin.name` / `version` | `hypeDropzone` / `7.0.0` |
| `entities` | `object.file_chunk` → `FileChunk::class`, `searchable => false` |
| `actions` | `dropzone/upload` → `UploadAction`, `dropzone/upload_chunk` → `ChunkUploadAction`, `dropzone/assemble_chunks` → `ChunkAssembleAction` (each with its own controller class) |
| `settings` | `chunked_uploads => true` |
| `views` | `dropzone/lib.js` alias → `vendor/npm-asset/dropzone/dist/min/dropzone-amd-module.min.js`; `css/dropzone/stylesheet` → `views/default/dropzone/dropzone.css` |
| `view_extensions` | Appends `css/dropzone/stylesheet` to `elgg.css` and `admin.css` |

## elgg-services.php

```php
return [
    'dropzone' => \DI\create(\hypeJunction\DropzoneService::class),
];
```

Retrieved as `elgg()->dropzone` from anywhere in the app.

## Dependencies

- **Elgg core** `^4.0`
- **PHP** `>= 7.4`
- **npm-asset/dropzone** `>5.5.0` (installed into `mod/hypedropzone/vendor/` via the per-plugin docker install step; uses asset-packagist for the npm bridge)
- **composer/installers** `^2.0`

No Elgg plugin dependencies declared.

## Migration notes — 3.x → 4.x

- `manifest.xml` removed; `composer.json` is the sole metadata source.
- `extra.installer-name = hypeDropzone` dropped — the plugin directory is
  lowercase (`hypedropzone`) per Iron Law 6.
- Autoload switched from PSR-0 (`"": "classes/"`) to PSR-4
  (`hypeJunction\\: classes/hypeJunction/`).
- `elgg-plugin.php` no longer walks up the filesystem looking for
  `vendor/autoload.php` in an Elgg root — it just uses `__DIR__` now
  that the per-plugin docker stack always installs to the plugin root.
- `views/default/input/dropzone.php` migrated
  `elgg_format_attributes($options)` (removed in 4.x) →
  `_elgg_services()->html_formatter->formatAttributes($options)`.
- The per-plugin `docker/elgg-install.sh` now runs
  `composer install -d mod/hypedropzone --no-dev` before plugin
  activation so the `vendor/npm-asset/dropzone/...` paths resolve at
  page render time.
- Pre-migration PHPUnit tests had to be adapted:
  - Plugin id lowercased at every `elgg_get_plugin_from_id` /
    `elgg_get_plugin_setting` call site.
  - `FileChunkTest::testFileChunkPersists` wraps the save path in
    `elgg_call(ELGG_IGNORE_ACCESS, ...)` to match how the upload
    pipeline actually writes chunks.
  - `DropzoneServiceTest` constructs `\Elgg\Request` via
    `new \Elgg\Request(elgg(), \Elgg\Http\Request::createFromGlobals())`
    — `\Elgg\Request::createFromGlobals()` doesn't exist in 4.x.
- Pre-migration Playwright tests had to be adapted:
  - `baseURL` moved inside the `use:` block of `playwright.config.ts`
    (top-level `baseURL` is silently ignored).
  - `loginAs` helper scopes to `form.elgg-form-login` with `.last()` to
    skip the hidden topbar copy of the login form in the 4.x default theme.
  - Unauthenticated action tests pass `maxRedirects: 0` so the expected
    302 to `/login` surfaces instead of being swallowed by redirect
    following.
- `@playwright/test` pinned to `1.49.0` exactly (matches the docker
  image `mcr.microsoft.com/playwright:v1.49.0-noble`).

## Known issues / follow-ups

- `--security` reports two warnings (not critical):
  - `ChunkAssembleAction.php:57` — `file_get_contents` with a variable
    path. The path comes from `ElggFile::getFilenameOnFilestore()`, which
    is the filestore's own sanitized path — not user input. False positive.
  - `input/dropzone.php:16` — `md5(microtime() . rand())` for a DOM id.
    Non-security use; not worth fixing during the migration.
