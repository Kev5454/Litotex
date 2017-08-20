<?php

/**
 * Litotex - Browsergame Engine
 * Copyright 2017 Das litotex.info Team, All Rights Reserved
 *
 * Website: http://www.litotex.info
 * License: GNU GENERAL PUBLIC LICENSE v3 (https://litotex.info/showthread.php?tid=3)
 *
 */

session_start();


$action = (isset($_REQUEST['action']) ? filter_var($_REQUEST['action'], FILTER_SANITIZE_STRING) : 'change');
$modul_name = "lang";
require ('../../includes/global.php');

if (is_modul_name_aktive($modul_name) == 0)
{
    show_error('MODUL_LOAD_ERROR', 'core');
    exit();
}


if ($action == "change")
{
    $newLang = (isset($_REQUEST['lang']) && strlen($_REQUEST['lang']) == 2 ? filter_var($_REQUEST['lang'],
        FILTER_SANITIZE_STRING) : 'de');
    $fileName = LITO_LANG_PATH . 'core' . DIRECTORY_SEPARATOR . 'lang_' . $newLang . '.php';
    if (!file_exists($fileName))
    {
        show_error('LANG_NOT_FOUND_ERROR', 'core');
    }

    if (isset($_SESSION['userid']))
    {
        $db->query("UPDATE cc" . $n . "_users SET lang='" . $newLang . "' WHERE userid='" . $_SESSION['userid'] . "'");
    }
    $_SESSION['lang'] = $newLang;
    template_out('lang.html', $modul_name);
    exit();
}
