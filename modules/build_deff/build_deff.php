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

require ($_SESSION['litotex_start_g'] . 'includes/global.php');
$action = (isset($_REQUEST['action']) ? filter_var($_REQUEST['action'], FILTER_SANITIZE_STRING) : 'main');
$modul_name = "build_deff";

if (is_modul_name_aktive($modul_name) == 0)
{
    show_error('MODUL_LOAD_ERROR', 'core');
    exit();
}

$lang_file = LITO_LANG_PATH . $modul_name . '/lang_' . $lang_suffix . '.php';
$tpl->config_load($lang_file);
$builddeff11 = $tpl->get_config_vars('BUILD_DEFF_11');
$builddeff12 = $tpl->get_config_vars('BUILD_DEFF_12');

if ($action == "main")
{
    timebanner_init(200, 1);
    $new_found_inhalt = array();
    $new_found = array();
    $new_found_inhalt_loop = array();
    $new_found_loop = array();

    $result = $db->query("SELECT * FROM cc" . $n . "_create_sol WHERE island_id = $userdata[activeid] AND sol_type = '1' ORDER BY create_sol_id");
    $z = 0;
    while ($row = $db->fetch_array($result))
    {

        $result2 = $db->query("SELECT * FROM cc" . $n . "_soldiers WHERE sid = $row[sid]");
        $row2 = $db->fetch_array($result2);

        $sol_tabless = $row2['tabless'];
        $sol_time = $row2['stime'];
        $name = $row2['name'];

        $numOfMans = $row['anz'];
        $time_ready = date("d.m.H:i:s", $row['endtime']);

        $cancelURL = "build_deff.php?csi=$row[create_sol_id]&action=del";

        if ($z == 0)
        {

            $numOfMans = ceil($requesttime / $sol_time); //anz der noch zu produzierenden einheiten anhand der restzeit
            $anz_fertig = floor($row['anz'] - $numOfMans); //anz der fertigen einheiten

            if ($anz_fertig != 0)
            {
                $starttime = $row['starttime'] + ($anz_fertig * $sol_time);
                $db->query("UPDATE cc" . $n . "_countries SET " . $sol_tabless . "=" . $sol_tabless . "+'$anz_fertig' WHERE islandid='$userdata[activeid]'");
                $db->query("UPDATE cc" . $n . "_create_sol SET anz= anz - '$anz_fertig' WHERE create_sol_id='$row[create_sol_id]'");
            }
            else
            {
                $starttime = $row['starttime'];
            }

            if (fmod($requesttime, $sol_time))
            {
                $time_for_one = fmod($requesttime, $sol_time); //rechnet den rest der zeit f�r aktuelle Einheit aus
            }
            else
            {
                $time_for_one = $sol_time;
            }
            $endtime = $row['endtime'] - ($requesttime - $time_for_one);
            $backurl = "build_deff.php";
            $time_one = make_timebanner($starttime, $endtime, $z, $backurl);
        }
        else
        {
            $time_one = $builddeff12;
        }
        $message = "<br><a href=\"$cancelURL\">$builddeff11</a>";
        $z++;
        $new_found_inhalt_loop = array(
            $z,
            $name,
            $numOfMans,
            $time_one,
            $time_ready,
            $message);
        array_push($new_found_loop, $new_found_inhalt_loop);
    }
    if ($z != 0)
    {
        $tpl->assign('loop', $new_found_loop);
    }

    $result = $db->query("SELECT * FROM cc" . $n . "_soldiers where race = " . $userdata['rassenid'] .
        " AND sol_type = 1 ORDER BY sid");
    while ($row = $db->fetch_array($result))
    {
        if ($userdata[$row['required']] >= $row['required_level'])
        {
            $resultt = $db->query("SELECT * FROM cc" . $n . "_soldiers where race = " . $userdata['rassenid'] .
                " AND sol_type = 1 ORDER BY name");
            while ($rowt = $db->fetch_array($resultt))
            {

            }

            if ($row['res1'] <= 0) $row['res1']++;
            if ($row['res2'] <= 0) $row['res2']++;
            if ($row['res3'] <= 0) $row['res3']++;
            if ($row['res4'] <= 0) $row['res4']++;

            $numOfMans = $userdata[$row['tabless']];
            $build_time = sec2time($row['stime']);
            $maxi = min(floor($userdata['res1'] / $row['res1']), floor($userdata['res2'] / $row['res2']), floor($userdata['res3'] /
                $row['res3']), floor($userdata['res4'] / $row['res4']));
            $url = "\"build_deff.php?action=create&soid=$row[sid]\"";
            $new_found_inhalt = array(
                "\"" . $row['solpic'] . "\"",
                $row['name'],
                $row['description'],
                $op_set_n_res1 . ": " . $row['res1'],
                $op_set_n_res2 . ": " . $row['res2'],
                $op_set_n_res3 . ": " . $row['res3'],
                $op_set_n_res4 . ": " . $row['res4'],
                $build_time,
                $numOfMans,
                $url,
                $maxi,
                $row['AP'],
                $row['VP']);
            array_push($new_found, $new_found_inhalt);
        }
    }
    $tpl->assign('daten', $new_found);
    template_out('build_deff.html', $modul_name);
    exit();
}


if ($action == "create")
{
    $num = intval($_POST['num']);
    $soid = intval($_GET['soid']);
    if ($num == 0 || $soid == 0 || $num < 0)
    {
        show_error('BUILD_DEFF_ERROR_1', $modul_name);
        exit();
    }
    $result = $db->query("SELECT * FROM cc" . $n . "_soldiers WHERE sid='$soid'");
    $row = $db->fetch_array($result);

    $row['res1'] = $row['res1'] * $num;
    $row['res2'] = $row['res2'] * $num;
    $row['res3'] = $row['res3'] * $num;
    $row['res4'] = $row['res4'] * $num;

    if ($row['res1'] > $userdata['res1'] || $row['res2'] > $userdata['res2'] || $row['res3'] > $userdata['res3'] || $row['res4'] >
        $userdata['res4'])
    {
        show_error('BUILD_DEFF_ERROR_2', $modul_name);
        exit();
    }

    $result2 = $db->query("SELECT island_id, endtime, starttime, create_sol_id FROM cc" . $n .
        "_create_sol WHERE island_id = $userdata[activeid] AND sol_type = 1 ORDER BY create_sol_id DESC LIMIT 1");
    if ($row2 = $db->fetch_array($result2))
    {
        $endtime = $row2['endtime'] + ($row['stime'] * $num);
        $starttime = $row2['endtime'];
    }
    else
    {
        $endtime = time() + ($row['stime'] * $num);
        $starttime = time();
    }
    $db->query("UPDATE cc" . $n . "_countries SET res1=res1-'$row[res1]', res2=res2-'$row[res2]', res3=res3-'$row[res3]', res4=res4-'$row[res4]' WHERE islandid='$userdata[activeid]'");

    $db->query("INSERT INTO cc" . $n . "_create_sol (island_id,sid,anz,starttime,endtime,sol_type) values ($userdata[activeid],$soid,$num,$starttime,$endtime,1)");

    header("LOCATION: build_deff.php");
    exit();
}
if ($action == "del")
{

    $create_sol_id = intval($_GET['csi']);

    $result_del = $db->query("SELECT * FROM cc" . $n . "_create_sol WHERE create_sol_id='$create_sol_id'");
    $row_del = $db->fetch_array($result_del);

    if ($row_del['island_id'] != $userdata['activeid'])
    {
        show_error('BUILD_DEFF_ERROR_3', $modul_name);
        exit();
    }
    else
    {

        $zeit_abzug = $row_del['endtime'] - $row_del['starttime'];
        if ($zeit_abzug > ($row_del['endtime'] - time())) $zeit_abzug = $row_del['endtime'] - time();


        $result = $db->query("SELECT * FROM cc" . $n . "_create_sol WHERE island_id = $userdata[activeid] AND sol_type = 1 AND create_sol_id > '" .
            $create_sol_id . "' ORDER BY create_sol_id");
        while ($row = $db->fetch_array($result))
        {

            $new_starttime = $row['starttime'] - $zeit_abzug;
            $new_endtime = $row['endtime'] - $zeit_abzug;
            $db->query("UPDATE cc" . $n . "_create_sol SET starttime='" . $new_starttime . "', endtime='" . $new_endtime .
                "' WHERE create_sol_id='" . $row['create_sol_id'] . "'");
        }


        $db->query("DELETE FROM cc" . $n . "_create_sol WHERE create_sol_id = $create_sol_id");
    }
    header("LOCATION: build_deff.php");
    exit();
}
