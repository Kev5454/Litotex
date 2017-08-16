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
$modul_name = "acp_pagemanager";


if ($action == "main")
{
    $new_found = array();
    $result_news = $db->query("SELECT * FROM cc" . $n . "_pages order by id ");

    while ($row_g = $db->fetch_array($result_news))
    {
        $new_found[] = array(
            $row_g['id'],
            $row_g['getName'],
            $row_g['title'],
            $row_g['isActive'],
            );
    }
    $tpl->assign('menu_name', "Seiten Manager");
    $tpl->assign('daten', $new_found);
    template_out('page.html', $modul_name);
}
elseif ($action == "new")
{
    $tpl->assign('menu_name', "Seite erstellen");
    $tpl->assign('ACTION_SAVE', 'save');
    $tpl->assign('TITLE', '');
    $tpl->assign('GETNAME', '');
    $tpl->assign('ACCESS', 'public');
    $tpl->assign('CONTENT', '');
    template_out('page_new.html', $modul_name);
}
elseif ($action == "save")
{
    if (empty($_POST['content']) || empty($_POST['getName']) || empty($_POST['access']))
    {
        error_msg('Es sind nicht alle erforderlichen Felder ausgef&uuml;llt!');
    }
    else
    {
        $heading = filter_var($_POST['heading'], FILTER_SANITIZE_STRING);
        $heading = (empty($heading) ? 'Unbekannt' : $db->escape_string(trim($heading)));

        $getName = filter_var($_POST['getName'], FILTER_SANITIZE_STRING);
        $access = filter_var($_POST['access'], FILTER_SANITIZE_STRING);

        $text = filter_var(htmlspecialchars($_POST['content']), FILTER_SANITIZE_STRING);
        $text = $db->escape_string(trim($text));
        $text = nl2br($text);

        $sql = 'INSERT INTO `cc' . $n .
            '_pages` (`id`, `getName`, `title`, `content`, `access`, `isActive`, `makeTime`, `updateTime`) VALUES (NULL, \'' . $getName .
            '\', \'' . $heading . '\', \'' . $text . '\', \'' . $access . '\', \'0\', \'' . time() . '\', \'' . time() . '\')';
        $db->query($sql);
    }
    redirect($modul_name, 'page', 'main');
}
elseif ($action == "edit")
{
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    $result_news = $db->query("SELECT * FROM cc" . $n . "_pages where id ='$id'");
    $row = $db->fetch_array($result_news);

    $tpl->assign('ACTION_SAVE', 'update&id=' . $id);
    $tpl->assign('menu_name', "Seite bearbeiten");
    $tpl->assign('TITLE', $row['title']);
    $tpl->assign('GETNAME', $row['getName']);
    $tpl->assign('ACCESS', $row['access']);
    $tpl->assign('CONTENT', $row['content']);
    template_out('page_new.html', $modul_name);
}
elseif ($action == "update")
{
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    if (empty($_POST['content']) || empty($_POST['getName']) || empty($_POST['access']))
    {
        error_msg('Es sind nicht alle erforderlichen Felder ausgef&uuml;llt!');
    }
    else
    {
        $heading = filter_var($_POST['heading'], FILTER_SANITIZE_STRING);
        $heading = (empty($heading) ? 'Unbekannt' : $db->escape_string(trim($heading)));

        $getName = filter_var($_POST['getName'], FILTER_SANITIZE_STRING);
        $access = filter_var($_POST['access'], FILTER_SANITIZE_STRING);

        $text = filter_var(htmlspecialchars($_POST['content']), FILTER_SANITIZE_STRING);
        $text = $db->escape_string(trim($text));
        $text = nl2br($text);

        $db->query("UPDATE cc" . $n . "_pages SET title= '$heading',getName='$getName',access='$access',content='$text', updateTime = '" .
            time() . "' WHERE id ='$id'");
    }
    redirect($modul_name, 'page', 'main');
}
elseif ($action == "activate")
{
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    $result_news = $db->query("SELECT isActive FROM cc" . $n . "_pages WHERE id ='$id'");
    $row = $db->fetch_array($result_news);

    if ($row['isActive'] == 0)
    {
        $sql = "UPDATE cc" . $n . "_pages SET isActive='1' WHERE id ='$id'";
    }
    else
    {
        $sql = "UPDATE cc" . $n . "_pages SET isActive='0' WHERE id ='$id'";
    }

    $db->query($sql);
    redirect($modul_name, 'page', 'main');
}
elseif ($action == "delete")
{
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    $db->query("DELETE FROM cc" . $n . "_pages where id ='$id'");

    redirect($modul_name, 'page', 'main');
}
elseif ($action == "dub")
{
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    $result_news = $db->query("SELECT * FROM cc" . $n . "_pages where id ='$id'");
    $row = $db->fetch_array($result_news);

    $break = false;
    while ($break == false)
    {
        $result_news = $db->query("SELECT * FROM cc" . $n . "_pages WHERE getName = '" . $row['getName'] . "'");
        if ($db->num_rows($result_news) > 0)
        {
            $row['getName'] .= 'C';
        }
        else
        {
            $break = true;
        }
    }

    $sql = 'INSERT INTO `cc' . $n .
        '_pages` (`id`, `getName`, `title`, `content`, `access`, `isActive`, `makeTime`, `updateTime`) VALUES (NULL, \'' . $row['getName'] .
        '\', \'' . $row['title'] . ' - Kopie\', \'' . $row['content'] . '\', \'' . $row['access'] . '\', \'0\', \'' . time() . '\', \'' .
        time() . '\')';
    $db->query($sql);
    redirect($modul_name, 'page', 'main');
}
