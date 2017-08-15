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
if (!isset($_SESSION['litotex_start_g']) || !isset($_SESSION['userid']))
{
    require ('../../includes/global.php');
    show_error("LOGIN_ERROR", 'core');
}

require ($_SESSION['litotex_start_g'] . 'includes/global.php');
$action = (isset($_REQUEST['action']) ? filter_var($_REQUEST['action'], FILTER_SANITIZE_STRING) : 'main');
$modul_name = "search";

if (is_modul_name_aktive($modul_name) == 0)
{
    show_error('MODUL_LOAD_ERROR', 'core');
    exit();
}

if ($action == "main")
{
    template_out('search.html', $modul_name);
    exit();
}

if ($action == "user")
{
    $user = c_trim($_POST['user']);

    if (strlen($user) <= 1)
    {
        show_error('SEARCH_ERROR_1', $modul_name);
        exit();
    }


    $result = $db->query("SELECT userid FROM cc" . $n . "_users");
    $numOfUsers = $db->num_rows($result);


    $daten = array();
    $result = $db->query("SELECT userid,username,points,allianzid,lastlogin,lastpoints,lastactive,status,umod,userpic FROM cc" .
        $n . "_users  WHERE username LIKE '%$user%' ORDER BY points DESC ");
    $i = 0;
    while ($row = $db->fetch_array($result))
    {
        $username = $row['username'];
        $userpoints = $row['points'];

        if ($row['lastactive'] > (time() - 3600)) $online = "<span class=\"green\">&nbsp;(Online)</span>";
        else  $online = "<span class=\"red\">&nbsp;(Offline)</span>";


        $alli = $row['allianzid'];
        $chpt = $row['points'] - $row['lastpoints'];

        $lastlog = strftime("%d.%m. %H:%M", $row['lastlogin']);

        if ($row['allianzid'] == 0) $allianzname = "";
        else  $allianzname = allianz($row['allianzid']);

        $userpic = "";

        if ($row['userpic'] == "")
        {
            $userpic = LITO_IMG_PATH_URL . "members/no_user_pic.jpg";
        }
        else
        {
            $userpic = $row['userpic'];
        }
        $daten[$i]['profile_link'] = generate_userlink($row['userid'], $row['username']);
        $daten[$i]['name'] = $username;
        $daten[$i]['u_points'] = $userpoints;
        $daten[$i]['image'] = $userpic;
        $daten[$i]['u_online'] = $online;
        $daten[$i]['lastlogin'] = $lastlog;
        $daten[$i]['alianz'] = $allianzname;
        $daten[$i]['message'] = generate_messagelink_smal($username);
        $i++;


    }
    if ($i > 0)
    {
        $tpl->assign('daten', $daten);
    }

    template_out('search.html', $modul_name);

    exit();
}
