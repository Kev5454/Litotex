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

if (version_compare(phpversion(), '5.2.0') <= 0)
{
    echo 'Sie benötigen mindestens PHP 5.2.0\n um diese Engine nutzen zu können!';
    exit();
}
if (version_compare(phpversion(), '7.0.0') >= 0)
{
    define('PHP7', true);
}

session_name("lito");
if (session_id() == "")
{
    session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/html; charset=utf-8');

$time_start = explode(' ', substr(microtime(), 1));
$time_start = $time_start[1] + $time_start[0];

$sid = session_id();
//$Database index
$n = 1;

if (isset($_SESSION['litotex_start_g']))
{
    $litotex_path = $_SESSION['litotex_start_g'];
    $litotex_url = $_SESSION['litotex_start_g_url'];
}
else
{
    if (is_file('.' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'config.php'))
    {
        require ('.' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'config.php');
    }
    elseif (is_file('.' . DIRECTORY_SEPARATOR . 'config.php'))
    {
        require ('.' . DIRECTORY_SEPARATOR . 'config.php');
    }
    elseif (is_file('.' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' .
        DIRECTORY_SEPARATOR . 'config.php'))
    {
        require ('.' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' .
            DIRECTORY_SEPARATOR . 'config.php');
    }
    else
    {
        echo ("Litotex System Error");
        exit();
    }
    $_SESSION['litotex_start_g'] = $litotex_path;
    $_SESSION['litotex_start_g_url'] = $litotex_url;
}

/** get db class **/
define("LITO_INCLUDES_PATH", $litotex_path . 'includes' . DIRECTORY_SEPARATOR);
require (LITO_INCLUDES_PATH . 'config.php');
require (LITO_INCLUDES_PATH . 'class_db_mysql.php');


$db = new db($dbhost, $dbuser, $dbpassword, $dbbase, $dbport);
if ($db->connect() !== true)
{
    echo 'Datenbank konnte keine Verbindung aufbauen!';
    exit();
}

$db->unbuffered_query("SET character_set_client = 'utf8'");
$db->unbuffered_query("SET character_set_connection = 'utf8'");

// get design
if (isset($_SESSION['userid']))
{
    $result_id = $db->query("SELECT design_id FROM cc" . $n . "_users where userid ='" . $_SESSION['userid'] . "'");
    $row_id = $db->fetch_array($result_id);
    if (intval($row_id['design_id']) > 0)
    {
        $theme_1 = $db->query("SELECT `design_name` FROM `cc" . $n . "_desigs` WHERE `design_id` = '" . $row_id['design_id'] .
            "'");
        $row_theme = $db->fetch_array($theme_1);
        define("LITO_THEMES", $row_theme['design_name']);
    }
}
else
{
    define("LITO_THEMES", 'standard');
}


define("LITO_ROOT_PATH", $litotex_path);
// e.g.  /srv/www/vhosts/freebg.de/subdomains/dev/httpdocs/
define("LITO_ROOT_PATH_URL", $litotex_url);
// e.g.  http://dev.freebg.de/

define("LITO_THEMES_PATH", $litotex_path . 'themes' . DIRECTORY_SEPARATOR . LITO_THEMES . DIRECTORY_SEPARATOR);
// e.g.  srv/www/vhosts/freebg.de/subdomains/dev/httpdocs/themes/standard/
define("LITO_THEMES_PATH_URL", $litotex_url . 'themes' . DIRECTORY_SEPARATOR . LITO_THEMES . DIRECTORY_SEPARATOR);
// e.g.  http://dev.freebg.de/themes/standard/

define("LITO_IMG_PATH", $litotex_path . 'images' . DIRECTORY_SEPARATOR . LITO_THEMES . DIRECTORY_SEPARATOR);
// e.g.  /srv/www/vhosts/freebg.de/subdomains/dev/httpdocs/images/standard/
define("LITO_IMG_PATH_URL", $litotex_url . 'images' . DIRECTORY_SEPARATOR . LITO_THEMES . DIRECTORY_SEPARATOR);
// e.g.  http://dev.freebg.de/images/standard/

define("LITO_MODUL_PATH", $litotex_path . 'modules' . DIRECTORY_SEPARATOR);
// e.g.  /srv/www/vhosts/freebg.de/subdomains/dev/httpdocs/modules/
define("LITO_MODUL_PATH_URL", $litotex_url . 'modules' . DIRECTORY_SEPARATOR);
// e.g.  http://dev.freebg.de/modules/

define("LITO_LANG_PATH", $litotex_path . 'lang' . DIRECTORY_SEPARATOR);
// e.g.  /srv/www/vhosts/freebg.de/subdomains/dev/httpdocs/lang/
define("LITO_LANG_PATH_URL", $litotex_url . 'lang' . DIRECTORY_SEPARATOR);
// e.g.  http://dev.freebg.de/lang/

define("LITO_MAIN_CSS", $litotex_url . 'css' . DIRECTORY_SEPARATOR . LITO_THEMES);
define("LITO_JS_URL", $litotex_url . 'js' . DIRECTORY_SEPARATOR);
define("LITO_GLOBAL_IMAGE_URL", $litotex_url . 'images' . DIRECTORY_SEPARATOR);

$lang_suffix = (isset($_SESSION['lang']) && strlen($_SESSION['lang']) == 2 ? $_SESSION['lang'] : "de");

/** get options **/
require (LITO_ROOT_PATH . 'options' . DIRECTORY_SEPARATOR . 'options.php');
require ('functions.php');
require (LITO_INCLUDES_PATH . 'smarty' . DIRECTORY_SEPARATOR . 'Smarty.class.php'); // Smarty class laden und pr�fen


$tpl = new smarty;
$tpl->template_dir = LITO_THEMES_PATH;
$tpl->compile_dir = LITO_ROOT_PATH . 'templates_c' . DIRECTORY_SEPARATOR . LITO_THEMES;
$tpl->cache_dir = LITO_ROOT_PATH . 'cache' . DIRECTORY_SEPARATOR . LITO_THEMES;

if (!is_dir($tpl->compile_dir))
{
    _mkdir('templates_c' . DIRECTORY_SEPARATOR . LITO_THEMES, false);
}
if (!is_dir($tpl->cache_dir))
{
    _mkdir('cache' . DIRECTORY_SEPARATOR . LITO_THEMES, false);
}
//$tpl->debugging = true;

setlocale(LC_ALL, array(
    'de_DE',
    'de_DE@euro',
    'de',
    'ger'));
date_default_timezone_set('Europe/Berlin');

$lang_file = LITO_LANG_PATH . 'core' . DIRECTORY_SEPARATOR . 'lang_' . $lang_suffix . '.php';
$tpl->config_load($lang_file);

$tpl->assign('GAME_TITLE_TEXT', $op_set_gamename);
$is_loged_in = 0;


if (isset($_SESSION['userid']))
{
    $is_loged_in = 1;
    // if Game Online or Offline
    if ($op_set_offline == 1 && $modul_name != "logout")
    {
        show_error($op_set_offline_message, "core", 0);
        exit();
    }

    // load Userdata array
    $result = $db->query("SELECT u.*,c.* FROM cc" . $n . "_users AS u, cc" . $n . "_countries AS c WHERE u.userid='" . $_SESSION['userid'] .
        "' AND u.activeid=c.islandid");
    $userdata = $db->fetch_array($result);

    if ($userdata['activeid'] == 0)
    {
        $result = $db->query("SELECT islandid,userid FROM cc" . $n . "_countries WHERE userid='" . $userdata['userid'] .
            "' ORDER BY islandid ASC LIMIT 1");
        $row = $db->fetch_array($result);

        $db->unbuffered_query("UPDATE cc" . $n . "_users SET activeid='" . $row['islandid'] . "' WHERE userid='" . $userdata['userid'] .
            "'");

        $userdata['name'] = $row['name'];
        $userdata['x'] = $row['x'];
        $userdata['y'] = $userdata['y'];
        $userdata['res1'] = $row['res1'];
        $userdata['res2'] = $row['res2'];
        $userdata['res3'] = $row['res3'];
        $userdata['res4'] = $row['res4'];
    }
    $db->unbuffered_query("UPDATE cc" . $n . "_users SET lastactive='" . time() . "' WHERE userid='" . $userdata['userid'] .
        "'");

    // check race
    if ($userdata['rassenid'] == 0 && $modul_name != "members" && $modul_name != "logout" && $modul_name != "navigation" &&
        $modul_name != "ajax_core")
    {
        header("LOCATION: " . LITO_MODUL_PATH_URL . 'members' . DIRECTORY_SEPARATOR . 'members.php?action=race_choose');
        exit();
    }

    $result_land = $db->query("SELECT * FROM cc" . $n . "_countries WHERE userid='" . $userdata['userid'] .
        "' ORDER BY islandid");

    $sql = '';
    while ($row_land = $db->fetch_array($result_land))
    {
        if (((int)$row_land['isbuilding']) == 1 && $row_land['endbuildtime'] <= time())
        {
            $result = $db->query("SELECT * FROM cc" . $n . "_buildings WHERE bid='" . $row_land['bid'] . "'");
            $row = $db->fetch_array($result);

            if ($db->num_rows($result) > 0)
            {
                $sql .= "UPDATE cc" . $n . "_countries SET " . $row['tabless'] . "=" . $row['tabless'] .
                    "+'1', bid='0', isbuilding='0', startbuildtime='0', endbuildtime='0' WHERE islandid='" . $row_land['islandid'] . "';";
            }
        }
        if (((int)$row_land['isexploring']) == 1 && $row_land['endexploretime'] <= time())
        {
            $result = $db->query("SELECT * FROM cc" . $n . "_explore WHERE eid='" . $row_land['eid'] . "'");
            $row = $db->fetch_array($result);

            if ($db->num_rows($result) > 0)
            {
                $sql .= "UPDATE cc" . $n . "_countries SET " . $row['tabless'] . "=" . $row['tabless'] .
                    "+1, eid='0', startexploretime='0', endexploretime='0', isexploring='0' WHERE islandid='" . $row_land['islandid'] . "';";
            }
        }

        $result = $db->query("SELECT * FROM cc" . $n . "_create_sol WHERE island_id = '" . $row_land['islandid'] .
            "' ORDER BY create_sol_id");
        if ($db->num_rows($result) > 0)
        {
            while ($row_create_sol = $db->fetch_array($result))
            {
                $result2 = $db->query("SELECT * FROM cc" . $n . "_soldiers WHERE sid = '" . $row_create_sol['sid'] . "'");
                $row_soldiers = $db->fetch_array($result2);

                if ($row_create_sol['endtime'] <= time())
                {
                    $sql .= "UPDATE cc" . $n . "_countries SET " . $row_soldiers['tabless'] . "=" . $row_soldiers['tabless'] . "+'" . $row_create_sol['anz'] .
                        "' WHERE islandid='" . $row_land['islandid'] . "';";
                    $sql .= "DELETE FROM cc" . $n . "_create_sol WHERE create_sol_id = '" . $row_create_sol['create_sol_id'] . "';";
                }
            }
        }

        $store_max = $op_set_store_max * (($row_land['store'] + 1) * $op_store_mulit);
        if ($row_land['res1'] > $store_max)
        {
            $sql .= "UPDATE cc" . $n . "_countries SET res1='$store_max' WHERE islandid='" . $row_land['islandid'] . "';";
        }
        if ($row_land['res2'] > $store_max)
        {
            $sql .= "UPDATE cc" . $n . "_countries SET res2='$store_max' WHERE islandid='" . $row_land['islandid'] . "';";
        }
        if ($row_land['res3'] > $store_max)
        {
            $sql .= "UPDATE cc" . $n . "_countries SET res3='$store_max' WHERE islandid='" . $row_land['islandid'] . "';";
        }
        if ($row_land['res4'] > $store_max)
        {
            $sql .= "UPDATE cc" . $n . "_countries SET res4='$store_max' WHERE islandid='" . $row_land['islandid'] . "';";
        }

        if (isset($userdata['activeid']) && $userdata['activeid'] == $row_land['islandid'])
        {
            $userdata['store_max'] = $store_max;
            $userdata['res1'] = $row_land['res1'];
            $userdata['res2'] = $row_land['res2'];
            $userdata['res3'] = $row_land['res3'];
            $userdata['res4'] = $row_land['res4'];
        }
        resreload($row_land['islandid']);
    }
    if (!empty($sql))
    {
        $db->multi_query($sql);
    }

    // check allianz
    if ($userdata['allianzid'] != 0)
    {
        $result = $db->query("SELECT * FROM cc" . $n . "_allianz WHERE aid='" . $userdata['allianzid'] . "'");
        $allianz = $db->fetch_array($result);
    }
    $banner = get_banner_code();

    $tpl->assign('GLOBAL_BANNERCODE', $banner);
    $tpl->assign('GLOBAL_STORE_SIZE', prettyNumber($userdata['store_max']));
    $tpl->assign('CURRENT_LAND_NAME', $userdata['name']);
    $tpl->assign('CURRENT_LAND_POS', $userdata['x'] . ":" . $userdata['y']);
    $tpl->assign('CURRENT_LAND_RES1', prettyNumber($userdata['res1']));
    $tpl->assign('CURRENT_LAND_RES2', prettyNumber($userdata['res2']));
    $tpl->assign('CURRENT_LAND_RES3', prettyNumber($userdata['res3']));
    $tpl->assign('CURRENT_LAND_RES4', prettyNumber($userdata['res4']));
    $tpl->assign('GLOBAL_RES1_NAME', $op_set_n_res1);
    $tpl->assign('GLOBAL_RES2_NAME', $op_set_n_res2);
    $tpl->assign('GLOBAL_RES3_NAME', $op_set_n_res3);
    $tpl->assign('GLOBAL_RES4_NAME', $op_set_n_res4);
}

$tpl->assign('IS_LOGED_IN', $is_loged_in);
