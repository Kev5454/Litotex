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
if (!isset($_SESSION['litotex_start_acp']) || !isset($_SESSION['userid']))
{
    header('LOCATION: ./../../index.php');
    exit();
}

require ($_SESSION['litotex_start_acp'] . 'acp/includes/global.php');

$action = (isset($_REQUEST['action']) ? filter_var($_REQUEST['action'], FILTER_SANITIZE_STRING) : 'main');

$modul_name = "acp_bannermgr";
$menu_name = "Bannermanager";
$tpl->assign('menu_name', $menu_name);

if ($action == "main")
{
    $inhalt = array();
    $result = $db->query("SELECT * FROM cc" . $n . "_banner_mgr");
    $i = 0;
    while ($row = $db->fetch_array($result))
    {
        $inhalt[$i]['active'] = $row['active'];
        $inhalt[$i]['banner_code'] = str_replace('"', "\'", $row['banner_code']);
        $inhalt[$i]['banner_label'] = $row['banner_label'];
        $inhalt[$i]['banner_count'] = $row['banner_count'];
        $inhalt[$i]['banner_id'] = $row['banner_id'];

        $i++;
    }
    $tpl->assign('banner_output', $inhalt);
    $tpl->assign('ACTION_SAVE', 'new');
    template_out('banner.html', $modul_name);
}
elseif ($action == "new")
{
    $b_label = filter_var($_POST['banner_label'], FILTER_SANITIZE_STRING);
    $b_code = filter_var($_POST['banner_code'], FILTER_SANITIZE_STRING);

    if (empty($b_code))
    {
        error_msg("Du hast keinen Bannercode eingetragen");
        exit();
    }
    if (empty($b_label))
    {
        $result = $db->query("SELECT max(banner_id) as maxi_b FROM cc" . $n . "_banner_mgr");
        $row = $db->fetch_array($result);

        $b_label = "Banner Nr.:" . (int)($row['maxi_b']) + 1;
    }

    $b_code = str_replace("'", '"', $b_code);

    $db->query("INSERT INTO cc" . $n . "_banner_mgr(banner_code, banner_label ,banner_count,active) VALUES ('" . $b_code .
        "','$b_label','0','0')");

    redirect($modul_name, 'banner', 'main');
}
elseif ($action == "edit")
{
    $news_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    $result_news = $db->query("SELECT * FROM cc" . $n . "_banner_mgr where banner_id ='$news_id'");
    $row = $db->fetch_array($result_news);

    $tpl->assign('banner_label', $row['banner_label']);
    $tpl->assign('banner_code', $row['banner_code']);
    $tpl->assign('ACTION_SAVE', 'update&id=' . $row['banner_id']);

    template_out('banner.html', $modul_name);
}
elseif ($action == "activate")
{
    $news_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    $result_news = $db->query("SELECT active  FROM cc" . $n . "_banner_mgr where banner_id  ='$news_id'");
    $row = $db->fetch_array($result_news);

    if ($row['active'] == 0)
    {
        $sql = "update cc" . $n . "_banner_mgr set active='1' where banner_id ='$news_id'";
    }
    else
    {
        $sql = "update cc" . $n . "_banner_mgr set active='0' where banner_id ='$news_id'";
    }
    $db->query($sql);

    redirect($modul_name, 'banner', 'main');
}
elseif ($action == "delete")
{
    $banner_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    
    $db->query("delete from cc" . $n . "_banner_mgr where banner_id ='$banner_id'");
    
    redirect($modul_name, 'banner', 'main');
}
elseif ($action == "update")
{
    $banner_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $b_label = filter_var($_POST['banner_label'], FILTER_SANITIZE_STRING);
    $b_code = filter_var($_POST['banner_code'], FILTER_SANITIZE_STRING);
    
    if (empty($b_code))
    {
        error_msg("Du hast keinen Bannercode eingetragen");
        exit();
    }
    if (empty($b_label))
    {
        $result = $db->query("SELECT max(banner_id) as maxi_b FROM cc" . $n . "_banner_mgr");
        $row = $db->fetch_array($result);
        
        $b_label = "Banner Nr.:" . (int)($row['maxi_b']) + 1;
    }

    $db->query("update  cc" . $n . "_banner_mgr set banner_code='" . $b_code . "', banner_label ='$b_label',banner_count='0',active='0' where banner_id ='$banner_id' ");

    redirect($modul_name, 'banner', 'main');
}
