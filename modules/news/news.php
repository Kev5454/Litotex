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

session_start();


$action = (isset($_REQUEST['action']) ? filter_var($_REQUEST['action'], FILTER_SANITIZE_STRING) : 'main');
$modul_name="news";
require ('../../includes/global.php');

if (is_modul_name_aktive($modul_name)==0){
	show_error('MODUL_LOAD_ERROR','core');
	exit();
}


if($action=="main") {
	$new_found_inhalt=array();
	$new_found=array();
	$result_news=$db->query("SELECT * FROM cc".$n."_news where activated='1' order by news_id desc");

	while($row_g=$db->fetch_array($result_news)) {
		$tt_text=$row_g['text'];

		$new_found_inhalt=array($row_g['news_id'],$row_g['date'],$tt_text,$row_g['heading'],$row_g['activated']);
		array_push($new_found,$new_found_inhalt);
	}
	$tpl->assign('daten', $new_found);
	template_out('news.html',$modul_name);
	exit();

}





?>
