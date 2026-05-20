<?php
/**
 * Cell Background plugin for DokuWiki — local fork.
 *
 * Allows user-defined background colors in table cells via the `@color:` prefix
 * inside table syntax. Original plugin: https://github.com/dr4Ke/cellbg
 *
 * Local modifications vs. upstream (b29e555, 2013-10-09):
 *   1. Removed the legacy DOKU_INC / DOKU_PLUGIN re-declarations and the
 *      `require_once(DOKU_PLUGIN.'syntax.php')` line. Modern DokuWiki autoloads
 *      the base class, the constants are defined in init.php before any plugin
 *      is loaded, and the require_once produces deprecation warnings on PHP 8.x.
 *   2. Switched output from the deprecated HTML4 `bgcolor=X` attribute to the
 *      HTML5-compliant `style="background-color:X"`. Visual result is
 *      identical under default templates; only matters if your template has
 *      CSS targeting `td[bgcolor]` selectors (rare).
 *   3. Removed the legacy `getInfo()` method. Plugin metadata now comes from
 *      plugin.info.txt via the modern PluginTrait::getInfo() base implementation.
 *
 * Derived from the highlight plugin: http://www.dokuwiki.org/plugin:highlight
 * and: http://www.staddle.net/wiki/plugins/highlight
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  dr4Ke <dr4ke@dr4ke.net> (original)
 */

class syntax_plugin_cellbg extends DokuWiki_Syntax_Plugin
{
    // What kind of syntax are we?
    public function getType()
    {
        return 'formatting';
    }

    // What kind of syntax do we allow (optional)
    public function getAllowedTypes()
    {
        return ['formatting', 'substition', 'disabled'];
    }

    // What about paragraphs? (optional)
    public function getPType()
    {
        return 'normal';
    }

    // Where to sort in?
    public function getSort()
    {
        return 200;
    }

    // Connect pattern to lexer
    public function connectTo($mode)
    {
        // The lookahead `(?=...|\s*\n)` ensures we only match `@color:` when it
        // appears inside a table cell (text up to the next `|` then EOL).
        $this->Lexer->addSpecialPattern(
            '^@#?[0-9a-zA-Z]*:(?=[^\n]*\|[[:space:]]*\n)',
            $mode,
            'plugin_cellbg'
        );
    }

    public function postConnect()
    {
        // No exit pattern needed (special pattern, not entry/exit pair).
    }

    // Handle the match
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        if ($state !== DOKU_LEXER_SPECIAL) {
            return [$state, 'yellow', $match];
        }

        // Extract the color name from `@<color>:`. If empty (`@:`), default to yellow.
        preg_match('/@([^:]*)/', $match, $color);
        if ($this->isValidColor($color[1])) {
            return [$state, $color[1], $match];
        }
        return [$state, 'yellow', $match];
    }

    // Create output
    public function render($mode, Doku_Renderer $renderer, $data)
    {
        if ($mode !== 'xhtml') return false;

        [$state, $color, $text] = $data;
        if ($state !== DOKU_LEXER_SPECIAL) return true;

        // The renderer has already emitted everything up to and including the
        // opening <td...> of the current cell at this point. We detect that
        // unclosed <td> at the tail of $renderer->doc and inject a style
        // attribute into it. If we're NOT inside a <td> (e.g. the @color:
        // pattern matched outside a table) fall back to emitting the literal
        // text so the user sees their input rather than nothing.
        if (preg_match('/(<td[^<>]*)>[[:space:]]*$/', $renderer->doc)) {
            // Use inline style (HTML5) instead of the deprecated HTML4 bgcolor
            // attribute. $color is validated by isValidColor() so it cannot
            // break out of the style attribute; we still hsc() defensively.
            $renderer->doc = preg_replace(
                '/(<td[^<>]*)>[[:space:]]*$/',
                '\1 style="background-color: ' . hsc($color) . ';">',
                $renderer->doc
            );
        } else {
            $renderer->doc .= $text;
        }
        return true;
    }

    /**
     * Lightweight validation of a CSS color value. Not a full CSS color parser;
     * just enough to ensure nothing harmful (e.g. quotes, semicolons, JS) can
     * appear inside the style attribute.
     *
     * Three accepted formats: named color (`red`), hex (`#fff` or `#ffffff`),
     * rgb triplet (`rgb(255,255,255)` with optional `%`).
     */
    protected function isValidColor($c)
    {
        $c = trim($c);

        $pattern = '/
            (^[a-zA-Z]+$)|                                # named color (not verified against the spec)
            (^\#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$)|        # hex color value
            (^rgb\(([0-9]{1,3}%?,){2}[0-9]{1,3}%?\)$)     # rgb() triplet
            /x';

        return (bool)preg_match($pattern, $c);
    }
}
