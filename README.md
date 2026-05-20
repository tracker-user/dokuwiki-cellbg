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
| HTML output uses `style="background-color: X"` instead of the HTML4 `bgcolor=X` attribute | `bgcolor` was dropped in HTML5. Browsers still render it but it's invalid markup. Templates that target `td[bgcolor]` in CSS would need a one-line change to target `td[style*="background-color"]` instead — most templates target by class, not attribute, so no change is needed in practice. |
| Removed legacy `if (!defined('DOKU_INC')) define(...); require_once(DOKU_PLUGIN.'syntax.php')` lines | Modern DokuWiki autoloads the base class. The constants are set in `init.php` before any plugin loads. The `require_once` produces a deprecation warning on PHP 8.x. |
| Removed the hand-coded `getInfo()` method | The base `PluginTrait::getInfo()` reads metadata from `plugin.info.txt`, which is the modern convention. Hand-coded `getInfo()` would shadow our `plugin.info.txt` updates. |
| `plugin.info.txt` `date` field set to `2077-10-09` | Suppresses the **Update** button in the Extension Manager so another admin can't accidentally overwrite this fork with the unmodified upstream source. The Extension Manager compares the installed `date` against dokuwiki.org's `lastupdate` and only shows Update when the installed value is older. |

The two PRs that change `&$handler` to `Doku_Handler $handler` are already merged in upstream `b29e555` and present in this fork.

## Install

Drop the folder into `lib/plugins/cellbg/`, or use Admin → Extension Manager → Manual Install to upload the zip.

## Restoring upstream

`git diff` against the upstream `b29e555` source will show exactly the changes above. Worst case, the deltas are confined to `syntax.php` (output attribute change + legacy line removal) and `plugin.info.txt` (date field). To restore upstream behavior: revert those two files. Pages don't need editing — wiki syntax is unchanged.

## Compatibility

Tested on DokuWiki `2025-05-14b "Librarian"`. Composes correctly with the local `sortablejs` and `searchtablejs` forks (cellbg colors apply inside both wrappers — verified by the rendering test suite).

## License

GPL 2, matching the original plugin.
