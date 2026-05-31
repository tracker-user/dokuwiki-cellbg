/**
 * Cell Background — editor toolbar picker.
 *
 * Auto-bundled by DokuWiki as part of the global JS bundle. The toolbar[]
 * push is safe on all pages — initToolbar() no-ops when #tool__bar is absent.
 *
 * Adds a colour-picker button to the DokuWiki toolbar that inserts @color:
 * tokens at the cursor position.
 */

(function () {
    'use strict';

    var colors = [
        { label: 'Yellow',     value: 'yellow'      },
        { label: 'Red',        value: 'red'          },
        { label: 'Orange',     value: 'orange'       },
        { label: 'Salmon',     value: 'salmon'       },
        { label: 'Pink',       value: 'pink'         },
        { label: 'Plum',       value: 'plum'         },
        { label: 'Purple',     value: 'purple'       },
        { label: 'Fuchsia',    value: 'fuchsia'      },
        { label: 'Silver',     value: 'silver'       },
        { label: 'Aqua',       value: 'aqua'         },
        { label: 'Teal',       value: 'teal'         },
        { label: 'Cornflower', value: '#6495ed'      },
        { label: 'Sky Blue',   value: 'skyblue'      },
        { label: 'Aquamarine', value: 'aquamarine'   },
        { label: 'Pale Green', value: 'palegreen'    },
        { label: 'Lime',       value: 'lime'         },
        { label: 'Green',      value: 'green'        },
        { label: 'Olive',      value: 'olive'        }
    ];

    /* Build the list DokuWiki's picker mechanism expects: text items that get
       inserted verbatim into the editor textarea on click. */
    var pickerList = [];
    var i;
    for (i = 0; i < colors.length; i++) {
        pickerList.push('@' + colors[i].value + ':');
    }

    /* Merge in any site-specific extra colours (defined as window.user_cellbg_colors
       = [{label:'...', value:'...'}] in a wiki template or userstyle script). */
    if (typeof window.user_cellbg_colors !== 'undefined') {
        var extra = window.user_cellbg_colors;
        for (i = 0; i < extra.length; i++) {
            colors.push(extra[i]);
            pickerList.push('@' + extra[i].value + ':');
        }
    }

    /* Resolve the localized toolbar title from the JS lang bundle.
       LANG.plugins.cellbg.toolbar_title is populated from $lang['js']['toolbar_title']
       in lang/<iso>/lang.php; fall back to English if the bundle is missing. */
    var toolbarTitle = (typeof window.LANG !== 'undefined' &&
        LANG.plugins && LANG.plugins.cellbg &&
        LANG.plugins.cellbg.toolbar_title) || 'Cell background';

    /* Register the toolbar button before initToolbar() fires. */
    if (typeof window.toolbar !== 'undefined') {
        toolbar.push({
            type:    'picker',
            title:   toolbarTitle,
            icon:    '../../plugins/cellbg/images/cellbg.png',
            list:    pickerList,
            'class': 'cellbg_picker'
        });
    }

    /* After the toolbar has been built, add background-colour styling to each
       picker button so the colour is visible rather than just the @label: text. */
    jQuery(function () {
        var $picker = jQuery('.cellbg_picker');
        if (!$picker.length) return;

        $picker.find('.pickerbutton').each(function (idx) {
            var entry = colors[idx];
            if (!entry) return;
            jQuery(this)
                .css({
                    'background':  entry.value,
                    'min-width':   '2em',
                    'height':      '2em',
                    'margin':      '3px',
                    'font-size':   '0.7em',
                    'line-height': '2em'
                })
                .text('');  /* hide the @label: text — the colour IS the label */
        });
    });

}());
