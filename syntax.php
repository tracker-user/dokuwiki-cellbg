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

if (!defined('DOKU_INC')) die();

class syntax_plugin_cellbg extends \dokuwiki\Extension\SyntaxPlugin
{
    /**
     * @return string Syntax type — this plugin applies inline formatting.
     */
    public function getType()
    {
        return 'formatting';
    }

    /**
     * @return array Markup types that may be nested inside this plugin's own markup.
     */
    public function getAllowedTypes()
    {
        return ['formatting', 'substition', 'disabled'];
    }

    /**
     * @return string Paragraph handling — 'normal' means usable inside paragraphs.
     */
    public function getPType()
    {
        return 'normal';
    }

    /**
     * @return int Sort order relative to other syntax plugins.
     */
    public function getSort()
    {
        return 200;
    }

    /**
     * Register the `@color:` pattern with the lexer.
     *
     * The lookahead ensures we only match inside a table cell (text must be
     * followed by `|` then optional whitespace then end-of-line).
     *
     * @param string $mode Current lexer mode.
     * @return void
     */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern(
            '^@#?[0-9a-zA-Z]*:(?=[^\n]*\|[[:space:]]*\n)',
            $mode,
            'plugin_cellbg'
        );
    }

    /**
     * Extract the color from the matched token.
     *
     * @param string       $match   The text matched by the lexer.
     * @param int          $state   The lexer state (always DOKU_LEXER_SPECIAL here).
     * @param int          $pos     Character position of the match.
     * @param Doku_Handler $handler The parser handler.
     * @return array [$state, $color, $match]
     */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        if ($state !== DOKU_LEXER_SPECIAL) {
            return [$state, 'yellow', $match];
        }

        preg_match('/@([^:]*)/', $match, $color);
        if ($this->isValidColor($color[1])) {
            return [$state, $color[1], $match];
        }
        return [$state, 'yellow', $match];
    }

    /**
     * Inject the background color into the already-emitted `<td>` tag.
     *
     * The renderer has already written the opening `<td...>` for the current
     * cell. We find that unclosed tag at the tail of `$renderer->doc` and
     * replace its `>` with ` style="background-color: X;">`. If the tag is
     * not found (e.g. `@color:` appeared outside a table) the literal match
     * text is emitted instead so the user sees their input.
     *
     * @param string        $mode     Output format (only 'xhtml' is handled).
     * @param Doku_Renderer $renderer The active renderer.
     * @param array         $data     [$state, $color, $text] from handle().
     * @return bool True on success, false if the format is not supported.
     */
    public function render($mode, Doku_Renderer $renderer, $data)
    {
        if ($mode !== 'xhtml') return false;

        [$state, $color, $text] = $data;
        if ($state !== DOKU_LEXER_SPECIAL) return true;

        if (preg_match('/(<td[^<>]*)>[[:space:]]*$/', $renderer->doc)) {
            // Inject inline style (HTML5) rather than the deprecated HTML4 bgcolor
            // attribute. $color is validated by isValidColor() so it is safe inside
            // a style attribute; hsc() is applied as an additional defensive layer.
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
     * Validate a CSS color value against the three formats accepted as input.
     *
     * Not a full CSS color parser — just enough to guarantee that nothing
     * harmful (quotes, semicolons, JS) can appear inside the style attribute.
     * Accepted formats: named color (`red`), hex (`#fff` / `#ffffff`),
     * rgb triplet (`rgb(255,255,255)` with optional `%`).
     *
     * @param string $c Raw color string from the wiki markup.
     * @return bool True if the value is safe to embed in a CSS style attribute.
     */
    protected function isValidColor($c)
    {
        $c = trim($c);

        $pattern = '/
            (^[a-zA-Z]+$)|                                # named color
            (^\#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$)|        # hex color
            (^rgb\(([0-9]{1,3}%?,){2}[0-9]{1,3}%?\)$)     # rgb() triplet
            /x';

        return (bool)preg_match($pattern, $c);
    }
}
