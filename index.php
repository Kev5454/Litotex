<?php

/**
 * Litotex - Browsergame Engine
 * Copyright 2017 Das litotex.info Team, All Rights Reserved
 *
 * Website: http://www.litotex.info
 * License: GNU GENERAL PUBLIC LICENSE v3
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
session_start();

require ( './includes/global.php' );

$modul_name = "index";
$action = (isset($_REQUEST['action']) ? filter_var($_REQUEST['action'], FILTER_SANITIZE_STRING) : 'main');


if ( $action == "main" )
{
    if ( $op_set_pageManager == "1" )
    {
        $_REQUEST['action'] = "main";
        $_GET['name'] = $op_set_pageManager_name;
        
        include(LITO_MODUL_PATH . 'pagemanager/page.php');
    }
    else
    {
        $tpl->assign( 'if_disable_menu',1 );

        template_out( 'index.html',$modul_name );
    }
}
