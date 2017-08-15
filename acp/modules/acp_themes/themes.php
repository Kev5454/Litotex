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
$modul_name = "acp_themes";
$menu_name = "Templatemanager";
require ($_SESSION['litotex_start_acp'] . 'acp/includes/perm.php');
$tpl->assign('menu_name', $menu_name);

if ($action == 'zip')
{
    if (!isset($_GET['id']))
    {
        error_msg('Es wurde keine ID &uuml;bergeben!');
        exit;
    }
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $nstd_q = $db->query("SELECT `design_id`, `aktive`, `design_name` FROM `cc" . $n . "_desigs` WHERE `design_id` = '" . $id .
        "'");
    if (!$db->num_rows($nstd_q))
    {
        error_msg('Das Template wurde nicht in der Datenbank gefunden!');
        exit;
    }
    $nstd = $db->fetch_array($nstd_q);

    $root = $_SESSION['litotex_start_acp'];
    $name = DIRECTORY_SEPARATOR . $nstd['design_name'] . DIRECTORY_SEPARATOR;

    $fileName = $root . 'acp' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'themes.zip';

    $zip = new ZipArchive($fileName);
    $zip = addDirectoryToZip($zip, $root . 'themes' . $name, 'themes' . DIRECTORY_SEPARATOR);
    $zip = addDirectoryToZip($zip, $root . 'images' . $name, 'images' . DIRECTORY_SEPARATOR);
    $zip = addDirectoryToZip($zip, $root . 'css' . $name, 'css' . DIRECTORY_SEPARATOR);
    $zip->close();

    setDownloadFile($fileName, $nstd['design_name'] . 'zip');
    unlink($fileName);
}
elseif ($action == 'changestd')
{
    if (!isset($_GET['id']))
    {
        error_msg('Es wurde keine ID &uuml;bergeben!');
        exit;
    }
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $nstd_q = $db->query("SELECT `design_id` FROM `cc" . $n . "_desigs` WHERE `design_id` = '" . $id . "'");
    if (!$db->num_rows($nstd_q))
    {
        error_msg('Das Template wurde nicht in der Datenbank gefunden!');
        exit;
    }
    $sql = "UPDATE `cc" . $n . "_desigs` SET `aktive` = 0;";
    $sql .= "UPDATE `cc" . $n . "_desigs` SET `aktive` = 1 WHERE `design_id` = '" . $id . "';";
    $sql .= "UPDATE `cc" . $n . "_users` SET `design_id` = '" . $id . "';";
    $db->multi_query($sql);
    redirect($modul_name, 'themes', 'main');
}
elseif ($action == 'changealt')
{
    if (!isset($_GET['id']))
    {
        error_msg('Es wurde keine ID &uuml;bergeben!');
        exit;
    }
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $nstd_q = $db->query("SELECT `design_id`, `alternate_permit` FROM `cc" . $n . "_desigs` WHERE `design_id` = '" . $id .
        "'");
    if (!$db->num_rows($nstd_q))
    {
        error_msg('Das Template wurde nicht in der Datenbank gefunden!');
        exit;
    }
    $nstd = $db->fetch_array($nstd_q);
    $alt = ($nstd['alternate_permit'] == 0 ? 1 : 0);

    $db->query("UPDATE `cc" . $n . "_desigs` SET `alternate_permit` = " . $alt . " WHERE `design_id` = '" . $id . "'");

    redirect($modul_name, 'themes', 'main');
}
elseif ($action == 'remove')
{
    if (!isset($_GET['id']))
    {
        error_msg('Es wurde keine ID &uuml;bergeben!');
        exit;
    }
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $nstd_q = $db->query("SELECT `design_id`, `aktive`, `design_name` FROM `cc" . $n . "_desigs` WHERE `design_id` = '" . $id .
        "'");
    if (!$db->num_rows($nstd_q))
    {
        error_msg('Das Template wurde nicht in der Datenbank gefunden!');
        exit;
    }
    $nstd = $db->fetch_array($nstd_q);
    if ($nstd['aktive'] == 1 || $nstd['design_id'] == 1)
    {
        error_msg('Sie versuchen das Standardtemplate zu löschen, das ist nicht möglich!');
        exit;
    }

    $root = $_SESSION['litotex_start_acp'];
    removeDirectory($root . 'themes' . DIRECTORY_SEPARATOR . $nstd['design_name'] . DIRECTORY_SEPARATOR);
    removeDirectory($root . 'images' . DIRECTORY_SEPARATOR . $nstd['design_name'] . DIRECTORY_SEPARATOR);
    removeDirectory($root . 'css' . DIRECTORY_SEPARATOR . $nstd['design_name'] . DIRECTORY_SEPARATOR);
    removeDirectory($root . 'templates_c' . DIRECTORY_SEPARATOR . $nstd['design_name'] . DIRECTORY_SEPARATOR);


    $db->query("DELETE FROM `cc" . $n . "_desigs` WHERE `design_id` = '" . $id . "'");

    $aktive_q = $db->query("SELECT `design_id` FROM `cc" . $n . "_desigs` WHERE `aktive` = 1");
    $aktive = $db->fetch_array($aktive_q);

    $db->query("UPDATE `cc" . $n . "_users` SET `design_id` = '" . $aktive['design_id'] . "' WHERE `design_id` = '" . $id .
        "'");
    redirect($modul_name, 'themes', 'main');
}
elseif ($action == 'new')
{
    if (!(isset($_POST['name']) && isset($_POST['mail']) && isset($_POST['description']) && isset($_POST['author']) && isset
        ($_POST['copy']) && isset($_POST['web'])))
    {
        error_msg('Es wurden nicht alle nötigen Daten übergeben.');
        exit;
    }
    if (!preg_match('!^[a-z_\-]*$!', $_POST['name']))
    {
        error_msg('Der neue Name darf nur Buchstaben (a-z), Unterstriche (_) und Minus (-) enthalten!');
        exit;
    }
    $cp_q = $db->query("SELECT * FROM `cc" . $n . "_desigs` WHERE `design_name` = '" . $_POST['name'] . "'");
    if ($db->num_rows($cp_q))
    {
        error_msg('Das Zieltemplate ist bereits in der Datenbank!');
        exit;
    }


    $root = $_SESSION['litotex_start_acp'];
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);

    _mkdir($root . 'themes' . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR, 0755);
    _mkdir($root . 'images' . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR, 0755);
    _mkdir($root . 'css' . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR, 0755);
    _mkdir($root . 'templates_c' . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR);


    $db->query("INSERT INTO `cc" . $n .
        "_desigs` (`design_name`, `design_author`, `design_copyright`, `design_author_web`, `design_author_mail`, `design_description`, `aktive`, `alternate_permit`) VALUES ('" .
        $_POST['name'] . "', '" . $db->escape_string($_POST['author']) . "', '" . $db->escape_string($_POST['copy']) . "', '" .
        $db->escape_string($_POST['web']) . "', '" . $db->escape_string($_POST['mail']) . "', '" . $db->escape_string($_POST['description']) .
        "', 0, 0)");
    $newid = $db->insert_id();
    //Standartdesign
    $std = $db->query("SELECT `design_id` FROM `cc" . $n . "_desigs` WHERE `aktive` = 1");
    $std = $db->fetch_array($std);
    $navi_db = $db->query("SELECT * FROM `cc" . $n . "_menu_game` WHERE `design_id` = " . $std['design_id'] .
        " ORDER BY `sort_order` ASC");

    while ($element = $db->fetch_array($navi_db))
    {
        $db->query("INSERT INTO `cc" . $n .
            "_menu_game` (`menu_game_name`, `menu_game_link`, `modul_id`, `sort_order`, `menu_art_id`, `ingame`, `optional_parameter`, `design_id`) VALUES ('" .
            $element['menu_game_name'] . "', '" . $element['menu_game_link'] . "', '" . $element['modul_id'] . "', '" . $element['sort_order'] .
            "', '" . $element['menu_art_id'] . "', '" . $element['ingame'] . "', '" . $element['optional_parameter'] . "', '" . $newid .
            "')");
    }
    redirect($modul_name, 'themes', 'main');
}
elseif ($action == 'dub')
{
    if (!isset($_GET['id']) || !isset($_GET['new']))
    {
        error_msg('Es wurde keine ID &uuml;bergeben!');
        exit;
    }
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    if (!preg_match('!^[a-z_\-]*$!', $_GET['new']))
    {
        error_msg('Der neue Name darf nur Buchstaben (a-z), Unterstriche (_) und Minus (-) enthalten!');
        exit;
    }
    $nstd_q = $db->query("SELECT * FROM `cc" . $n . "_desigs` WHERE `design_id` = '" . $id . "'");
    if (!$db->num_rows($nstd_q))
    {
        error_msg('Das Template wurde nicht in der Datenbank gefunden!');
        exit;
    }
    $cp_q = $db->query("SELECT * FROM `cc" . $n . "_desigs` WHERE `design_name` = '" . $_GET['new'] . "'");
    if ($db->num_rows($cp_q))
    {
        error_msg('Das Zieltemplate ist bereits in der Datenbank!');
        exit;
    }
    $nstd = $db->fetch_array($nstd_q);

    $root = $_SESSION['litotex_start_acp'];
    $name = $nstd['design_name'];
    $newName = filter_var($_GET['new'], FILTER_SANITIZE_STRING);

    $sourcet = $root . 'themes' . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR;
    $sourcei = $root . 'images' . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR;
    $sourcec = $root . 'css' . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR;

    $destt = $root . 'themes' . DIRECTORY_SEPARATOR . $newName . DIRECTORY_SEPARATOR;
    $desti = $root . 'images' . DIRECTORY_SEPARATOR . $newName . DIRECTORY_SEPARATOR;
    $destc = $root . 'css' . DIRECTORY_SEPARATOR . $newName . DIRECTORY_SEPARATOR;
    $desttc = $root . 'template_c' . DIRECTORY_SEPARATOR . $newName . DIRECTORY_SEPARATOR;
    if (!file_exists($sourcet))
    {
        error_msg('Die Daten des Quell Templates(Themes) konnten nicht auf dem Server gefunden werden!');
        exit;
    }
    if (!file_exists($sourcei))
    {
        error_msg('Die Daten des Quell Templates(Images) konnten nicht auf dem Server gefunden werden!');
        exit;
    }
    if (!file_exists($sourcec))
    {
        error_msg('Die Daten des Quell Templates(Css) konnten nicht auf dem Server gefunden werden!');
        exit;
    }

    if (file_exists($destt))
    {
        error_msg('Das Zeil Template(Themes) existiert bereits!');
        exit;
    }
    if (file_exists($desti))
    {
        error_msg('Das Zeil Template(Images) existiert bereits!');
        exit;
    }
    if (file_exists($destc))
    {
        error_msg('Das Zeil Template(Css) existiert bereits!');
        exit;
    }

    _mkdir($desttc, 0777, false);

    _copy($sourcet, $destt);
    _copy($sourcei, $desti);
    _copy($sourcec, $destc);

    $db->query("INSERT INTO `cc" . $n .
        "_desigs` (`design_name`, `design_author`, `design_copyright`, `design_author_web`, `design_author_mail`, `design_description`, `aktive`, `alternate_permit`) VALUES ('" .
        $_GET['new'] . "', '" . $nstd['design_author'] . "', '" . $nstd['design_copyright'] . "', '" . $nstd['design_author_web'] .
        "', '" . $nstd['design_author_mail'] . "', '" . $nstd['design_description'] . "', 0, 0)");
    $newid = $db->insert_id();

    $sql = '';
    $navi_db = $db->query("SELECT * FROM `cc" . $n . "_menu_game` WHERE `design_id` = " . $nstd['design_id'] .
        " ORDER BY `sort_order` ASC");
    while ($element = $db->fetch_array($navi_db))
    {
        $sql .= "INSERT INTO `cc" . $n .
            "_menu_game` (`menu_game_name`, `menu_game_link`, `modul_id`, `sort_order`, `menu_art_id`, `ingame`, `optional_parameter`, `design_id`) VALUES ('" .
            $element['menu_game_name'] . "', '" . $element['menu_game_link'] . "', '" . $element['modul_id'] . "', '" . $element['sort_order'] .
            "', '" . $element['menu_art_id'] . "', '" . $element['ingame'] . "', '" . $element['optional_parameter'] . "', '" . $newid .
            "');";
    }
    $db->multi_query($sql);
    redirect($modul_name, 'themes', 'main');
}
elseif ($action == 'test')
{
    if (!isset($_GET['id']))
    {
        error_msg('Es wurde keine ID &uuml;bergeben!');
        exit;
    }
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $db->query("UPDATE `cc" . $n . "_users` SET `design_id` = '" . $id . "' WHERE `userid` = '" . $_SESSION['userid'] . "'");
    header("Location:" . LITO_ROOT_PATH_URL);
}
elseif ($action == "main")
{
    $themes_q = $db->query("SELECT `design_id`, `design_name`, `design_author`, `design_copyright`, `design_author_mail`, `design_author_web`, `design_description`, `aktive`, `alternate_permit` FROM `cc" .
        $n . "_desigs`");
    $themes = array();
    $i = 0;
    while ($theme = $db->fetch_array($themes_q))
    {
        $themes[$i]['id'] = $theme['design_id'];
        $themes[$i]['name'] = $theme['design_name'];
        $themes[$i]['author'] = $theme['design_author'];
        $themes[$i]['copy'] = $theme['design_copyright'];
        $themes[$i]['mail'] = $theme['design_author_mail'];
        if (!preg_match('!^http://!', $theme['design_author_web'])) $theme['design_author_web'] = 'http://' . $theme['design_author_web'];
        $themes[$i]['web'] = $theme['design_author_web'];
        $themes[$i]['description'] = $theme['design_description'];
        $themes[$i]['aktive'] = $theme['aktive'];
        $themes[$i]['alt'] = $theme['alternate_permit'];
        $i++;
    }
    $tpl->assign('themes', $themes);
    template_out('main.html', $modul_name);
}