<?PHP

/*
************************************************************
Litotex BrowsergameEngine
http://www.Litotex.de
http://www.freebg.de

Copyright (c) 2008 FreeBG Team
************************************************************
Hinweis:
Diese Software ist urheberechtlich gesch�tzt.

F�r jegliche Fehler oder Sch�den, die durch diese Software
auftreten k�nnten, �bernimmt der Autor keine Haftung.

Alle Copyright - Hinweise Innerhalb dieser Datei 
d�rfen NICHT entfernt und NICHT ver�ndert werden. 
************************************************************
Released under the GNU General Public License 
************************************************************  
*/
session_start();
require ('includes/global.php');

redirect('acp_login', 'login', 'main');
