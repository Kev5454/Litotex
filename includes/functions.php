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

if (!function_exists('session_unregister'))
{
    function session_unregister($name)
    {
        unset($_SESSION[$name]);
    }
}

function _mkdir($directory, $_mode = 0777, $setroot = true)
{
    $dir = ($setroot ? LITO_ROOT_PATH . DIRECTORY_SEPARATOR : '');
    mkdir($dir . $directory);
    chmod($dir . $directory, $_mode);
}


function show_error($error_msg, $from_modul, $load_from_lang = 1)
{
    global $tpl, $db, $n, $userdata, $lang_suffix;
    if ($load_from_lang == 1)
    {
        $tpl->config_load(LITO_LANG_PATH . $from_modul . '/lang_' . $lang_suffix . '.php');
        $tpl->assign('LITO_ERROR_MSG', $tpl->get_config_vars($error_msg));
    }
    else
    {
        $tpl->assign('LITO_ERROR_MSG', $error_msg);
    }

    $tpl->assign('if_login_error', 1);
    template_out('error.html', 'core');
    exit();
}

function prettyNumber($value, $decimal = 0)
{
    return number_format($value, $decimal, ',', '.');
}


function redirect($module, $file, $action = 'main', $vars = array())
{
    $get = '';
    if (count($vars) > 0)
    {
        $get .= '&';
        foreach ($vars as $key => $value)
        {
            $get .= $key . '=' . $value . '&';
        }
        $get = substr($get, 0, -1);
    }

    header("LOCATION: " . LITO_ROOT_PATH_URL . "modules/" . $module . "/" . $file . ".php?action=" . $action . $get);
    exit();
}

/** create an password **/
function password($char)
{
    $length = intval($char);
    if ($length < 5)
    {
        $length = 6;
    }
    $password = "";
    $pool = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $pool .= "abcdefghijklmnopqrstuvwxyz";
    $pool .= "1234567890";

    srand((double)microtime() * 1000000);
    for ($i = 0; $i < intval($length); $i++)
    {
        $password .= $pool{rand(0, strlen($pool) - 1)};
    }
    return $password;
}

function get_footer()
{
    global $db, $time_start;

    $time_end = explode(' ', substr(microtime(), 1));
    $time_end = $time_end[1] + $time_end[0];
    $run_time = $time_end - $time_start;

    return "time: " . number_format($run_time, 5, '.', '') . " sec <br>query count: " . $db->number_of_querys();
}

function is_modul_name_aktive($modul_name)
{
    global $db, $n, $userdata;

    static $cache = array();
    if (!isset($cache[$modul_name]))
    {
        $result = $db->query("SELECT activated FROM cc" . $n . "_modul_admin where modul_name ='$modul_name'");
        $row = $db->fetch_array($result);
        $cache[$modul_name] = (int)($row['activated']);
    }
    return $cache[$modul_name];
}

function is_modul_id_aktive($modul_id)
{
    global $db, $n, $userdata;

    static $cache = array();
    if (!isset($cache[$modul_id]))
    {
        $result = $db->query("SELECT activated FROM cc" . $n . "_modul_admin where modul_admin_id ='$modul_id'");
        $row = $db->fetch_array($result);
        $cache[$modul_id] = (int)($row['activated']);
    }
    return $cache[$modul_id];

}
function c_trim($string)
{
    $replace = strip_tags($string);
    $replace = str_replace("'", "", $replace);
    return trim($replace);
}


function get_navigation($modulname)
{
    global $db, $tpl, $lang_suffix, $n, $is_loged_in;
    // get_modulname
    // return array
    // 0 = modulname
    // 1 = modulstartfile
    // 2 = modul ID
    // 3 = Show Error 0/1
    $module = get_modulname(1);

    if (is_modul_id_aktive($module['modul_admin_id']) == 1)
    {

        include (LITO_MODUL_PATH . $module['modul_name'] . '/' . $module['startfile']);

        $navi = new navigation();
        //hauptnavi
        $rrr = $navi->make_navigation($modulname, $module['modul_admin_id'], $is_loged_in, 0);
        $tpl->assign('LITO_NAVIGATION', $rrr);
        //navigation left side
        $rrr_1 = $navi->make_navigation($modulname, $module['modul_admin_id'], $is_loged_in, 1);
        $tpl->assign('LITO_NAVIGATION_1', $rrr_1);

        //navigation right side
        $rrr_2 = $navi->make_navigation($modulname, $module['modul_admin_id'], $is_loged_in, 2);
        $tpl->assign('LITO_NAVIGATION_2', $rrr_2);

        unset($navi);


    }
    else
    {
        return ($module['show_error_msg'] == 0 ? '' : 'Modul wurde deaktiviert.');
    }
}


function get_modulname($modul_type)
{
    // $modul_type
    // 0 =  default modul with Links
    // 1 =  naviagtion Modul
    // 2 =  GAME Members Modul
    // 3 =  GAME Ranking Modul
    // 4 =  GAME Alliance Modul
    // 5 =  GAME Country Modul
    // 6 =  GAME Message Modul
    // 7 =  GAME Map Modul
    // 8 =  GAME UserEditor Modul
    // 9 =  GAME Battle Modul
    // 10 = GAME Buildings Modul
    // 11 = GAME Build Units Modul
    // 12 = GAME Buid Def Modul
    // 13 = GAME Group Modul
    // 14 = GAME Explore Modul
    // 15 = GAME Spion Modul
    // 16 = GAME Techtree Modul
    // 17 = GAME Search Modul
    // 18 = GAME Alliance Forum Modul
    // 19 = GAME Login
    // 20 = GAME Registrieren
    // 21 = GAME News
    // 22 = GAME Screenshot
    // 23 = GAME impressum
    // 24 = GAME Upload_PIC
    // 25 = GAME CoreModul
    // 26 = GAME usr_signature
    // 27 = Srpachwechsler
    global $tpl, $db, $n;

    static $cache = array();
    if (!isset($cache[$modul_type]))
    {
        $result = $db->query("SELECT modul_name,startfile,modul_admin_id,show_error_msg,acp_modul,modul_type   FROM cc" . $n .
            "_modul_admin where acp_modul ='0' and modul_type='$modul_type'");
        $cache[$modul_type] = $db->fetch_array($result);
    }
    return $cache[$modul_type];
}

function template_out($template_name, $from_modulname)
{
    global $db, $tpl, $lang_suffix, $n, $userdata, $is_loged_in;

    $lang_file = LITO_LANG_PATH . $from_modulname . '/lang_' . $lang_suffix . '.php';
    $tpl->config_load($lang_file);

    if (intval($userdata['rassenid']) > 0 || $is_loged_in == 0)
    {
        get_navigation($from_modulname);
    }

    $tpl->assign('LITO_GLOBAL_IMAGE_URL', LITO_GLOBAL_IMAGE_URL);
    $tpl->assign('LITO_IMG_PATH', LITO_IMG_PATH_URL . $from_modulname . DIRECTORY_SEPARATOR);
    $tpl->assign('LITO_IMG_PATH_URL', LITO_IMG_PATH_URL);
    $tpl->assign('LITO_MAIN_CSS', LITO_MAIN_CSS);
    $tpl->assign('GAME_FOOTER_MSG', get_footer());
    $tpl->assign('LITO_ROOT_PATH_URL', LITO_ROOT_PATH_URL);
    $tpl->assign('LITO_MODUL_PATH_URL', LITO_MODUL_PATH_URL);
    $tpl->assign('LITO_BASE_MODUL_URL', LITO_MODUL_PATH_URL);

    $tpl->display(LITO_THEMES_PATH . $from_modulname . DIRECTORY_SEPARATOR . $template_name);
}

function trace_msg($message, $error_type)
{
    global $db, $n, $userdata;

    $message = addslashes($message);
    $db->query("INSERT INTO cc" . $n . "_debug(db_time, db_text ,db_type ,fromuserid) VALUES ('" . time() . "','$message','" .
        $error_type . "','" . $userdata['userid'] . "')");

    return;
}

function get_race($rassenid)
{
    global $db, $n;
    static $cache = array();
    if (!isset($cache[$rassenid]))
    {
        $result = $db->query("SELECT rassenname FROM cc" . $n . "_rassen WHERE rassenid='$rassenid'");
        $cache[$rassenid] = $db->fetch_array($result);
    }
    return $cache[$rassenid]['rassenname'];
}


function send_register_mail($mail_send_to, $filename, $modulname, $u_name, $password, $x_pos, $y_pos)
{
    global $op_admin_email, $op_set_author, $op_set_gamename, $tpl, $op_send_html_mail;


    require_once (LITO_ROOT_PATH . "includes/mime_mail.class.php");


    $mime = new MIME_Mail;
    $html = "";
    $from_e = $op_admin_email;
    $from_n = $op_set_author;

    $to_e = $mail_send_to;
    $to_n = $mail_send_to;

    $text = "Registrierung bei " . $op_set_gamename;

    $filename_txt = str_replace('.html', '.txt', $filename);

    $html = $tpl->fetch(LITO_THEMES_PATH . $modulname . '/' . $filename);
    $txt = $tpl->fetch(LITO_THEMES_PATH . $modulname . '/' . $filename_txt);


    $search = array(
        '[REG_USERNAME]',
        '[REG_PASSWORD]',
        '[REG_X_POS]',
        '[REG_Y_POS]',
        '[REG_GAME_NAME]',
        );
    $replace = array(
        $u_name,
        $password,
        $x_pos,
        $y_pos,
        $op_set_gamename,
        );

    $html = str_replace($search, $replace, $html);
    $txt = str_replace($search, $replace, $txt);


    $key = "Mailer";
    $val = "Litotex mailer";

    //$mime->addXHeader( $key, $val );
    $mime->addXHeader("", "");
    $mime->addTo($to_e, $to_n);
    $mime->setFrom($from_e, $from_n);
    $mime->setSubject($text);
    $mime->setPriority(3);
    if (intval($op_send_html_mail) == 1)
    {
        $mime->setHTMLPart($html);

        $mime->sendMail();
    }
    else
    {
        $mime->setPlainPart($txt);

        mail($to_e, $text, $txt, "From: $from_e");
    }
}

function get_soldiers_name($tabless, $rasse)
{
    global $db, $n;

    $result = $db->query("SELECT name FROM cc" . $n . "_soldiers WHERE tabless='" . $tabless . "' and race='" . $rasse . "'");
    $row = $db->fetch_array($result);

    return (empty($row['name']) ? "unbekannt" : $row['name']);
}

function get_buildings_name($tabless, $rasse)
{
    global $db, $n;
    $result = $db->query("SELECT name FROM cc" . $n . "_buildings WHERE tabless='" . $tabless . "' and race='" . $rasse .
        "'");
    $row = $db->fetch_array($result);
    return (empty($row['name']) ? "unbekannt" : $row['name']);
}

function get_race_id_from_user($fuserid)
{
    global $db, $n;

    $result = $db->query("SELECT rassenid FROM cc" . $n . "_users WHERE userid='" . $fuserid . "'");
    $row = $db->fetch_array($result);

    return $row['rassenid'];
}

function get_soldiers_speed($tabless, $rasse)
{
    global $db, $n;

    $result = $db->query("SELECT traveltime FROM cc" . $n . "_soldiers WHERE tabless='" . $tabless . "' and race='" . $rasse .
        "'");
    $row = $db->fetch_array($result);

    return (empty($row['traveltime']) ? 11 : $row['traveltime']);

}
function get_distance_simple($startX, $startY, $endX, $endY)
{
    $x = $endX - $startX;
    $y = $endY - $startY;

    return sqrt(pow($x, 2) + pow($y, 2));
}


function get_duration_time($startX, $startY, $endX, $endY, $Sol_speed)
{
    global $op_land_duration;
    $duration = ceil(get_distance_simple($startX, $startY, $endX, $endY));
    return time() + round($duration * $op_land_duration * $Sol_speed / 100);
}

/** get a islandid **/
function get_island($islandid)
{
    global $db, $n;

    $result = $db->query("SELECT islandid,name FROM cc" . $n . "_countries WHERE islandid='$islandid'");
    $row = $db->fetch_array($result);

    return c_trim($row['name']);
}

function timebanner_init($banner_with, $image)
{
    global $tpl;

    $tpl->assign('LITO_ROOT_PATH_URL', LITO_ROOT_PATH_URL);
    $tpl->assign('TIME_BANNER_INIT', $tpl->fetch(LITO_THEMES_PATH . 'core/time_banner_init.html'));
}

function make_timebanner($timestart, $timeend, $banner_id, $reload_url, $css = "progressBar", $w = 100)
{
    // make a new Timebanner
    global $tpl;

    $tpl->assign('banner_w', $w);
    $tpl->assign('banner_css', $css);
    $tpl->assign('banner_id', $banner_id);
    $tpl->assign('startzeit', $timestart);
    $tpl->assign('endzeit', $timeend);
    $tpl->assign('NAVIGATE2SITE', $reload_url);

    $tpl->assign('banner_curtime', time());
    return $tpl->fetch(LITO_THEMES_PATH . 'core/time_banner.html');
}

function make_ingamemail($fromuser, $touser, $subject, $mailtext)
{
    global $db, $n, $userdata;

    $db->query("INSERT INTO cc" . $n .
        "_messages (username,fromuserid,touserid,text,time,isnew,inbox,subject,pri) VALUES ('" . $userdata['username'] . "','" .
        $fromuser . "','" . $touser . "','" . $mailtext . "','" . time() . "','1','1','" . $subject . "','0')");
}

function username($userid)
{
    global $db, $n;

    $result = $db->query("SELECT username FROM cc" . $n . "_users WHERE userid='$userid'");
    $row = $db->fetch_array($result);

    return c_trim($row['username']);
}

function sec2time($sek)
{
    if ($sek < 0)
    {
        $sek = 0;
    }
    return gmdate("H:i:s", $sek);
}

function bb2html($text)
{
    $find = array(
        "@\n@",
        "@[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]@is",
        "/\[url\=(.+?)\](.+?)\[\/url\]/is",
        "/\[b\](.+?)\[\/b\]/is",
        "/\[i\](.+?)\[\/i\]/is",
        "/\[u\](.+?)\[\/u\]/is",
        "/\[color\=(.+?)\](.+?)\[\/color\]/is",
        "/\[size\=(.+?)\](.+?)\[\/size\]/is",
        "/\[center\](.+?)\[\/center\]/is",
        "/\[right\](.+?)\[\/right\]/is",
        "/\[left\](.+?)\[\/left\]/is",
        "/\[img\](.+?)\[\/img\]/is",
        "/\[email\](.+?)\[\/email\]/is",
        "/\[quote\](.+?)\[\/quote\]/is",
        "/\[code\](.+?)\[\/code\]/is",
        "/\[list\](.+?)\[\/list\]/is",
        "/\[item\](.+?)\[\/item\]/is",
        );
    $replace = array(
        "<br />",
        "<a href=\"\\0\">\\0</a>",
        "<a href=\"$1\" target=\"_blank\">$2</a>",
        "<strong>$1</strong>",
        "<em>$1</em>",
        "<span style=\"text-decoration:underline;\">$1</span>",
        "<font color=\"$1\">$2</font>",
        "<font size=\"$1\">$2</font>",
        "<div style=\"text-align:center;\">$1</div>",
        "<div style=\"text-align:right;\">$1</div>",
        "<div style=\"text-align:left;\">$1</div>",
        "<img src=\"$1\" alt=\"Image\" />",
        "<a href=\"mailto:$1\" target=\"_blank\">$1</a>",
        "<table width=100% bgcolor=lightgray><tr><td bgcolor=white>$1</td></tr></table>",
        "<code>$1</code>",
        "<ul>$1</ul>",
        "<li>$1</li>",
        );
    return preg_replace($find, $replace, htmlspecialchars($text));
}

function html2bb($text)
{
    return bb2html($text);
}

function get_userid($username)
{
    global $db, $n;

    $result = $db->query("SELECT userid FROM cc" . $n . "_users WHERE username='$username'");
    $row = $db->fetch_array($result);

    return $row['userid'];
}

/** get username **/
function is_username($username)
{
    global $db, $n;
    $result = $db->query("SELECT username FROM cc" . $n . "_users WHERE username='$username'");
    $row = $db->fetch_array($result);

    return ($row['username'] != $username ? 0 : 1);
}

/** get name of group**/
function group($gid)
{
    global $db, $n;

    $result = $db->query("SELECT name FROM cc" . $n . "_groups WHERE groupid='$gid'");
    $row = $db->fetch_array($result);

    return $row['name'];
}

function get_userid_from_countrie($countrie_id)
{
    global $db, $n;

    $result = $db->query("SELECT userid FROM cc" . $n . "_countries WHERE islandid='$countrie_id'");
    $row = $db->fetch_array($result);

    return $row['userid'];
}

function get_race_id_from_countrie($countrie_id)
{
    global $db, $n;

    $result = $db->query("SELECT race FROM cc" . $n . "_countries WHERE islandid='" . $countrie_id . "'");
    $row = $db->fetch_array($result);

    return $row['race'];
}

function get_race_id_from_group($group_id)
{
    global $db, $n;
    $result = $db->query("SELECT islandid FROM cc" . $n . "_groups WHERE groupid='" . $group_id . "'");
    $row = $db->fetch_array($result);

    return get_race_id_from_countrie($row['islandid']);

}

function get_countrie_name_from_group_id($group_id, $inclu_koords = 0)
{
    global $db, $n;
    $result = $db->query("SELECT islandid FROM cc" . $n . "_groups WHERE groupid='" . $group_id . "'");
    $row = $db->fetch_array($result);

    return get_countrie_name_from_id($row['islandid'], $inclu_koords);
}


function get_countrie_name_from_id($country_id, $koords = 0)
{
    global $db, $n;

    $result = $db->query("SELECT name,islandid,x,y FROM cc" . $n . "_countries WHERE islandid='" . $country_id . "'");
    $row = $db->fetch_array($result);

    return ($koords == 1 ? $row['name'] . " (" . $row['x'] . ":" . $row['y'] . ")" : $row['name']);

}

function get_user_id_from_group_id($group_id)
{
    global $db, $n;

    $result = $db->query("SELECT islandid,groupid FROM cc" . $n . "_groups WHERE groupid='" . $group_id . "'");
    $row = $db->fetch_array($result);

    return get_userid_from_countrie($row['islandid']);
}

function get_allianz_flag($ali_id)
{
    $filename_flag = "./../../alli_flag/flag_" . $ali_id . ".png";
    if (!file_exists($filename_flag))
    {
        $filename_flag = LITO_IMG_PATH_URL . "alliance/no.png";
    }
    return ("<img src=\"$filename_flag\" border=\"1\">");
}

function generate_allilink($ali_id)
{
    if ($ali_id <= 0)
    {
        return "";
    }

    $module = get_modulname(4);
    $modul_org = "./../" . $module[0] . "/" . $module[1];
    $a_name = allianz($ali_id);

    return "<a href=\"$modul_org?action=get_info&id=$ali_id\">$a_name</a>";
}

function generate_userlink($user_id, $username)
{
    return "<a href=\"../members/members.php?action=profile&id=$user_id\">$username</a>";
}

function generate_messagelink($username, $show_icon = 0)
{
    $module = get_modulname(6);
    $msg_modul_org = "./../" . $module[0] . "/" . $module[1];
    $icon_url = "";
    if ($show_icon == 1)
    {
        $icon_url = "<div id=\"msglinkimg\"><img src=\"" . LITO_IMG_PATH_URL . $module[0] . "/newpost.png\" alt=\"Nachricht senden\" title=\"Nachricht senden\" border=\"0\"></div>";
    }

    return "<div id=\"msglink\"><a href=\"$msg_modul_org?action=send&username=$username\">" . $icon_url .
        " Nachricht senden</a></div>";
}
function generate_messagelink_smal($username)
{
    $module = get_modulname(6);
    $msg_modul_org = "./../" . $module[0] . "/" . $module[1];
    $icon_url = "<img src=\"" . LITO_IMG_PATH_URL . $module[0] . "/newpost.png\" alt=\"Nachricht senden\" title=\"Nachricht senden\" border=\"0\">";

    return "<div id=\"msglink\"><a href=\"$msg_modul_org?action=send&username=$username\">" . $icon_url . "</a></div>";
}

function allianz($aid)
{
    global $db, $n;

    $result = $db->query("SELECT name,aid FROM cc" . $n . "_allianz WHERE aid='$aid'");
    $row = $db->fetch_array($result);

    return ($row['aid'] != $aid ? 0 : c_trim($row['name']));
}

function get_allianz_points($aid)
{
    global $db, $n;

    $result = $db->query("SELECT points,aid FROM cc" . $n . "_allianz WHERE aid='$aid'");
    $row = $db->fetch_array($result);

    return ($row['aid'] != $aid ? 0 : $row['points']);
}


function get_new_msg_count()
{
    global $db, $n, $userdata;

    $result = $db->query("SELECT count(isnew) as anz  FROM cc" . $n . "_messages WHERE touserid='" . $userdata['userid'] .
        "' and isnew='1'");
    $row = $db->fetch_array($result);

    return $row['anz'];

}
function is_build_id_present($bid)
{
    global $db, $n, $userdata;

    $result = $db->query("SELECT name FROM cc" . $n . "_buildings WHERE bid  ='" . $bid . "'");
    $row = $db->fetch_array($result);

    return ($row['name'] == "" ? 0 : 1);
}

function calcRes($resName, $row, $numOfRessources)
{
    global $op_store_mulit, $op_set_store_max;
    global $op_set_res1, $op_set_res2, $op_set_res3, $op_set_res4, $op_mup_res1, $op_mup_res2, $op_mup_res3, $op_mup_res4;

    $op_res = ${'op_set_' . $resName};
    $op_mup_res = ${'op_mup_' . $resName};

    $store_max = $op_set_store_max * (($row['store'] + 1) * $op_store_mulit);
    $SetRes = $row[$resName] + (($op_res + ($row[$resName . 'mine'] * $op_mup_res)) * $numOfRessources);

    if ($SetRes > $store_max)
    {
        $SetRes = $store_max;
    }
    return round($SetRes, 0);
}

function resreload($countryid)
{
    global $db, $n, $userdata, $op_store_mulit, $op_set_store_max, $op_res_reload_time;
    global $op_set_res1, $op_set_res2, $op_set_res3, $op_set_res4, $op_mup_res1, $op_mup_res2, $op_mup_res3, $op_mup_res4, $op_res_reload_type;

    if (intval($op_res_reload_type) == 1 || $countryid <= 0)
    {
        return;
    }

    $result = $db->query("SELECT * FROM cc" . $n . "_countries WHERE islandid  ='" . $countryid . "'");
    $row = $db->fetch_array($result);

    $time4ressources = time() - $row['lastressources'];
    $numOfRessources = abs($time4ressources / $op_res_reload_time);
    $numOfRessources1 = floor($time4ressources / $op_res_reload_time);

    if ($numOfRessources1 >= 1)
    {
        $store_max = $op_set_store_max * (($row['store'] + 1) * $op_store_mulit);
        $SetRes1 = calcRes('res1', $row, $numOfRessources);
        $SetRes2 = calcRes('res2', $row, $numOfRessources);
        $SetRes3 = calcRes('res3', $row, $numOfRessources);
        $SetRes4 = calcRes('res4', $row, $numOfRessources);

        $new_last_res_time = ($row['lastressources'] + ($numOfRessources1 * $op_res_reload_time));
        //$div =time()-$new_last_res_time;
        //trace_msg ("last_res_time : $new_last_res_time currenttime: ".time()." anzhal:$numOfRessources anzahl_1:$numOfRessources1   last:".$row['lastressources']." new_div:$div --$time4ressources  " ,77);
        trace_msg("Function resreload ($countryid) res1:$SetRes1 res2:$SetRes2 res3:$SetRes3 res4:$SetRes4 storemax:$store_max",
            77);
        $db->query("UPDATE cc" . $n . "_countries SET res1='$SetRes1', res2='$SetRes2', res3='$SetRes3', res4='$SetRes4', lastressources='" .
            $new_last_res_time . "' WHERE islandid='" . $countryid . "'");

        if ($countryid == $userdata['islandid'])
        {
            $userdata['res1'] = $SetRes1;
            $userdata['res2'] = $SetRes2;
            $userdata['res3'] = $SetRes3;
            $userdata['res4'] = $SetRes4;
            $userdata['lastressources'] = $new_last_res_time;
        }
    }
    return;
}

function get_banner_code()
{
    global $db, $n, $userdata;

    if (is_modul_name_aktive("acp_bannermgr") == 0)
    {
        return;
    }

    $result = $db->query("SELECT * FROM cc" . $n . "_banner_mgr where active = 1 ORDER BY RAND() limit 1"); // WHERE  userid  ='$user_id'");
    $row = $db->fetch_array($result);
    if ((int)($row['banner_id']) > 0)
    {
        $result = $db->query("update cc" . $n . "_banner_mgr set banner_count=banner_count+1 where banner_id='" . $row['banner_id'] .
            "'");
    }
    return $row['banner_code'];
}

function make_tooltip_text($text)
{
    $text = str_replace('"', "\'", $text);
    return "onmouseover=\"Tip('$text',PADDING, 1,BGCOLOR, '#D3E3F6',TEXTALIGN,'left',SHADOW, true)\" onmouseout=\"UnTip()\"";
}


/*
1= Allianztext �ndern
2= Allianzr�nge �ndern
3= Allianzr�nge vergeben
4= Allianznews �ndern
5= Allianzfahne �ndern
6= Rundmail schreiben
7= Forumeinstellungen �ndern
*/

function get_alianz_members($allianzid)
{
    global $db, $n, $userdata;
    $sql_s = "SELECT count(userid) as anzahl FROM cc" . $n . "_users WHERE allianzid='$allianzid'";
    $result = $db->query($sql_s);
    $row_ali = $db->fetch_array($result);
    $maxallianzmembers = $row_ali['anzahl'];
    return $maxallianzmembers;
}

function flag_save($ali_id)
{
    global $db, $n, $userdata;
    $allifahne = $db->query("SELECT fahne FROM cc" . $n . "_allianz WHERE aid='$ali_id'");
    $fahnerow = $db->fetch_array($allifahne);
    $fahcol = $fahnerow['fahne'];


    $filename_flag = LITO_ROOT_PATH . "alli_flag/flag_" . $ali_id . ".png";


    $image = imagecreatetruecolor(20, 15);
    $image2 = imagecreatetruecolor(15, 10);
    $f[1] = imagecolorallocate($image, 255, 255, 255);
    $f[2] = imagecolorallocate($image, 50, 50, 50);
    $f[3] = imagecolorallocate($image, 255, 0, 0);
    $f[4] = imagecolorallocate($image, 0, 255, 0);
    $f[5] = imagecolorallocate($image, 0, 0, 255);
    $f[6] = imagecolorallocate($image, 255, 255, 0);
    $f[7] = imagecolorallocate($image, 255, 170, 0);
    $f[8] = imagecolorallocate($image, 0, 255, 255);
    $f[9] = imagecolorallocate($image, 150, 150, 150);
    imagefill($image, 1, 1, $f[1]);

    if ($fahcol)
    {
        imagefilledrectangle($image, 0, 0, 6.7, 5, $f[$fahcol[0]]);
        imagefilledrectangle($image, 6.6, 0, 13.4, 5, $f[$fahcol[1]]);
        imagefilledrectangle($image, 13.4, 0, 20, 5, $f[$fahcol[2]]);
        imagefilledrectangle($image, 0, 5, 6.7, 10, $f[$fahcol[3]]);
        imagefilledrectangle($image, 6.6, 5, 13.4, 10, $f[$fahcol[4]]);
        imagefilledrectangle($image, 13.4, 5, 20, 10, $f[$fahcol[5]]);
        imagefilledrectangle($image, 0, 10, 6.7, 15, $f[$fahcol[6]]);
        imagefilledrectangle($image, 6.6, 10, 13.4, 15, $f[$fahcol[7]]);
        imagefilledrectangle($image, 13.4, 10, 20, 15, $f[$fahcol[8]]);
    }
    imagecopyresampled($image2, $image, 0, 0, 0, 0, 15, 10, 20, 15);
    imagecopyresampled($image, $image2, 0, 0, 0, 0, 20, 15, 15, 10);

    if (file_exists($filename_flag))
    {
        unlink($filename_flag);
    }
    imagepng($image2, $filename_flag);

    imagedestroy($image);
    imagedestroy($image2);


}

function get_msg_count($boradid)
{
    global $db, $n, $userdata;
    $alle_msg = 0;
    $result = $db->query("SELECT count(messageid) as uuuu FROM cc" . $n . "_amessage WHERE allianzid='$userdata[allianzid]' AND boardid='$boradid' ");
    while ($row = $db->fetch_array($result))
    {
        $alle_msg = $row['uuuu'];
    }
    return $alle_msg;
}

function get_rang_from_user($usersid)
{
    global $db, $n, $userdata;
    $ali_id = $userdata['allianzid'];
    $users_rang_is = -1;
    $result_s = $db->query("SELECT rang_id  FROM cc" . $n . "_allianz_rang_user WHERE  user_id ='$usersid' and allianz_id  ='$ali_id'");
    while ($row_s = $db->fetch_array($result_s))
    {
        $users_rang_is = $row_s['rang_id'];
    }
    return $users_rang_is;
}
function get_rang_id_from_user($usersid, $allianzid)
{
    global $db, $n, $userdata;
    $users_rang_is = "";
    $result_s = $db->query("SELECT rang_id  FROM cc" . $n . "_allianz_rang_user WHERE  user_id ='$usersid' and allianz_id  ='$allianzid'");
    while ($row_s = $db->fetch_array($result_s))
    {
        $users_rang_is = $row_s['rang_id'];

    }
    return $users_rang_is;
}

function get_rang_name_from_allianz_rang($allianz_rang_id, $alli_id)
{
    global $db, $n, $userdata;
    $name = "";
    $result_s = $db->query("SELECT rangname FROM cc" . $n . "_allianz_rang  WHERE  allianz_rang_id ='$allianz_rang_id' and allianz_id ='$alli_id'");
    while ($row_s = $db->fetch_array($result_s))
    {
        $name = c_trim(($row_s['rangname']));
    }
    return $name;
}

function is_allowed($right_name)
{
    global $db, $n, $userdata;
    $ret = 0;
    $ali_id = $userdata['allianzid'];
    $userid_id = $userdata['userid'];

    if ($userdata['is_ali_admin'] == 1)
    {
        return "1";
        exit();
    }
    else
    {
        $this_rang = get_rang_from_user($userid_id);
        $result_rights = $db->query("SELECT * FROM cc" . $n . "_allianz_rechte WHERE allianz_id='$ali_id' and rang_id ='$this_rang' ");
        while ($row_right = $db->fetch_array($result_rights))
        {
            if ($row_right[$right_name] == 1)
            {
                return "1";
                exit();
            }
        }
    }
    return $ret;
}


function auto_generate_thumbs_ali($pic_name)
{
    global $db, $n, $userdata;

    $mode = 1;


    $uid = $userdata['userid'];
    $time = time();
    $filename = $pic_name;

    $filename_small = LITO_ROOT_PATH . "alli_flag/alli_" . $uid . "_" . $time . "_image.jpg";
    $filename_small_save = LITO_ROOT_PATH_URL . "alli_flag/alli_" . $uid . "_" . $time . "_image.jpg";
    $size = getimagesize($filename);
    $breite = $size[0];
    $hoehe = $size[1];
    $pic_type = $size[2];

    if ($pic_type != 2)
    {
        $filename = LITO_IMG_PATH_URL . "upload_pic/error.jpg";
        $size = getimagesize($filename);
        $breite = $size[0];
        $hoehe = $size[1];
        $pic_type = $size[2];

    }

    $thumb_w = $breite;
    $thumb_h = $hoehe;


    $bgimg = imagecreatefromjpeg($filename);


    // funtion zum scalieren
    $orig_w = ImageSX($bgimg);
    $orig_h = ImageSY($bgimg);
    if ($mode == 1)
    {
        $wmax = 468;
        $hmax = 60;
    }
    elseif ($mode == 2)
    {
        $wmax = 468;
        $hmax = 60;
    }


    if ($wmax || $hmax)
    {
        if ($orig_w > $wmax || $orig_h > $hmax)
        {
            $thumb_w = $wmax;
            $thumb_h = $hmax;
            if ($thumb_w / $orig_w * $orig_h > $thumb_h) $thumb_w = round($thumb_h * $orig_w / $orig_h);
            else  $thumb_h = round($thumb_w * $orig_h / $orig_w);
        }
        else
        {
            $thumb_w = $orig_w;
            $thumb_h = $orig_h;
        }
    }
    else
    {
        $thumb_w = $orig_w;
        $thumb_h = $orig_h;
    }

    $image = imagecreatetruecolor(468, 60);

    $blk = imagecolorallocate($image, 0, 0, 0);
    $wht = imagecolorallocate($image, 255, 255, 255);
    $red = imagecolorallocate($image, 255, 0, 0);
    $blue = imagecolorallocate($image, 0, 0, 255);
    $bgcol = "000000";


    imagefilledrectangle($image, 0, 0, $wmax - 1, $hmax - 1, intval($bgcol, 16));

    if ($thumb_w != $orig_w)
    {
        $rt = (468 / 2) - ($thumb_w / 2);

        imagecopyresampled($image, $bgimg, $rt, 0, 0, 0, $thumb_w, $thumb_h, $orig_w, $orig_h);
    }
    else
    {

        imagecopyresampled($image, $bgimg, 0, 0, 0, 0, $thumb_w, $thumb_h, $orig_w, $orig_h);
    }
    $ali_id = $userdata['allianzid'];
    $db->unbuffered_query("update cc" . $n . "_allianz   set  image_path ='$filename_small', imageurl='$filename_small_save' where  aid  = '$ali_id'");
    imagejpeg($image, $filename_small, 100);
    imagedestroy($image);
    imagedestroy($bgimg);

}

function auto_generate_thumbs($pic_name)
{
    global $db, $n, $userdata;

    $mode = 1;

    $uid = $userdata['userid'];
    $time = time();
    $filename = $pic_name;
    $filename_small = LITO_ROOT_PATH . "image_user/" . $uid . "_" . $time . "_image.jpg";
    $filename_small_URL = LITO_ROOT_PATH_URL . "image_user/" . $uid . "_" . $time . "_image.jpg";
    $size = getimagesize($filename);
    $breite = $size[0];
    $hoehe = $size[1];
    $pic_type = $size[2];

    if ($pic_type != 2)
    {

        $filename = LITO_IMG_PATH_URL . "upload_pic/error.jpg";
        $size = getimagesize($filename);
        $breite = $size[0];
        $hoehe = $size[1];
        $pic_type = $size[2];

    }

    $thumb_w = $breite;
    $thumb_h = $hoehe;
    $bgimg = imagecreatefromjpeg($filename);
    $orig_w = ImageSX($bgimg);
    $orig_h = ImageSY($bgimg);
    if ($mode == 1)
    {
        $wmax = 100;
        $hmax = 100;
    }
    elseif ($mode == 2)
    {
        $wmax = 200;
        $hmax = 200;
    }


    if ($wmax || $hmax)
    {
        if ($orig_w > $wmax || $orig_h > $hmax)
        {
            $thumb_w = $wmax;
            $thumb_h = $hmax;
            if ($thumb_w / $orig_w * $orig_h > $thumb_h) $thumb_w = round($thumb_h * $orig_w / $orig_h);
            else  $thumb_h = round($thumb_w * $orig_h / $orig_w);
        }
        else
        {
            $thumb_w = $orig_w;
            $thumb_h = $orig_h;
        }
    }
    else
    {
        $thumb_w = $orig_w;
        $thumb_h = $orig_h;
    }

    $image = imagecreatetruecolor($thumb_w, $thumb_h);

    $blk = imagecolorallocate($image, 0, 0, 0);
    $wht = imagecolorallocate($image, 255, 255, 255);
    $red = imagecolorallocate($image, 255, 0, 0);
    $blue = imagecolorallocate($image, 0, 0, 255);
    $bgcol = "FF0000";

    imagefilledrectangle($image, 0, 0, $wmax - 1, $hmax - 1, intval($bgcol, 16));
    imagecopyresampled($image, $bgimg, 0, 0, 0, 0, $thumb_w, $thumb_h, $orig_w, $orig_h);


    $db->unbuffered_query("update cc" . $n . "_users set userpic='$filename_small_URL' where  userid = '$uid'");
    imagejpeg($image, $filename_small, 100);
    imagedestroy($image);
    imagedestroy($bgimg);

}


function get_name_from_explore($explore_name, $race_id)
{
    global $db, $n;
    $result = $db->query("SELECT name FROM cc" . $n . "_explore WHERE tabless='$explore_name' and race='$race_id'");
    $row = $db->fetch_array($result);
    return $row['name'];
}
