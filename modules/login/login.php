<?php

/*
************************************************************
Litotex BrowsergameEngine
https://litotex.info
http://www.Litotex.de
http://www.freebg.de

Copyright (c) 2017 K. Wehmeyer
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

require ('../../includes/global.php');
$action = (isset($_REQUEST['action']) ? filter_var($_REQUEST['action'], FILTER_SANITIZE_STRING) : 'main');
$modul_name = "login";


if (is_modul_name_aktive($modul_name) == 0)
{
    show_error('MODUL_LOAD_ERROR', 'core');
    exit();
}


if ($action == "main")
{
    $tpl->assign('if_disable_menu', 1);
    template_out('login.html', $modul_name);
    exit();
}

if ($action == "submit")
{
    $username = strtolower($_POST['username']);
    $password = c_trim($_POST['password']);

    if (!$username || !$password )
    {
        show_error("LOGIN_ERROR_1", 'login');
    }

    $result = $db->query("SELECT * FROM cc" . $n . "_users WHERE username='$username'");
    $row = $db->fetch_array($result);

    if (strtolower($row['username']) != $username)
    {
        trace_msg("login ERROR '$username' wrong username", 2);
        show_error("LOGIN_ERROR_2", 'login');
    }

    if ($row['password'] != md5($password))
    {
        trace_msg("login ERROR '$username' wrong password", 2);
        show_error("LOGIN_ERROR_2", 'login');
    }
    
    $_SESSION['userid'] = intval($row['userid']);
    $_SESSION['lang'] = $row['lang'];
    trace_msg("login OK '" . $username . "' with lang '" . $row['lang'] . "'", 2);
    $db->unbuffered_query("UPDATE cc" . $n . "_users SET lastlogin='" . time() . "', ip='" . $_SERVER["REMOTE_ADDR"] .
        "' WHERE username='" . $username . "'");
    header("LOCATION: " . LITO_MODUL_PATH_URL . 'members/members.php');
    exit();
}
