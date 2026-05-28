# Cell Background plugin for DokuWiki — local fork

Allows user-defined background colors in table cells via the `@color:` prefix.

```
^ Header 1 ^ Header 2 ^
| @yellow:Row 1 | text |
| @#6495ed:Row 2 | text |
| @red:Row 3 | text |
```

Original plugin: [github.com/dr4Ke/cellbg](https://github.com/dr4Ke/cellbg). This is a local fork tracking upstream `b29e555`.

## What changed in the local fork

| Change | Why |
| --- | --- |
| HTML output uses `style="background-color: X"` instead of the HTML4 `bgcolor=X` attribute | `bgcolor` was dropped in HTML5. Browsers still render it but it's invalid markup. |
| Removed legacy `if (!defined('DOKU_INC')) define(...); require_once(DOKU_PLUGIN.'syntax.php')` lines | Modern DokuWiki autoloads the base class. The require_once produces deprecation warnings on PHP 8.x. |
| Removed the hand-coded `getInfo()` method | The base `PluginTrait::getInfo()` reads metadata from `plugin.info.txt`, which is the modern convention. |
| `plugin.info.txt` `date` field set to `2077-10-09` | Suppresses the **Update** button in the Extension Manager. |
| `syntax.php` extends `\dokuwiki\Extension\SyntaxPlugin` directly | Namespace class is stable in Librarian; eliminates reliance on the legacy alias. |
| `script.js` rewritten against the modern DokuWiki toolbar API | The original used `showPicker()`, `jsEscape()`, and the `spell__action` element — all removed from DokuWiki. The picker was silently broken. The rewrite uses the `toolbar[]` array + `picker` type, no `eval()`. Picker buttons are styled with their actual background colour. |

## Toolbar colour picker

The plugin contributes a colour-picker button to the DokuWiki editor toolbar. Each button in the picker is filled with its colour so you can identify it at a glance. Clicking a button inserts the corresponding `@color:` token at the cursor.

To add extra colours, define `window.user_cellbg_colors` as an array of `{label, value}` objects before `script.js` loads (e.g. in your template's custom JS file):

```js
window.user_cellbg_colors = [
    { label: 'Navy', value: '#001f3f' },
    { label: 'Coral', value: 'coral' }
];
```

## Install

Drop the folder into `lib/plugins/cellbg/`, or use Admin → Extension Manager → Manual Install to upload the zip.

## Restoring upstream

The changes are confined to `syntax.php` (output attribute + legacy line removal) and `plugin.info.txt` (date field). Pages do not need editing — wiki syntax is unchanged. `script.js` is a complete rewrite of the old file.

## Compatibility

Tested on DokuWiki `2025-05-14b "Librarian"`. Composes correctly with the local `sortablejs` and `searchtablejs` forks.

## License

GPL 2, matching the original plugin.
