<?php

/*
* Created on 21.05.2009
* By: Joans Schwabe (GH1234)
* j.s@cascaded-web.com
*/
error_reporting(E_ALL);
@session_start();

if (!isset($_SESSION['litotex_start_acp']) || !isset($_SESSION['userid']))
{
    unset($_SESSION);
    header("LOCATION: ../acp_login/login.php");
    exit();
}

require ($_SESSION['litotex_start_acp'] . 'acp/includes/global.php');


$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : 'main');

$modul_name = "acp_themes";
$menu_name = "Templatemanager";
$tpl->assign('menu_name', $menu_name);
require ($_SESSION['litotex_start_acp'] . 'acp/includes/perm.php');

if ($action == 'zip')
{
    if (!isset($_GET['id']))
    {
        error_msg('Es wurde keine ID &uuml;bergeben!');
        exit;
    }
    $id = $_GET['id'] * 1;
    $nstd_q = $db->query("SELECT `design_id`, `aktive`, `design_name` FROM `cc" . $n . "_desigs` WHERE `design_id` = '" . $id .
        "'");
    $nstd = $db->fetch_array($nstd_q);
    if (empty($nstd))
    {
        error_msg('Das Template wurde nicht in der Datenbank gefunden!');
        exit;
    }
    $name = trim($nstd['design_name']);

    $zipFile = LITO_ROOT_PATH . 'cache/' . $name . '.zip';
    if (is_file($zipFile))
    {
        unlink($zipFile);
    }

    $dirNameT = LITO_ROOT_PATH . 'themes/' . $name;
    $dirNameI = LITO_ROOT_PATH . 'images/' . $name;
    $dirNameC = LITO_ROOT_PATH . 'css/' . $name;

    if (!is_dir($dirNameT) || !is_dir($dirNameI) || !is_dir($dirNameC))
    {
        error_msg('Es ist ein Fehler aufgetreten, Es existsieren nicht alle Ordner des zu speichernen Designes!');
        exit;
    }

    $zip = new ZipArchive();
    $zip->open($zipFile, ZipArchive::CREATE);

    $allFiles = _scandir($dirNameT);
    $allFiles = array_merge($allFiles, _scandir($dirNameI));
    $allFiles = array_merge($allFiles, _scandir($dirNameC));

    foreach ($allFiles as $FileName)
    {
        $zip->addFile($FileName, str_replace(LITO_ROOT_PATH, '', $FileName));
    }

    if (!$zip->close())
    {
        error_msg('Es ist ein Fehler aufgetreten, dieser konnte nicht n&auml;her bestimmt werden!');
        exit;
    }

    header('Location:../../../cache/' . $name . '.zip');
    $action = 'main';
}

if ($action == 'changestd')
{
    if (!isset($_GET['id']))
    {
        error_msg('Es wurde keine ID &uuml;bergeben!');
        exit;
    }
    $id = $_GET['id'] * 1;
    $nstd_q = $db->query("SELECT `design_id` FROM `cc" . $n . "_desigs` WHERE `design_id` = '" . $id . "'");
    if (!$db->num_rows($nstd_q))
    {
        error_msg('Das Template wurde nicht in der Datenbank gefunden!');
        exit;
    }
    $db->update("UPDATE `cc" . $n . "_desigs` SET `aktive` = 0; 
                 UPDATE `cc" . $n . "_desigs` SET `aktive` = 1 WHERE `design_id` = '" . $id . "';
                 UPDATE `cc" . $n . "_users` SET `design_id` = '" . $id . "';");
    $action = 'main';
}

if ($action == 'changealt')
{
    if (!isset($_GET['id']))
    {
        error_msg('Es wurde keine ID &uuml;bergeben!');
        exit;
    }
    $id = $_GET['id'] * 1;
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
    $action = 'main';
}

if ($action == 'remove')
{
    if (!isset($_GET['id']))
    {
        error_msg('Es wurde keine ID &uuml;bergeben!');
        exit;
    }
    $id = $_GET['id'] * 1;
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

    $dirNameT = LITO_ROOT_PATH . 'themes/' . $nstd['design_name'] . '/';
    $dirNameI = LITO_ROOT_PATH . 'images/' . $nstd['design_name'] . '/';
    $dirNameC = LITO_ROOT_PATH . 'css/' . $nstd['design_name'] . '/';
    if (!_rmdir($dirNameT))
    {
        error_msg('Litotex konnte den Ordner "' . $dirNameT . '" nicht löschen!');
        exit;
    }

    if (!_rmdir($dirNameI))
    {
        error_msg('Litotex konnte den Ordner "' . $dirNameI . '" nicht löschen!');
        exit;
    }

    if (!_rmdir($dirNameC))
    {
        error_msg('Litotex konnte den Ordner "' . $dirNameC . '" nicht löschen!');
        exit;
    }

    $db->delete("DELETE FROM `cc" . $n . "_desigs` WHERE `design_id` = '" . $id . "'");

    $aktive_q = $db->query("SELECT `design_id` FROM `cc" . $n . "_desigs` WHERE `aktive` = 1");
    $aktive = $db->fetch_array($aktive_q);

    $db->update("UPDATE `cc" . $n . "_users` SET `design_id` = '" . $aktive['design_id'] . "' WHERE `design_id` = '" . $id .
        "'");
    $action = 'main';
}

if ($action == 'new')
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

    mkdir(LITO_ROOT_PATH . 'themes/' . $_POST['name']);
    chmod(LITO_ROOT_PATH . 'themes/' . $_POST['name'], 0755);

    mkdir(LITO_ROOT_PATH . 'images/' . $_POST['name']);
    chmod(LITO_ROOT_PATH . 'images/' . $_POST['name'], 0755);

    mkdir(LITO_ROOT_PATH . 'css/' . $_POST['name']);
    chmod(LITO_ROOT_PATH . 'css/' . $_POST['name'], 0755);

    mkdir(LITO_ROOT_PATH . 'templates_c/' . $_POST['name']);
    chmod(LITO_ROOT_PATH . 'templates_c/' . $_POST['name'], 0777);


    $db->insert("INSERT INTO `cc" . $n .
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
        $db->insert("INSERT INTO `cc" . $n .
            "_menu_game` (`menu_game_name`, `menu_game_link`, `modul_id`, `sort_order`, `menu_art_id`, `ingame`, `optional_parameter`, `design_id`) VALUES ('" .
            $element['menu_game_name'] . "', '" . $element['menu_game_link'] . "', '" . $element['modul_id'] . "', '" . $element['sort_order'] .
            "', '" . $element['menu_art_id'] . "', '" . $element['ingame'] . "', '" . $element['optional_parameter'] . "', '" . $newid .
            "')");
    }
    $action = 'main';
}

if ($action == 'dub')
{
    if (!isset($_GET['id']) || !isset($_GET['new']))
    {
        error_msg('Es wurde keine ID &uuml;bergeben!');
        exit;
    }
    $id = $_GET['id'] * 1;
    if (!preg_match('!^[a-z_\-]*$!', $_GET['new']))
    {
        error_msg('Der neue Name darf nur Buchstaben (a-z), Unterstriche (_) und Minus (-) enthalten!');
        exit;
    }
    $nstd_q = $db->query("SELECT * FROM `cc" . $n . "_desigs` WHERE `design_id` = '" . $id . "'");
    $nstd = $db->fetch_array($nstd_q);

    if ($nstd == false || $nstd == null)
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
    $sourcet = LITO_ROOT_PATH . 'themes/' . $nstd['design_name'];
    $sourcei = LITO_ROOT_PATH . 'images/' . $nstd['design_name'];
    $sourcec = LITO_ROOT_PATH . 'css/' . $nstd['design_name'];

    $destt = LITO_ROOT_PATH . 'themes/' . $_GET['new'];
    $desti = LITO_ROOT_PATH . 'images/' . $_GET['new'];
    $destc = LITO_ROOT_PATH . 'css/' . $_GET['new'];

    $tpl_c = LITO_ROOT_PATH . 'templates_c/' . $_GET['new'];

    if (!is_dir($sourcet))
    {
        error_msg('Die Daten des Quell Templates konnten nicht auf dem Server gefunden werden!');
        exit;
    }
    if (!is_dir($sourcei))
    {
        error_msg('Die Daten des Quell Templates konnten nicht auf dem Server gefunden werden!');
        exit;
    }
    if (!is_dir($sourcec))
    {
        error_msg('Die Daten des Quell Templates konnten nicht auf dem Server gefunden werden!');
        exit;
    }
    //$ftp->mkdir($ftproot . 'templates_c/' . $_POST['name'] . '/', true);
    //$ftp->chmod('0777', $ftproot . 'templates_c/' . $_POST['name'] . '/');

    if (is_dir($destt))
    {
        error_msg('Das Zeil Template existiert bereits!');
        exit;
    }
    if (is_dir($desti))
    {
        error_msg('Das Zeil Template existiert bereits!');
        exit;
    }
    if (is_dir($destc))
    {
        error_msg('Das Zeil Template existiert bereits!');
        exit;
    }

    if (is_dir($tpl_c))
    {
        _rmdir($tpl_c, true);
    }
    mkdir($tpl_c);
    chmod($tpl_c, 0777);

    copyr($sourcet, $destt);
    copyr($sourcei, $desti);
    copyr($sourcec, $destc);

    //$ftp->close();

    $db->insert("INSERT INTO `cc" . $n .
        "_desigs` (`design_name`, `design_author`, `design_copyright`, `design_author_web`, `design_author_mail`, `design_description`, `aktive`, `alternate_permit`) VALUES ('" .
        $_GET['new'] . "', '" . $nstd['design_author'] . "', '" . $nstd['design_copyright'] . "', '" . $nstd['design_author_web'] .
        "', '" . $nstd['design_author_mail'] . "', '" . $nstd['design_description'] . "', 0, 0)");
    $newid = $db->insert_id();


    $navi_db = $db->query("SELECT * FROM `cc" . $n . "_menu_game` WHERE `design_id` = " . $nstd['design_id'] .
        " ORDER BY `sort_order` ASC");

    while ($element = $db->fetch_array($navi_db))
    {
        $db->insert("INSERT INTO `cc" . $n .
            "_menu_game` (`menu_game_name`, `menu_game_link`, `modul_id`, `sort_order`, `menu_art_id`, `ingame`, `optional_parameter`, `design_id`) VALUES ('" .
            $element['menu_game_name'] . "', '" . $element['menu_game_link'] . "', '" . $element['modul_id'] . "', '" . $element['sort_order'] .
            "', '" . $element['menu_art_id'] . "', '" . $element['ingame'] . "', '" . $element['optional_parameter'] . "', '" . $newid .
            "')");
    }
    $action = 'main';
}

if ($action == 'test')
{
    if (!isset($_GET['id']))
    {
        error_msg('Es wurde keine ID &uuml;bergeben!');
        exit;
    }
    $_GET['id'] = $_GET['id'] * 1;
    $db->update("UPDATE `cc" . $n . "_users` SET `design_id` = '" . $_GET['id'] . "' WHERE `userid` = '" . $_SESSION['userid'] .
        "'");
    header("Location:" . LITO_ROOT_PATH_URL);
}

if ($action == "main")
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
