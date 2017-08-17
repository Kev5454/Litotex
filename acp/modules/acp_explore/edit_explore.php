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
if (!isset($_SESSION['litotex_start_acp']) || !isset($_SESSION['userid']))
{
    header('LOCATION: ./../../index.php');
    exit();
}

require ($_SESSION['litotex_start_acp'] . 'acp/includes/global.php');

$action = (isset($_REQUEST['action']) ? filter_var($_REQUEST['action'], FILTER_SANITIZE_STRING) : 'main');
$modul_name = "acp_explore";
$menu_name = "Forschungseditor";

require ($_SESSION['litotex_start_acp'] . 'acp/includes/perm.php');
$tpl->assign('menu_name', $menu_name);
if ($action == 'cp')
{
    if (!isset($_POST['cpfrom']) || !isset($_POST['cpto']))
    {
        error_msg('Es wurden nicht alle nötigen Daten übergeben.');
        exit;
    }
    $from = filter_var($_POST['cpfrom'], FILTER_SANITIZE_NUMBER_INT);
    $to = filter_var($_POST['cpto'], FILTER_SANITIZE_NUMBER_INT);

    if ($from == $to)
    {
        error_msg('Es ist nicht möglich auf die gleiche Rasse zu kopieren! Dies würde zu Datenverlust führen.');
        exit;
    }
    $race_to_q = $db->query("SELECT `rassenid` FROM `cc" . $n . "_rassen` WHERE `rassenid` = '" . $to . "'");
    $race_from_q = $db->query("SELECT `rassenid` FROM `cc" . $n . "_rassen` WHERE `rassenid` = '" . $from . "'");
    if (!$db->num_rows($race_from_q) || !$db->num_rows($race_to_q))
    {
        error_msg('Rasse existiert nicht.');
        exit;
    }
    $db->query("DELETE FROM `cc" . $n . "_explore` WHERE `race` = '" . $to . "'");
    $units_q = $db->query("SELECT * FROM `cc" . $n . "_explore` WHERE `race` = '" . $from . "'");
    while ($unit = $db->fetch_array($units_q))
    {
        $db->query("INSERT INTO `cc" . $n .
            "_explore` (`name`, `race`, `tabless`, `time`, `points`, `required`, `description`, `res1`, `res2`, `res3`, `res4`, `explorePic`, `p`) VALUES ('" .
            $db->escape_string($unit['name']) . "', '" . $to . "', '" . $unit['tabless'] . "', '" . $unit['time'] . "', '" . $unit['points'] .
            "', '" . $unit['required'] . "', '" . $unit['description'] . "', '" . $unit['res1'] . "', '" . $unit['res2'] . "', '" .
            $unit['res3'] . "', '" . $unit['res4'] . "', '" . $unit['explorePic'] . "', '" . $unit['p'] . "')");
    }
    redirect($modul_name, 'edit_explore', 'main');
}
elseif ($action == "main")
{

    $ras = "";
    $load_name = "";
    $load_ress_1 = "";
    $load_ress_2 = "";
    $load_ress_3 = "";
    $load_ress_4 = "";
    $load_buildtime = "";
    $load_points = "";
    $load_required = "";
    $load_b_pic = "";
    $description = "";
    $make_aktion = "";
    $error_buildings = "";
    $positions = 0;

    $make_aktion = "action=new&cxid=$sid";
    $build_option = make_explore_option_choice("build_option", "");
    $not_show = 0;

    $load_b_pic = LITO_IMG_PATH_URL . "acp_explore/keins.png";


    $result = $db->query("SELECT * FROM cc" . $n . "_rassen");
    $race_count = 0;
    $out_race = array();
    while ($row_ras = $db->fetch_array($result))
    {
        $race_count++;
        $out_race[] = $row_ras;
    }
    $tpl->assign('race_all', $out_race);


    $result_options = $db->query("SELECT * FROM cc" . $n . "_explore_option");
    $out_units = array();
    $count = 0;
    while ($row_b_option = $db->fetch_array($result_options))
    {
        $name = $row_b_option['description'];
        $name_tag = $row_b_option['tabless'];
        $value[0] = $name;
        for ($i = 1; $i <= $race_count; $i++)
        {
            //werte f�r die rassen suchen
            $value[$i] = "undefiniert";
            $result = $db->query("SELECT * FROM cc" . $n . "_explore where race='$i' and tabless='$name_tag'");
            $count = 0;
            while ($row_ras = $db->fetch_array($result))
            {
                $new_b_id = $row_ras['eid'];
                $del_link = "<a href=\"edit_explore.php?action=del&del_id=$new_b_id\"><img src=\"" . LITO_IMG_PATH_URL .
                    "acp_explore/delete.png\" alt=\"l&ouml;schen\" title =\"l&ouml;schen\" border=\"0\"></a> ";
                $value[$i] = $del_link . "<a href=\"edit_explore.php?id=" . $new_b_id . "\">" . $row_ras['name'] . "</a> ";
            }
        }
        $out_units[] = $value;
    }

    $tpl->assign('units', $out_units);

    $lade_id = (isset($_GET['id']) ? filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT) : 0);
    if ($lade_id > 0)
    {
        $not_show = 1;
        $result_loader = $db->query("SELECT * FROM cc" . $n . "_explore where eid='$lade_id'");

        while ($row_load = $db->fetch_array($result_loader))
        {
            $load_name = $row_load['name'];
            $load_ress_1 = $row_load['res1'];
            $load_ress_2 = $row_load['res2'];
            $load_ress_3 = $row_load['res3'];
            $load_ress_4 = $row_load['res4'];
            $load_required = $row_load['required'];
            $load_points = $row_load['points'];
            $load_buildtime = $row_load['time'];
            $load_b_pic = (empty($row_load['explorePic']) ? LITO_IMG_PATH_URL . "acp_explore/keins.png" : $row_load['explorePic']);

            $ras = make_race_choice("rasse", $row_load['race']);
            $build_option = make_explore_option_choice("build_option", $row_load['tabless']);
            $make_aktion = "action=update&cxid=$sid&id=$lade_id";
            $description = $row_load['description'];
            $positions = $row_load['p'];
        }
    }
    else
    {
        $ras = make_race_choice("rasse", 0);
    }

    //load error buildings
    $result_error = $db->query("SELECT * FROM cc" . $n . "_explore where race='' or tabless='' or tabless='0'");
    $count = 0;
    $error_buildings = "";
    while ($row_b_error = $db->fetch_array($result_error))
    {
        $name = $row_b_error['name'];
        $name_id = $row_b_error['eid'];
        $del_link = "<a href=\"edit_explore.php?action=del&del_id=$name_id\"><img src=\"" . LITO_IMG_PATH_URL .
            "acp_explore/delete.png\" alt=\"l&ouml;schen\" title =\"l&ouml;schen\" border=\"0\"></a> ";
        $new_name = $del_link . "<a href=\"edit_explore.php?id=$name_id\">$name</a> ";
        $error_buildings .= $new_name . "<br><br>";

    }

    $tpl->assign('ras', $ras);
    $tpl->assign('load_name', $load_name);
    $tpl->assign('load_ress_1', $load_ress_1);
    $tpl->assign('load_ress_2', $load_ress_2);
    $tpl->assign('load_ress_3', $load_ress_3);
    $tpl->assign('load_ress_4', $load_ress_4);
    $tpl->assign('buildtime', $load_buildtime);
    $tpl->assign('load_points', $load_points);
    $tpl->assign('load_required', $load_required);
    $tpl->assign('load_b_pic', $load_b_pic);
    $tpl->assign('description', $description);
    $tpl->assign('make_aktion', $make_aktion);
    $tpl->assign('error_buildings', $error_buildings);
    $tpl->assign('build_option', $build_option);
    $tpl->assign('positions', $positions);

    template_out('edit_explore.html', $modul_name);
}
elseif ($action == "update")
{
    $name = filter_var($_POST['explorename'], FILTER_SANITIZE_STRING);
    $explorepic = filter_var($_POST['buildpic'], FILTER_SANITIZE_STRING);

    $gold = filter_var($_POST['kost1'], FILTER_SANITIZE_NUMBER_INT);
    $stone = filter_var($_POST['kost2'], FILTER_SANITIZE_NUMBER_INT);
    $oil = filter_var($_POST['kost3'], FILTER_SANITIZE_NUMBER_INT);
    $exp = filter_var($_POST['kost4'], FILTER_SANITIZE_NUMBER_INT);

    $race = filter_var($_POST['rasse'], FILTER_SANITIZE_NUMBER_INT);
    $buildtime = filter_var($_POST['buildtime'], FILTER_SANITIZE_NUMBER_INT);
    $points = filter_var($_POST['points'], FILTER_SANITIZE_NUMBER_INT);

    $required = filter_var($_POST['required'], FILTER_SANITIZE_NUMBER_INT);
    $explore_id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
    $b_option = filter_var($_POST['build_option'], FILTER_SANITIZE_STRING);
    $description = filter_var($_POST['descr'], FILTER_SANITIZE_STRING);
    $positions = filter_var($_POST['positions'], FILTER_SANITIZE_NUMBER_INT);

    $kurz = get_explore_tabless_name($b_option);
    if ($explore_id <= 0)
    {
        error_msg("Ung�ltige Eingabe");
        exit();
    }


    $db->query("UPDATE cc" . $n . "_explore set tabless='' and race='' where tabless='$b_option' and race='$race'");
    $db->query("UPDATE cc" . $n . "_explore SET p='$positions',tabless='$b_option', name = '$name',res1='$gold',res2='$stone',res3='$oil',res4='$exp',time='$buildtime',race='$race',points='$points',required='$required',explorePic='$explorepic' ,description='$description' WHERE eid='$explore_id' ");

    redirect($modul_name, 'edit_explore', 'main');
}
elseif ($action == "new")
{
    $name = filter_var($_POST['explorename'], FILTER_SANITIZE_STRING);
    $explorepic = filter_var($_POST['explorepic'], FILTER_SANITIZE_STRING);

    $gold = filter_var($_POST['kost1'], FILTER_SANITIZE_NUMBER_INT);
    $stone = filter_var($_POST['kost2'], FILTER_SANITIZE_NUMBER_INT);
    $oil = filter_var($_POST['kost3'], FILTER_SANITIZE_NUMBER_INT);
    $exp = filter_var($_POST['kost4'], FILTER_SANITIZE_NUMBER_INT);

    $race = filter_var($_POST['rasse'], FILTER_SANITIZE_NUMBER_INT);
    $buildtime = filter_var($_POST['buildtime'], FILTER_SANITIZE_NUMBER_INT);
    $points = filter_var($_POST['points'], FILTER_SANITIZE_NUMBER_INT);

    $required = filter_var($_POST['required'], FILTER_SANITIZE_NUMBER_INT);
    $b_option = filter_var($_POST['build_option'], FILTER_SANITIZE_STRING);
    $description = filter_var($_POST['descr'], FILTER_SANITIZE_STRING);

    $kurz = get_explore_tabless_name($b_option);
    if (empty($kurz))
    {
        error_msg("Bitte einen Kurznamen eintragen!");
        exit();
    }

    if (if_spalte_exist($kurz, "cc" . $n . "_countries") == 0)
    {
        $update = $db->query("ALTER TABLE cc" . $n . "_countries ADD " . $kurz . " INT( 10 ) NOT NULL DEFAULT '0'");
    }

    $update = $db->query("UPDATE cc" . $n . "_explore set tabless='' and race='' where tabless='$b_option' and race='$race'");
    $update = $db->query("Insert Into cc" . $n .
        "_explore (description,name,race,tabless,res1,res2,res3,res4,time,points,required,explorePic)VALUES ('$description','$name','$race','$kurz','$gold','$stone','$oil','$exp','$buildtime','$points','$required','$explorepic') ");

    redirect($modul_name, 'edit_explore', 'main');
}
elseif ($action == "del")
{
    $del_id = filter_var($_GET['del_id'], FILTER_SANITIZE_NUMBER_INT);
    if ($del_id <= 0)
    {
        error_msg("Vorgang kann nicht ausgef&uuml;hrt werden!");
        exit();
    }
    // search for old
    $result = $db->query("SELECT tabless,eid FROM cc" . $n . "_explore WHERE eid='" . $del_id . "'");
    $row = $db->fetch_array($result);
    if (!empty($row['tabless']))
    {
        $db->query("delete from cc" . $n . "_explore where eid='" . $del_id . "'");
    }

    redirect($modul_name, 'edit_explore', 'main');
}
