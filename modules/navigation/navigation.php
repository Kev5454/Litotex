<?php

/**
 * Litotex - Browsergame Engine
 * Copyright 2017 Das litotex.info Team, All Rights Reserved
 *
 * Website: http://www.litotex.info
 * License: GNU GENERAL PUBLIC LICENSE v3 (https://litotex.info/showthread.php?tid=3)
 *
 */
/*
************************************************************
Litotex BrowsergameEngine
http://www.Litotex.de
http://www.freebg.de

Copyright (c) 2008 FreeBG Team
************************************************************
Hinweis:
Diese Software ist urheberechtlich geschützt.

Für jegliche Fehler oder Schäden, die durch diese Software
auftreten könnten, übernimmt der Autor keine Haftung.

Alle Copyright - Hinweise Innerhalb dieser Datei 
dürfen NICHT entfernt und NICHT verändert werden. 
************************************************************
Released under the GNU General Public License 
************************************************************  
*/

class navigation
{

    private $version = "0.7.2";
    private $modul_name = "navigation";
    private $modul_type = "nav";


    public function make_navigation($modulename, $modul_id, $ingame, $menue_art)
    {
        global $tpl, $db, $n;

        $new_found_inhalt_navi = array();
        $new_found_navi = array();

        $IMG_PATH = LITO_IMG_PATH_URL . $this->modul_name . '/';

        $navi = "";
        $theme = 0;
        if (!defined('LITO_THEMES'))
        {
            $theme = 1;
        }
        else
        {
            $themeq = $db->query("SELECT `design_id` FROM `cc" . $n . "_desigs` WHERE `design_name` = '" . LITO_THEMES . "'");
            $themeq = $db->fetch_array($themeq);

            $theme = (!isset($themeq['design_id']) ? 1 : $themeq['design_id']);
        }
        $result = $db->query("SELECT * FROM cc" . $n . "_menu_game where ingame='" . $ingame . "' and  modul_id ='$modul_id' and menu_art_id ='" .
            $menue_art . "' and design_id = $theme order by sort_order ASC");

        while ($row_g = $db->fetch_array($result))
        {

            $new_found_navi[] = array(
                $row_g['sort_order'],
                $row_g['menu_game_name'],
                $row_g['menu_game_link'],
                $row_g['optional_parameter']);
        }
        $tpl->assign('daten_navi', $new_found_navi);
        $navi = $tpl->fetch(LITO_THEMES_PATH . $this->modul_name . '/navigation_' . $menue_art . '.html');

        $search = array(
            "[LITO_ROOT_PATH_URL]",
            "[LITO_IMG_PATH]",
            "[LITO_BASE_MODUL_URL]",
            'ä',
            'ö',
            'ü',
            'Ä',
            'Ö',
            'Ü',
            'ß',
            );
        $replace = array(
            LITO_ROOT_PATH_URL,
            $IMG_PATH,
            LITO_MODUL_PATH_URL,
            '&auml;',
            '&ouml;',
            '&uuml;',
            '&Auml;',
            '&Ouml;',
            '&szlig;',
            );
        return str_replace($search, $replace, $navi);
    }
}
