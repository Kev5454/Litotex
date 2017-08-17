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

$modul_name = "exploring";

if (is_modul_name_aktive($modul_name) == 0)
{
    show_error('MODUL_LOAD_ERROR', 'core');
    exit();
}

$lang_file = LITO_LANG_PATH . $modul_name . '/lang_' . $lang_suffix . '.php';
$tpl->config_load($lang_file);
$exploreabort = $tpl->get_config_vars('EXPLORE_ABORT');
$explore = $tpl->get_config_vars('EXPLORE');
$explore_error = $tpl->get_config_vars('EXPLORING_ERROR_6');

if ($action == "main")
{
    timebanner_init(200, 1);
    if ($userdata['isexploring'] == "1" and $userdata['endexploretime'] > time())
    {
        $show_bau = 1;
    }
    elseif ($userdata['isexploring'] == "1" and $userdata['endexploretime'] <= time())
    {
        $result = $db->query("SELECT * FROM cc" . $n . "_explore WHERE eid='" . $userdata['eid'] . "'");
        $row = $db->fetch_array($result);
        if ($row['tabless'] == "")
        {
            $db->query("UPDATE cc" . $n . "_countries SET startexploretime='0', endexploretime='0',isexploring='0' WHERE islandid='$userdata[activeid]'");
        }
        else
        {
            $db->query("UPDATE cc" . $n . "_countries SET " . $row['tabless'] . "=" . $row['tabless'] .
                "+1, eid='0', startexploretime='0', endexploretime='0', isexploring='0' WHERE islandid='$userdata[activeid]'");
        }
        header("LOCATION: exploring.php");
        exit();

    }

    $requesttime = $userdata['endexploretime'] - time();
    $new_found = array();

    $result_explores = $db->query("SELECT * FROM cc" . $n . "_explore where race = " . $userdata['rassenid'] .
        " and (tabless !='' and tabless !='0' ) ORDER BY eid ASC");
    while ($row_explores = $db->fetch_array($result_explores))
    {
        if ($row_explores['required'] <= $userdata['build_explore'])
        {
            $size = $userdata[$row_explores['tabless']];
            $size_new = $userdata[$row_explores['tabless']] + 1;
            $buildtime = sec2time($row_explores['time'] * $size_new);

            $res1 = $row_explores['res1'] * ($size + 1);
            $res2 = $row_explores['res2'] * ($size + 1);
            $res3 = $row_explores['res3'] * ($size + 1);
            $res4 = $row_explores['res4'] * ($size + 1);

            $anzeige_res1 = $op_set_n_res1 . ": " . $res1;
            $anzeige_res2 = $op_set_n_res2 . ": " . $res2;
            $anzeige_res3 = $op_set_n_res3 . ": " . $res3;
            $anzeige_res4 = $op_set_n_res4 . ": " . $res4;

            if (isset($show_bau) and $show_bau == 1)
            {
                $time_2_go = sec2time($requesttime);
                if ($userdata['eid'] == $row_explores['eid'])
                {
                    $es_wird_gebaut = 1;
                    $timer = time();
                    $cancelURL = "exploring.php?eid=$userdata[eid]&action=del";
                    $message = make_timebanner($userdata['startexploretime'], $userdata['endexploretime'], $row_explores['eid'], "") .
                        "<br><a href=\"$cancelURL\">" . $exploreabort . "</a>";
                }
                else
                {
                    $es_wird_gebaut = 1;
                    $message = 'Es wird bereits geforscht! ';
                }
            }
            else
            {
                $message = "<a href=\"exploring.php?action=explore&eid=$row_explores[eid]\">$explore</a>";
            }
            $image = "\"" . $row_explores['explorePic'] . "\"";
            $new_found[] = array(
                $image,
                $row_explores['name'],
                $size,
                $row_explores['description'],
                $anzeige_res1,
                $anzeige_res2,
                $anzeige_res3,
                $anzeige_res4,
                $buildtime,
                $message,
                );
        }
    }
    $tpl->assign('daten',$new_found);
    template_out('exploring.html', $modul_name);
    exit();
}

if ($action == "explore")
{
    $eid = intval($_GET['eid']);
    if (!$eid)
    {
        show_error('EXPLORING_ERROR_1', $modul_name);
        exit();
    }

    $result = $db->query("SELECT * FROM cc" . $n . "_explore WHERE eid='$eid'");
    $row = $db->fetch_array($result);

    if ($row['required'] > $userdata['build_explore'])
    {
        show_error('EXPLORING_ERROR_2', $modul_name);
        exit();
    }

    $res1 = $row['res1'] * ($userdata[$row['tabless']] + 1);
    $res2 = $row['res2'] * ($userdata[$row['tabless']] + 1);
    $res3 = $row['res3'] * ($userdata[$row['tabless']] + 1);
    $res4 = $row['res4'] * ($userdata[$row['tabless']] + 1);


    if ($res1 > $userdata['res1'] || $res2 > $userdata['res2'] || $res3 > $userdata['res3'] || $res4 > $userdata['res4'])
    {
        show_error('EXPLORING_ERROR_3', $modul_name);
        exit();
    }

    $endexploretime = time() + ($row['time'] * ($userdata[$row['tabless']] + 1));
    $startexploretime = time();
    $db->query("UPDATE cc" . $n . "_countries SET eid='$eid', startexploretime='$startexploretime', endexploretime='$endexploretime', isexploring='1', res1=res1-'$res1', res2=res2-'$res2', res3=res3-'$res3', res4=res4-'$res4' WHERE islandid='$userdata[activeid]'");
    header("LOCATION: exploring.php");
    exit();
}

if ($action == "del")
{
    $eid = intval($_GET['eid']);

    $result_b = $db->query("SELECT eid FROM cc" . $n . "_countries WHERE islandid='" . $userdata['activeid'] . "' ");
    $row_in_b = $db->fetch_array($result_b);

    $in_bau = intval($row_in_b['eid']);
    if ($in_bau <= 0)
    {
        show_error('EXPLORING_ERROR_4', $modul_name);
        exit();
    }

    if ($in_bau != $eid)
    {
        show_error('EXPLORING_ERROR_5', $modul_name);
        exit();
    }

    $result = $db->query("SELECT * FROM cc" . $n . "_explore WHERE eid='$eid'");
    $row = $db->fetch_array($result);

    $us_size = $userdata[$row['tabless']] + 1;
    $size = $userdata[$row['tabless']];

    $res1 = ($row['res1'] * $us_size) * ($op_credit_cancel / 100);
    $res2 = ($row['res2'] * $us_size) * ($op_credit_cancel / 100);
    $res3 = ($row['res3'] * $us_size) * ($op_credit_cancel / 100);
    $res4 = ($row['res4'] * $us_size) * ($op_credit_cancel / 100);

    $a_user_id = $userdata['userid'];
    $u_db_name = username($a_user_id);
    $bauname = $row['name'];
    $bau_land = get_island($userdata['activeid']);
    $new_size = $size;
    trace_msg("User $u_db_name bricht Forschung $bauname auf Land $bau_land ab", 4);

    $db->query("UPDATE cc" . $n . "_countries SET eid='0', res1=res1+'$res1', res2=res2+'$res2', res3=res3+'$res3', res4=res4+'$res4', endexploretime='0', startexploretime='0', isexploring='0' WHERE islandid='" .
        $userdata['activeid'] . "'");
    header("LOCATION: exploring.php");
    exit();
}

?>