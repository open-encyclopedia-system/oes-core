<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_shortcode('oesnote', 'oes_note_shortcode');
add_shortcode('oesnote_list', 'oes_note_shortcode_list');


/**
 * Create the html representation of a note and prepare notes list.
 *
 * @param array $args Shortcode attributes (is mostly empty, unused).
 * @param string $content Content within the shortcode.
 *
 * @return string Return the html string representing a note.
 */
function oes_note_shortcode(array $args, string $content = ""): string
{
    /* get post id */
    global $oesNotes;
    $postID = $GLOBALS['post']->ID;

    /* check if pdf */
    $is_pdf = $args['pdf'] ?? false;

    /* get note number */
    $number = (!isset($oesNotes[$postID]) || count($oesNotes[$postID]['used_reference_numbers']) == 0) ?
        1 :
        max($oesNotes[$postID]['used_reference_numbers']) + 1;

    /* render content */
    $content = do_shortcode($content);

    /* replace paragraph characters */
    $content = str_replace(['<p>', '</p>'], ['', '<br /><br />'], $content);

    /* update class variable */
    if (!isset($oesNotes[$postID])) {

        if (!isset($GLOBALS['current_notes_post_number'])) $GLOBALS['current_notes_post_number'] = 0;

        $oesNotes[$postID] = [
            'notes_post_number' => $GLOBALS['current_notes_post_number'],
            'used_reference_numbers' => [$number],
            'notes' => [$number => $content]
        ];
        $GLOBALS['current_notes_post_number']++;
    } else {
        $oesNotes[$postID]['used_reference_numbers'][] = $number;
        $oesNotes[$postID]['notes'][$number] = $content;
    }

    /* replace note paragraphs */
    $content = str_replace(['<oesnotep>', '</oesnotep>'], ['', '<br/>'], $content);

    /* create note */
    $returnString = $is_pdf ?
        ('<span id="note' . $number . '" class="oes-pdf-note oes-pdf-link">[' . $number . ']</span>') :
        ('<sup id="note' . $number . '" class="oes-note" data-fn="' .
            $number . '" data-fn-post-scope="post_' . $postID . '"><a href="javascript:void(0)">' .
            $number . '</a></sup>');

    /* add note text */
    if(!$is_pdf) {

        /**
         * Filter the note content.
         *
         * @param string $content The note content.
         */
        if (has_filter('oes/note_content'))
            $content = apply_filters('oes/note_content', $content, $number);

        $returnString .= '<span class="oes-note__note" data-fn="' . $number . '">' . $content . '</span>';
    }

    return $returnString;
}


/**
 * Create the html representation of the note list.
 *
 * @param mixed $args Shortcode attributes (is mostly empty, unused).
 * @param string $content Content within the shortcode.
 *
 * @return string Return the html string representing the note list.
 */
function oes_note_shortcode_list($args, string $content = ""): string
{
    /* get global parameters */
    global $oesNotes;
    $postID = $GLOBALS['post']->ID;

    /* return early if no notes available */
    if (empty($oesNotes[$postID]) || !isset($oesNotes[$postID]['notes'])) return '';

    /* loop through notes */
    $is_pdf = $args['pdf'] ?? false;
    $content .= $args['header'] ?? '';
    $content .= $is_pdf ? '<div class="oes-notes-list oes-pdf-replace-list">' : '<ul class="oes-notes-list">';
    foreach ($oesNotes[$postID]['notes'] as $number => $note) {

        /* check for paragraphs */
        $modifiedNote = '';
        if (strpos($note, '<oesnotep>') || strpos($note, '<oesnotep>') === 0) {

            /* get text before and after paragraph */
            $prepareNote = preg_replace('/<oesnotep>/', 'OESSPLIT', $note, 1);
            $prepareNote = preg_replace('~</oesnotep>(?!.*</oesnotep>)~', 'OESSPLIT', $prepareNote);
            $prepareNoteSplit = preg_split('/(OESSPLIT)/', $prepareNote, null);

            /* should contain three items BEFORE PARAGRAPHS AFTER */
            if (sizeof($prepareNoteSplit) == 3) {

                /* before text */
                if (!empty($prepareNoteSplit[0])) $modifiedNote .= '<div>' . $prepareNoteSplit[0] . '</div>';

                /* paragraphs */
                if (!empty($prepareNoteSplit[1])) {

                    $noteColumns = [];
                    $i = 0;
                    $insidep = true;
                    $paragraphs = preg_split('/(<oesnotep>|<\/oesnotep>)/', $prepareNoteSplit[1], null, PREG_SPLIT_DELIM_CAPTURE);
                    if ($paragraphs) foreach ($paragraphs as $paragraph) {

                        if (empty($paragraph) || $paragraph == " ") continue;
                        elseif ($paragraph == "<oesnotep>") continue;
                        elseif ($paragraph == "</oesnotep>") continue;
                        elseif ($paragraph == "<br>") $i++;
                        else {
                            $noteColumns[$i][] = '<p>' . $paragraph . '</p>';
                            $i++;
                        }

                        /*if(empty($paragraph) || $paragraph == " ") continue;
                        elseif($paragraph == "<oesnotep>")$insidep = true;
                        elseif($paragraph == "</oesnotep>")$insidep = false;
                        elseif($paragraph == "<br>")$i++;
                        else{
                            if($insidep) $noteColumns[$i][] = '<p>' . $paragraph . '</p>';
                            else{
                                $i++;
                                $noteColumns[$i][] = '<p>' . $paragraph . '</p>';
                            }
                        }*/
                    }

                    /* put into columns */
                    if ($noteColumns) {
                        $modifiedNote .= '<div class="note-columns-' . sizeof($noteColumns) . ' row">';
                        foreach ($noteColumns as $noteContainer)
                            $modifiedNote .= '<div class="column">' . implode('', $noteContainer) . '</div>';
                        $modifiedNote .= '</div>';
                    }
                }

                /* after text */
                if (!empty($prepareNoteSplit[2])) $modifiedNote .= '<div>' . $prepareNoteSplit[2] . '</div>';
            }
        }

        $content .= $is_pdf ?
            sprintf('<div><div class="oes-pdf-note">%s</div><div class="oes-pdf-note-text %s">%s</div></div>',
                $number,
                'note' . intval($number),
                empty($modifiedNote) ? $note : $modifiedNote) :
            sprintf('<li id="oes-note-list-%s"><span><a href="#note%s">%s</a></span><div class="%s">%s</div></li>',
                $number,
                $number,
                $number,
                'note' . intval($number),
                empty($modifiedNote) ? $note : $modifiedNote);

    }
    $content .= $is_pdf ? '</div>' : '</ul>';

    return $content;
}