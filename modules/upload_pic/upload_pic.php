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
if (!isset($_SESSION['litotex_start_g']) || !isset($_SESSION['userid']))
{
    require ('../../includes/global.php');
    show_error("LOGIN_ERROR", 'core');
}

$action = (isset($_REQUEST['action']) ? filter_var($_REQUEST['action'], FILTER_SANITIZE_STRING) : 'main');
$modul_name = "upload_pic";
require ($_SESSION['litotex_start_g'] . 'includes/global.php');

if (is_modul_name_aktive($modul_name) == 0)
{
    show_error('MODUL_LOAD_ERROR', 'core');
    exit();
}

if ($action == "main")
{
    if ($userdata['userpic'] == "")
    {
        $userpic = LITO_IMG_PATH_URL . "members/no_user_pic.jpg";
    }
    else
    {
        $userpic = $userdata['userpic'];
    }
    $tpl->assign('USER_USERIMAGE', $userpic);
    template_out('pic_uploads.html', $modul_name);
    exit();


}

if ($action == "img_upload")
{


    $uid = $userdata['userid'];
    $filename = "";
    $path = LITO_ROOT_PATH . "images_tmp/";
    $banner = "";
    $time = time();


    if ($_FILES['userfile']['tmp_name'] <> 'none')
    {
        $file = $_FILES['userfile']['name'];
        if (substr($file, strlen($file) - 5) == ".jpeg" or substr($file, strlen($file) - 4) == ".jpg" or substr($file, strlen($file) -
            4) == ".JPG")
        {
            $filext = "ok";
        }
        else
        {
            $filext = "nok";
        }


        if ($filext == "nok")
        {

            show_error('PIC_UPLOAD_ERROR_1', $modul_name);
            exit();
        }

        $temp = $_FILES['userfile']['tmp_name'];

        $path_parts = pathinfo($file);

        $filename = "gal_" . $uid . "." . $path_parts["extension"];
        $dest = $path . $filename;


        if ($temp != "")
        {
            copy($temp, $dest);
            $up_date = time();
            auto_generate_thumbs($dest);

        }
        else
        {

            show_error('PIC_UPLOAD_ERROR_2', $modul_name);
            exit();
        }
        header("LOCATION: " . LITO_ROOT_PATH_URL . "/modules/members/members.php?action=edituserdata");
        exit();

    }
}
if ($action == "img_upload_ali")
{


    $uid = $userdata['userid'];
    $filename = "";
    $path = LITO_ROOT_PATH . "images_tmp/";


    $time = time();
    if ($_FILES['userfile']['tmp_name'] <> 'none')
    {
        $file = $_FILES['userfile']['name'];
        if (substr($file, strlen($file) - 5) == ".jpeg" or substr($file, strlen($file) - 4) == ".jpg" or substr($file, strlen($file) -
            4) == ".JPG")
        {
            $filext = "ok";
        }
        else
        {
            $filext = "nok";
        }
        if ($filext == "nok")
        {

            show_error('PIC_UPLOAD_ERROR_1', $modul_name);
            exit();
        }

        $temp = $_FILES['userfile']['tmp_name'];
        $path_parts = pathinfo($file);
        $filename = "gal_" . $uid . "." . $path_parts["extension"];
        $dest = $path . $filename;

        if ($temp != "")
        {
            copy($temp, $dest);
            $up_date = time();
            auto_generate_thumbs_ali($dest);

        }
        else
        {
            show_error('PIC_UPLOAD_ERROR_2', $modul_name);
            exit();
        }
        header("LOCATION: ./../alliance/alliance.php?action=change_ali_text");
        exit();

    }
}

if ($action == "del_pic")
{
    $uid = $userdata['userid'];
    $filename = $userdata['userpic'];

    unlink($filename);

    $db->unbuffered_query("update cc" . $n . "_users set userpic='' where  userid = '$uid'");

    header("LOCATION: upload_pic.php");
    exit();
}


if ($action == "alibild")
{

    $u_pic = $userdata['userpic'];

    template_out('pic_uploads_ali.html', $modul_name);
    exit();


}

if ($action == "del_pic_ali")
{
    $uid = $userdata['userid'];
    $ali_id = $userdata['allianzid'];


    $result_e = $db->query("SELECT * FROM cc" . $n . "_allianz WHERE aid ='" . $ali_id . "' ");
    while ($row_e = $db->fetch_array($result_e))
    {

        $allianz_i_url = $row_e['image_path'];
        if ($allianz_i_url != "")
        {
            unlink($allianz_i_url);
        }
    }

    $db->unbuffered_query("update cc" . $n . "_allianz set imageurl='' where  aid= '" . $ali_id . "'");
    header("LOCATION: ./../alliance/alliance.php?action=change_ali_text");
    exit();
}