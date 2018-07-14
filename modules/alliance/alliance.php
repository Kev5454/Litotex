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
$modul_name = 'alliance';
require ($_SESSION['litotex_start_g'] . 'includes/global.php');

if (is_modul_name_aktive($modul_name) == 0)
{
    show_error('MODUL_LOAD_ERROR', 'core');
}

$lang_file = LITO_LANG_PATH . $modul_name . '/lang_' . $lang_suffix . '.php';
$tpl->config_load($lang_file);
$ln_allianz_c_r_2 = $tpl->get_config_vars('ln_allianz_c_r_2');
$ln_allianz_c_r_13 = $tpl->get_config_vars('ln_allianz_c_r_13');
$ln_allianz_c_r_14 = $tpl->get_config_vars('ln_allianz_c_r_14');
$ln_allianz_c_r_5 = $tpl->get_config_vars('ln_allianz_c_r_5');
$ln_allianz_c_r_6 = $tpl->get_config_vars('ln_allianz_c_r_6');
$ln_allianz_c_r_8 = $tpl->get_config_vars('ln_allianz_c_r_8');
$ln_allianz_c_r_15 = $tpl->get_config_vars('ln_allianz_c_r_15');
$ln_allianz_c_r_16 = $tpl->get_config_vars('ln_allianz_c_r_16');
$ln_allianz_index_2 = $tpl->get_config_vars('ln_allianz_index_2');
$ln_allianz_index_3 = $tpl->get_config_vars('ln_allianz_index_3');
$ln_allianz_php_10 = $tpl->get_config_vars('ln_allianz_php_10');
$ln_allianz_php_11 = $tpl->get_config_vars('ln_allianz_php_11');
$ln_allianz_php_8 = $tpl->get_config_vars('ln_allianz_php_8');
$ln_allianz_php_9 = $tpl->get_config_vars('ln_allianz_php_9');


$ln_allianz_php_5 = $tpl->get_config_vars('ln_allianz_php_5');
$ln_allianz_b_in_1 = $tpl->get_config_vars('ln_allianz_b_in_1');
$ln_login_e_6 = $tpl->get_config_vars('ln_login_e_6');
$ln_login_e_7 = $tpl->get_config_vars('ln_login_e_7');

$module = get_modulname(6);
$msg_modul_org = "./../" . $module[0] . "/" . $module[1];

$modul = get_modulname(18);
$ali_modul_org = "./../" . $modul[0] . "/" . $modul[1];


if ($action == "main")
{
    $show_menue = "";
    $user_id = $userdata['userid'];
    $ali_id = $userdata['allianzid'];


    if ($userdata['allianzid'] == 0)
    {
        template_out('alliance_join.html', $modul_name);
        exit();
    }
    else
    {
        $members = '';
        $result = $db->query("SELECT userid,username,allianzid,is_ali_admin,points FROM cc" . $n . "_users WHERE allianzid='$allianz[aid]'");
        while ($row = $db->fetch_array($result))
        {

            $name = "k.A";
            $rang_id = get_rang_id_from_user($row['userid'], $ali_id);
            $name = get_rang_name_from_allianz_rang($rang_id, $ali_id);

            $members .= "$row[username] <b>$row[points]</b>" . (($row['is_ali_admin'] == 1) ? " (Admin)" : " ($name)") . "\n<br>\n";
            $pm_array[] = $row['username'];
        }
        $pm_user = implode(",", $pm_array);


        $result_ssi = $db->query("SELECT aid, max_members FROM cc" . $n . "_allianz WHERE aid='" . $userdata['allianzid'] . "'");
        $row_ali = $db->fetch_array($result_ssi);
        $ali_flag = get_allianz_flag($userdata['allianzid']);
        $allianz['name'] = c_trim($allianz['name']);
        $banner_ali = trim($allianz['imageurl']);
        if ($banner_ali == "")
        {
            $banner_ali = LITO_IMG_PATH_URL . $modul_name . "/no_ali_banner.png";
        }
        $maxallianzmembers = $op_max_ali_members;


        $tpl->assign('banner_ali', $banner_ali);
        $tpl->assign('members', $members);
        $tpl->assign('ali_flag', $ali_flag);
        $tpl->assign('a_name', $allianz['name']);
        $tpl->assign('maxallianzmembers', $maxallianzmembers);
        $tpl->assign('a_members', $allianz['members']);


        if ($userdata['is_ali_admin'] == 1)
        {
            // bewerbungen anzeigen
            $count = 0;
            $bew_array = array();

            $result_bewerbungen = $db->query("SELECT * FROM cc" . $n . "_allianz_bewerbung WHERE allianz_id ='" . $userdata['allianzid'] .
                "' order by datum");
            while ($row_bewerb = $db->fetch_array($result_bewerbungen))
            {
                $be_id = $row_bewerb['bewerber_id'];
                $t_bewerbung_id = $row_bewerb['bewerbung_id'];
                $be_name = username($be_id);
                $be_datum = date("d.m.Y H:i:s", $row_bewerb['datum']);
                $b_text = $row_bewerb['bewerber_text'];
                $ok_a = "<a href=\"alliance.php?action=bewerben_accept&id=$t_bewerbung_id\"><img src=\"" . LITO_IMG_PATH_URL . $modul_name .
                    "/ok.png\" ALT=\"$ln_login_e_6\" border=\"0\" TITLE=\"$ln_login_e_6\"></a> ";
                $del_a = "<a href=\"alliance.php?action=bewerben_cancel&id=$t_bewerbung_id\"><img src=\"" . LITO_IMG_PATH_URL . $modul_name .
                    "/nok.png\" ALT=\"$ln_login_e_7\" border=\"0\" TITLE=\"$ln_login_e_7\"></a> ";

                $bew_array[$count]['be_id'] = $be_id;
                $bew_array[$count]['be_name'] = $be_name;
                $bew_array[$count]['be_datum'] = $be_datum;
                $bew_array[$count]['ok_a'] = $ok_a;
                $bew_array[$count]['del_a'] = $del_a;
                $bew_array[$count]['b_text'] = $b_text;
                $count++;


            }
            $tpl->assign('ali_data_bew', $bew_array);


            $show_menue = "";
            $show_menue = $show_menue . "<li><a href=\"alliance.php?action=change_ali_text\">$ln_allianz_c_r_2</li></a>";
            $show_menue = $show_menue . "<li><a href=\"alliance.php?action=change_rang\">$ln_allianz_c_r_13</li></a>";
            $show_menue = $show_menue . "<li><a href=\"alliance.php?action=change_rang_user\">$ln_allianz_c_r_14</a></li>";
            $show_menue = $show_menue . "<li><a href=\"alliance.php?action=change_news\">$ln_allianz_c_r_5</a></li>";
            $show_menue = $show_menue . "<li><a href=\"alliance.php?action=fahne\">$ln_allianz_c_r_6</a></li>";
            $show_menue = $show_menue . "<li><a href=\"alliance.php?action=change_forum\">$ln_allianz_c_r_8</a></li>";
            $show_menue = $show_menue . "<li><a href=\"" . $ali_modul_org . "\">$ln_allianz_c_r_15</a></li>";
            $show_menue = $show_menue . "<li><a href=\"" . $msg_modul_org . "?action=send&username=$pm_user\">$ln_allianz_c_r_16</li></a>";
            $show_menue = $show_menue . "<li><a href=\"alliance.php?action=kick\">$ln_allianz_index_2</a></li>";
            $show_menue = $show_menue . "<li><a href=\"#\"  onclick=\"delalli()\">$ln_allianz_index_3</a></li>";


            $tpl->assign('show_menue', $show_menue);
            template_out('ali_admin_menu.html', $modul_name);


            exit();
        }
        else
        {
            $urang = get_rang_from_user($user_id);
            $result_rang = $db->query("SELECT * FROM cc" . $n . "_allianz_rechte WHERE allianz_id='$ali_id' and rang_id ='$urang'");
            $show_menue = "";
            while ($row_rang = $db->fetch_array($result_rang))
            {

                if ($row_rang['change_text'] == 1)
                {
                    $show_menue = $show_menue . "<li><a href=\"alliance.php?action=change_ali_text\">$ln_allianz_c_r_2</a></li>";
                }
                if ($row_rang['change_rang'] == 1)
                {
                    $show_menue = $show_menue . "<li><a href=\"alliance.php?action=change_rang\">$ln_allianz_c_r_13</a></li>";
                }
                if ($row_rang['give_rang'] == 1)
                {
                    $show_menue = $show_menue . "<li><a href=\"alliance.php?action=change_rang_user\">$ln_allianz_c_r_14</a></li>";
                }
                if ($row_rang['change_news'] == 1)
                {
                    $show_menue = $show_menue . "<li><a href=\"alliance.php?action=change_news\">$ln_allianz_c_r_5</a></li>";
                }
                if ($row_rang['change_fahne'] == 1)
                {
                    $show_menue = $show_menue . "<li><a href=\"alliance.php?action=fahne\">$ln_allianz_c_r_6</a></li>";
                }
                if ($row_rang['write_rundmail'] == 1)
                {
                    $show_menue = $show_menue . "<li><a href=\"" . $msg_modul_org . "?action=send&username=$pm_user\">$ln_allianz_c_r_16</a></li>";
                }
                if ($row_rang['change_forum'] == 1)
                {
                    $show_menue = $show_menue . "<li><a href=\"alliance.php?action=change_forum\">$ln_allianz_c_r_8</a></li>";
                }
                if ($row_rang['use_bord'] == 1)
                {
                    $show_menue = $show_menue . "<li><a href=\"" . $ali_modul_org . "\">$ln_allianz_c_r_15</a></li>";
                }
                if ($row_rang['use_kasse'] == 1)
                {
                    $show_menue = $show_menue . "<li><a href=\"alliance.php?action=alli_bank\">Allianzkasse</a></li>";
                }

            }
            $show_menue = $show_menue . "<li><a href=\"alliance.php?action=leave\">$ln_allianz_index_4</a></li>";

            template_out('ali_admin_menu.html', $modul_name);
            exit();
        }
    }
}
if ($action == "change_rang_user")
{
    $erlaubt = is_allowed("give_rang");
    $ali_id = $userdata['allianzid'];

    if ($erlaubt == 0)
    {
        show_error('ln_allianz_php_2', $modul_name);
        exit();
    }

    $user_sel_id = (!isset($_GET['uid']) ? 0 : intval(c_trim($_GET['uid'])));
    $rang_sel_id = (!isset($_GET['id']) ? 0 : intval(c_trim($_GET['id'])));
    if ($user_sel_id > 0 && $rang_sel_id > 0)
    {
        $db->unbuffered_query("delete from cc" . $n . "_allianz_rang_user WHERE allianz_id ='$ali_id' and user_id ='$user_sel_id'");
        $db->query("INSERT INTO cc" . $n . "_allianz_rang_user(allianz_id  ,rang_id ,user_id  ) VALUES ('$ali_id','$rang_sel_id','$user_sel_id')");

        redirect($modul_name, 'alliance', 'change_rang_user');
    }
    if ($user_sel_id > 0 && $rang_sel_id == -1)
    {
        $db->unbuffered_query("delete from cc" . $n . "_allianz_rang_user WHERE allianz_id ='$ali_id' and user_id ='$user_sel_id'");
        redirect($modul_name, 'alliance', 'change_rang_user');
    }


    $result = $db->query("SELECT userid,username  FROM cc" . $n . "_users WHERE  allianzid='$ali_id'");
    $counter = 0;
    $array_count = 0;
    while ($row = $db->fetch_array($result))
    {
        $counter = $counter + 1;
        $uname = $row['username'];
        $uid = $row['userid'];
        $user_rank_id = get_rang_from_user($uid);

        $all_rang_names = "<select name='rang_names' class=\"button\" ONCHANGE='location.href=this.options[this.selectedIndex].value'>";
        $all_rang_names = $all_rang_names . "<option value='alliance.php?action=change_rang_user&id=-1&uid=$uid'>kein Rang</option>";
        $result_rang = $db->query("SELECT * FROM cc" . $n . "_allianz_rang WHERE allianz_id='$ali_id'");
        while ($row_rang = $db->fetch_array($result_rang))
        {
            $t_id = $row_rang['allianz_rang_id'];

            if ($t_id == $user_rank_id)
            {
                $all_rang_names = $all_rang_names . "<option value='alliance.php?action=change_rang_user&id=$row_rang[allianz_rang_id]&uid=$uid' selected>$row_rang[rangname]</option>";
            }
            else
            {
                $all_rang_names = $all_rang_names . "<option value='alliance.php?action=change_rang_user&id=$row_rang[allianz_rang_id]&uid=$uid'>$row_rang[rangname]</option>";
            }
        }

        $rank_array[$array_count]['counter'] = $counter;
        $rank_array[$array_count]['uname'] = $uname;
        $rank_array[$array_count]['all_rang_names'] = $all_rang_names;
        $array_count++;
    }


    $tpl->assign('ali_data_rank', $rank_array);
    template_out('ali_user_rang.html', $modul_name);
    exit();
}


if ($action == "change_rang")
{
    $erlaubt = is_allowed("change_rang");
    $ali_id = $userdata['allianzid'];

    if ($erlaubt == 0)
    {
        show_error('ln_allianz_php_2', $modul_name);
    }
    $tt_id = (!isset($_GET['id']) ? 0 : intval(c_trim($_GET['id'])));

    $all_rang_names = "<select name='rang_names' class=\"button\" ONCHANGE='location.href=this.options[this.selectedIndex].value'>";
    $all_rang_names = $all_rang_names . "<option value='alliance.php?action=change_rang'>" . $tpl->get_config_vars('ln_allianz_php_3') .
        "</option>";
    $result = $db->query("SELECT * FROM cc" . $n . "_allianz_rang WHERE allianz_id='$ali_id'");
    while ($row = $db->fetch_array($result))
    {
        $t_id = $row['allianz_rang_id'];

        //exit();
        if ($t_id == $tt_id)
        {
            $all_rang_names = $all_rang_names . "<option value='alliance.php?action=change_rang&id=$row[allianz_rang_id]' selected>$row[rangname]</option>";
        }
        else
        {
            $all_rang_names = $all_rang_names . "<option value='alliance.php?action=change_rang&id=$row[allianz_rang_id]'>$row[rangname]</option>";
        }
    }
    $all_rang_names = $all_rang_names . "</select>";

    $change_text_b = "";
    $change_rang_b = "";
    $give_rang_b = "";
    $change_news_b = "";
    $change_fahne_b = "";
    $write_rundmail_b = "";
    $change_forum_b = "";
    $use_bord_b = "";
    $use_kasse_b = "";
    if ($tt_id > 0)
    {
        $result_rang = $db->query("SELECT * FROM cc" . $n . "_allianz_rechte WHERE rang_id ='$tt_id' and allianz_id ='$ali_id'");
        $row_rang = $db->fetch_array($result_rang);
        if ($row_rang['change_text'] == 1) $change_text_b = "checked";
        if ($row_rang['change_rang'] == 1) $change_rang_b = "checked";
        if ($row_rang['give_rang'] == 1) $give_rang_b = "checked";
        if ($row_rang['change_news'] == 1) $change_news_b = "checked";
        if ($row_rang['change_fahne'] == 1) $change_fahne_b = "checked";
        if ($row_rang['write_rundmail'] == 1) $write_rundmail_b = "checked";
        if ($row_rang['change_forum'] == 1) $change_forum_b = "checked";
        if ($row_rang['use_bord'] == 1) $use_bord_b = "checked";
        if ($row_rang['use_kasse'] == 1) $use_kasse_b = "checked";


    }
    echo ($change_text_b . "<br>");


    $tpl->assign('tt_id', $tt_id);
    $tpl->assign('change_text_b', $change_text_b);
    $tpl->assign('change_rang_b', $change_rang_b);
    $tpl->assign('give_rang_b', $give_rang_b);
    $tpl->assign('change_news_b', $change_news_b);
    $tpl->assign('change_fahne_b', $change_fahne_b);
    $tpl->assign('write_rundmail_b', $write_rundmail_b);
    $tpl->assign('change_forum_b', $change_forum_b);
    $tpl->assign('use_bord_b', $use_bord_b);
    $tpl->assign('use_kasse_b', $use_kasse_b);


    $tpl->assign('all_rang_names', $all_rang_names);

    template_out('ali_create_rang.html', $modul_name);
}


if ($action == "update_rang")
{
    $ali_id = $userdata['allianzid'];
    $erlaubt = is_allowed("change_rang");
    if ($erlaubt == 0)
    {
        show_error('ln_allianz_php_2', $modul_name);
    }
    $gewaehlt = intval((c_trim($_POST['nur'])));
    $change_text_P = (c_trim($_POST['change_text']));
    $change_rang_P = (c_trim($_POST['change_rang']));
    $rang_P = (c_trim($_POST['rang']));
    $change_news_P = (c_trim($_POST['change_news']));
    $change_fahne_P = (c_trim($_POST['change_fahne']));
    $write_rundmail_P = (c_trim($_POST['write_rundmail']));
    $change_forum_P = (c_trim($_POST['change_forum']));
    $use_bord_P = (c_trim($_POST['use_bord']));
    $use_kasse_P = (c_trim($_POST['use_kasse']));


    if ($change_text_P != "") $change_text_P = 1;
    else  $change_text_P = 0;
    if ($change_rang_P != "") $change_rang_P = 1;
    else  $change_rang_P = 0;
    if ($rang_P != "") $rang_P = 1;
    else  $rang_P = 0;
    if ($change_news_P != "") $change_news_P = 1;
    else  $change_news_P = 0;
    if ($change_fahne_P != "") $change_fahne_P = 1;
    else  $change_fahne_P = 0;
    if ($write_rundmail_P != "") $write_rundmail_P = 1;
    else  $write_rundmail_P = 0;
    if ($change_forum_P != "") $change_forum_P = 1;
    else  $change_forum_P = 0;
    if ($use_bord_P != "") $use_bord_P = 1;
    else  $use_bord_P = 0;
    if ($use_kasse_P != "") $use_kasse_P = 1;
    else  $use_kasse_P = 0;

    $db->unbuffered_query("delete from cc" . $n . "_allianz_rechte WHERE allianz_id ='$ali_id' and rang_id ='$gewaehlt'");
    $db->query("INSERT INTO cc" . $n .
        "_allianz_rechte (allianz_id ,rang_id ,change_text ,change_rang,give_rang ,change_news,change_fahne ,write_rundmail,change_forum, use_bord,use_kasse  ) VALUES ('$ali_id','$gewaehlt','$change_text_P','$change_rang_P','$rang_P','$change_news_P','$change_fahne_P','$write_rundmail_P','$change_forum_P','$use_bord_P','$use_kasse_P')");

    redirect($modul_name, 'alliance', 'change_rang', array('id' => $gewaehlt));
}


if ($action == "new_rang")
{
    $erlaubt = is_allowed("change_rang");
    $ali_id = $userdata['allianzid'];
    if ($erlaubt == 0)
    {
        show_error('ln_allianz_php_2', $modul_name);
    }
    $neuer_rang = c_trim($_POST['new_rang']);
    if ($neuer_rang == "")
    {
        show_error('ln_allianz_e_1', $modul_name);

    }
    $db->query("INSERT INTO cc" . $n . "_allianz_rang (allianz_id ,rangname ) VALUES ('$ali_id','$neuer_rang')");

    redirect($modul_name, 'alliance', 'change_rang');
    exit();
}

if ($action == "change_ali_text")
{
    $erlaubt = is_allowed("change_text");
    $ali_id = $userdata['allianzid'];
    if ($erlaubt == 0)
    {
        show_error('ln_allianz_php_2', $modul_name);
    }
    $result_e = $db->query("SELECT * FROM cc" . $n . "_allianz WHERE aid ='$ali_id' ");
    while ($row_e = $db->fetch_array($result_e))
    {
        $allianz_t = $row_e['text'];
        $description = $row_e['text_long'];
        $allianz_i_url = trim($row_e['imageurl']);
        if ($allianz_i_url == "")
        {
            $allianz_i_url = LITO_IMG_PATH_URL . $modul_name . "/no_ali_banner.png";
        }
    }

    $tpl->assign('allianz_i_url', $allianz_i_url);
    $tpl->assign('allianz_t', $allianz_t);
    $tpl->assign('description', $description);

    template_out('ali_text.html', $modul_name);
    exit();
}
if ($action == "change_ali_text_s")
{
    $erlaubt = is_allowed("change_text");
    $ali_id = $userdata['allianzid'];
    if ($erlaubt == 0)
    {
        show_error('ln_allianz_php_2', $modul_name);
    }
    $text = c_trim($_POST['text']);
    $text_l = html2bb($_POST['descr']);
    $db->query("UPDATE cc" . $n . "_allianz SET text='" . $text . "',text_long='" . $text_l . "'  WHERE aid='" . $ali_id .
        "'");

    $password = c_trim($_POST['password']);
    if ($password != "")
    {
        $db->query("UPDATE cc" . $n . "_allianz SET password='$password' WHERE aid='" . $userdata['allianzid'] . "'");
    }

    redirect($modul_name, 'alliance', 'main', array('cxid' => $cxid));
}
if ($action == "change_news_s")
{
    $erlaubt = is_allowed("change_news");
    $ali_id = $userdata['allianzid'];
    if ($erlaubt == 0)
    {
        show_error('ln_allianz_php_2', $modul_name);
    }
    $text_l = html2bb($_POST['descr']);
    $change_date = time();
    $db->query("delete from  cc" . $n . "_allianznews WHERE allianz_id ='$ali_id'");
    $db->query("INSERT INTO cc" . $n . "_allianznews (allianz_id , a_news_text,change_date ) VALUES ('$ali_id','$text_l','$change_date')");

    redirect($modul_name, 'alliance', 'main');
    exit();
}
if ($action == "change_news")
{
    $erlaubt = is_allowed("change_news");
    $ali_id = $userdata['allianzid'];
    if ($erlaubt == 0)
    {
        show_error('ln_allianz_php_2', $modul_name);
    }
    $result_e = $db->query("SELECT * FROM cc" . $n . "_allianznews WHERE allianz_id  ='$ali_id' ");
    $row_e = $db->fetch_array($result_e);

    $tpl->assign('description', $row_e['a_news_text']);
    $tpl->assign('change_date', date("d.m.Y (H:i:s)", $row_e['change_date']));

    template_out('ali_news.html', $modul_name);
    exit();
}
if ($action == "fahne")
{
    $erlaubt = is_allowed("change_fahne");
    $ali_id = $userdata['allianzid'];
    if ($erlaubt == 0)
    {
        show_error('ln_allianz_php_2', $modul_name);
    }
    $ali_flag_pic = get_allianz_flag($ali_id);
    $result_e = $db->query("SELECT fahne FROM cc" . $n . "_allianz WHERE aid ='$ali_id' ");
    $allianz = $db->fetch_array($result_e);

    if (isset($allianz['fahne'][0]))
    {
        $f1[$allianz['fahne'][0]] = "selected";
    }
    else
    {
        $f1[1] = "selected";
    }
    if (isset($allianz['fahne'][1]))
    {
        $f2[$allianz['fahne'][1]] = "selected";
    }
    else
    {
        $f2[1] = "selected";
    }
    if (isset($allianz['fahne'][2]))
    {
        $f3[$allianz['fahne'][2]] = "selected";
    }
    else
    {
        $f3[1] = "selected";
    }
    if (isset($allianz['fahne'][3]))
    {
        $f4[$allianz['fahne'][3]] = "selected";
    }
    else
    {
        $f4[1] = "selected";
    }
    if (isset($allianz['fahne'][4]))
    {
        $f5[$allianz['fahne'][4]] = "selected";
    }
    else
    {
        $f5[1] = "selected";
    }
    if (isset($allianz['fahne'][5]))
    {
        $f6[$allianz['fahne'][5]] = "selected";
    }
    else
    {
        $f6[1] = "selected";
    }
    if (isset($allianz['fahne'][6]))
    {
        $f7[$allianz['fahne'][6]] = "selected";
    }
    else
    {
        $f7[1] = "selected";
    }
    if (isset($allianz['fahne'][7]))
    {
        $f8[$allianz['fahne'][7]] = "selected";
    }
    else
    {
        $f8[1] = "selected";
    }
    if (isset($allianz['fahne'][8]))
    {
        $f9[$allianz['fahne'][8]] = "selected";
    }
    else
    {
        $f9[1] = "selected";
    }


    $tpl->assign('ali_flag_pic', $ali_flag_pic);
    $tpl->assign('f1', $f1);
    $tpl->assign('f2', $f2);
    $tpl->assign('f3', $f3);
    $tpl->assign('f4', $f4);
    $tpl->assign('f5', $f5);
    $tpl->assign('f6', $f6);
    $tpl->assign('f7', $f7);
    $tpl->assign('f8', $f8);
    $tpl->assign('f9', $f9);

    template_out('ali_flag.html', $modul_name);
    exit();
}
if ($action == "fahne_s")
{
    $erlaubt = is_allowed("change_fahne");
    $ali_id = $userdata['allianzid'];
    if ($erlaubt == 0)
    {
        show_error('ln_allianz_php_2', $modul_name);
    }

    $fahne = $_POST['1'] . $_POST['2'] . $_POST['3'] . $_POST['4'] . $_POST['5'] . $_POST['6'] . $_POST['7'] . $_POST['8'] .
        $_POST['9'];
    $db->query("UPDATE cc" . $n . "_allianz SET fahne='$fahne' WHERE aid='$ali_id'");
    flag_save($ali_id);

    redirect($modul_name, 'alliance', 'fahne');
}


if ($action == "change_forum")
{
    $erlaubt = is_allowed("change_forum");
    $ali_id = $userdata['allianzid'];
    $allianz_boards = "";
    if ($erlaubt == 0)
    {
        show_error('ln_allianz_php_2', $modul_name);
    }
    $ali_count = 0;
    $ali_forum = array();
    $result_e = $db->query("SELECT * FROM cc" . $n . "_forum   WHERE alli_id ='$ali_id' ");
    while ($row_e = $db->fetch_array($result_e))
    {
        $ali_forum[$ali_count]['katname'] = $row_e['si_forum_name'];
        $ali_forum[$ali_count]['description'] = $row_e['si_forum_desc'];
        $ali_forum[$ali_count]['aktion'] = "delete";
        $ali_forum[$ali_count]['forum_id'] = $row_e['si_forum_id'];
        $ali_count++;
    }

    $tpl->assign('allianz_boards', "");
    $tpl->assign('descript', "");
    $tpl->assign('ali_forum', $ali_forum);

    template_out('ali_board_a.html', $modul_name);
    exit();
}
if ($action == "change_forum_s")
{
    $erlaubt = is_allowed("change_forum");
    $ali_id = $userdata['allianzid'];
    if ($erlaubt == 0)
    {
        show_error('ln_allianz_php_2', $modul_name);
        exit();
    }

    $boards = c_trim($_POST['boards']);
    $description = c_trim($_POST['descript']);


    $db->query("Insert into cc" . $n . "_forum (si_forum_name,si_forum_desc,alli_id) VALUES ('$boards','$description','$ali_id')");

    redirect($modul_name, 'alliance', 'change_forum');
}

if ($action == "use_bord")
{

    $erlaubt = is_allowed("use_bord");
    $ali_id = $userdata['allianzid'];
    $ali_user_id = $userdata['userid'];
    if ($erlaubt == 0)
    {
        show_error('ln_allianz_php_2', $modul_name);
    }


    $db->unbuffered_query("UPDATE cc" . $n . "_users SET newallianzmessage='0' WHERE allianzid='$ali_id' and userid='$ali_user_id'");
    if (isset($_REQUEST['boardid'])) $boardid = intval($_REQUEST['boardid']);
    else  $boardid = "";

    if ($boardid)
    {
        $msg_count = 0;
        $result = $db->query("SELECT * FROM cc" . $n . "_amessage WHERE allianzid='$userdata[allianzid]' AND boardid='$boardid' ORDER BY time DESC");
        while ($row = $db->fetch_array($result))
        {
            $title = c_trim($row['title']);
            $text = c_trim($row['text']);
            $username = c_trim($row['username']);
            $dates = date("d.m.Y H:i:s", $row['time']);

            eval("\$allianz_message_bit .= \"" . $tpl->get("alliance_message_bit") . "\";");
        }
        eval("\$tpl->output(\"" . $tpl->get("ali_board_show") . "\");");
        exit();
    }
    else
    {
        $board = explode("\n", $allianz['boards']);
        for ($i = 0; $i < count($board); $i++)
        {
            $allianzboardid = $i + 1;
            $anzahl_msg = 0;
            $anzahl_msg = get_msg_count($allianzboardid);
            $boardname = c_trim($board[$i]);
            eval("\$allianz_board_bit .= \"" . $tpl->get("alliance_board_bit") . "\";");
        }

        eval("\$tpl->output(\"" . $tpl->get("ali_board") . "\");");
        exit();
    }

}


if ($action == "kick")
{
    if ($userdata['is_ali_admin'] == 0)
    {
        show_error('ln_allianz_e_12', $modul_name);
    }
    $kick_counter = 0;
    $result = $db->query("SELECT * FROM cc" . $n . "_users WHERE allianzid='" . $allianz['aid'] . "'");
    while ($row = $db->fetch_array($result))
    {
        $username = $row['username'];
        $punke = $row['points'];
        $id = $row['userid'];

        $kicker[$kick_counter]['name'] = $username;
        $kicker[$kick_counter]['points'] = $punke;
        $kicker[$kick_counter]['id'] = $id;

        $kick_counter++;

    }
    $tpl->assign('kicker', $kicker);
    template_out('alliance_kick.html', $modul_name);
    exit();
}

if ($action == "dokick")
{
    if ($userdata['is_ali_admin'] == 0)
    {
        show_error('ln_allianz_e_12', $modul_name);
    }

    $kuserid = intval($_GET['kuserid']);
    if ($kuserid == $userdata['userid'])
    {
        show_error('ln_allianz_php_4', $modul_name);
    }

    $db->query("UPDATE cc" . $n . "_users SET allianzid='0', is_ali_admin='0', newallianzmessage= '0' WHERE userid='$kuserid' AND allianzid='$allianz[aid]'");
    $db->query("UPDATE cc" . $n . "_allianz SET members=members-1 WHERE aid='" . $userdata['allianzid'] . "'");
    $db->query("DELETE from  cc" . $n . "_allianz_rang_user  WHERE user_id ='$kuserid'");

    redirect($modul_name, 'alliance', 'main');
    exit();
}


if ($action == "remove")
{
    if ($userdata['is_ali_admin'] == 0)
    {
        show_error('ln_allianz_e_12', $modul_name);
    }
    $db->query("DELETE FROM cc" . $n . "_allianz WHERE aid='" . $userdata['allianzid'] . "'");
    $db->query("UPDATE cc" . $n . "_users SET allianzid='0', is_ali_admin='0' , newallianzmessage= '0' WHERE allianzid='$allianz[aid]'");


    $db->query("DELETE FROM cc" . $n . "_allianz_rang WHERE allianz_id ='" . $userdata['allianzid'] . "'");
    $db->query("DELETE FROM cc" . $n . "_allianz_rang_user WHERE allianz_id='" . $userdata['allianzid'] . "'");
    $db->query("DELETE FROM cc" . $n . "_allianz_rechte WHERE allianz_id ='" . $userdata['allianzid'] . "'");


    $db->query("DELETE FROM cc" . $n . "_allianz_log WHERE ali_id ='" . $userdata['allianzid'] . "'");

    redirect($modul_name, 'alliance', 'main');
}

if ($action == "leave")
{
    if ($userdata['is_ali_admin'] == 1)
    {
        show_error('ln_allianz_e_11', $modul_name);
    }
    $db->query("UPDATE cc" . $n . "_allianz SET members=members-1 WHERE aid='$userdata[allianzid]'");
    $db->query("UPDATE cc" . $n . "_users SET allianzid='0' , newallianzmessage= '0' WHERE userid='$userdata[userid]'");
    $db->query("DELETE FROM cc" . $n . "_allianz_rang_user WHERE user_id  ='" . $userdata['userid'] . "'");


    redirect($modul_name, 'alliance', 'main');
}

if ($action == "join")
{
    $allianz = c_trim($_POST['allianz']);
    $password = c_trim($_POST['password']);

    if (!$allianz || !$password)
    {
        show_error('ln_allianz_e_3', $modul_name);
    }

    $result = $db->query("SELECT * FROM cc" . $n . "_allianz WHERE name='$allianz' and space ='0' ");
    $row = $db->fetch_array($result);

    if ($row['name'] != $allianz)
    {
        show_error('ln_allianz_e_4', $modul_name);
    }

    if ($row['password'] != $password)
    {
        show_error('ln_allianz_e_5', $modul_name);
    }


    /** set max members 25 (old value=25) **/
    $anzahl_members_max = $op_max_ali_members;
    $anzahl_curent = get_alianz_members($row['aid']);

    if ($anzahl_curent >= $anzahl_members_max)
    {
        show_error('ln_allianz_e_7', $modul_name);
    }

    $db->query("UPDATE cc" . $n . "_allianz SET members=members+'1' WHERE name='$allianz'");
    $db->query("UPDATE cc" . $n . "_users SET allianzid='$row[aid]' WHERE userid='" . $userdata['userid'] . "'");

    redirect($modul_name, 'alliance', 'main');
}


if ($action == "create")
{
    $allianz = c_trim($_POST['allianz']);
    $password = c_trim($_POST['password']);

    if (!$allianz || !$password)
    {
        show_error('ln_allianz_e_8', $modul_name);
    }

    if (strlen($allianz) < 3 || strlen($allianz) > 20)
    {
        show_error('ln_allianz_e_9', $modul_name);
    }

    $result = $db->query("SELECT * FROM cc" . $n . "_allianz WHERE name='$allianz'");
    $row = $db->fetch_array($result);

    if ($row['name'] == $allianz)
    {
        show_error('ln_allianz_e_10', $modul_name);
    }

    $db->query("INSERT INTO cc" . $n . "_allianz (name,members,password,rassenid,space) VALUES ('$allianz','1','$password','" .
        $userdata['rassenid'] . "','0')");
    $id = $db->insert_id();
    $db->query("UPDATE cc" . $n . "_users SET allianzid='$id', is_ali_admin='1' WHERE userid='" . $userdata['userid'] . "'");

    redirect($modul_name, 'alliance', 'main');
}


if ($action == "post")
{

    $erlaubt = is_allowed("use_bord");
    $ali_id = $userdata['allianzid'];
    if ($erlaubt == 0)
    {
        show_error('ln_allianz_php_2', $modul_name);
    }

    $title = c_trim($_POST['title']);
    $text = c_trim($_POST['text']);
    $boardid = intval($_REQUEST['boardid']);
    if (!$text || !$title)
    {
        error_page($ln_allianz_e_1);
    }
    $db->query("INSERT INTO cc" . $n . "_amessage (allianzid,text,title,username,time,boardid,fromuserid) VALUES ('$userdata[allianzid]','$text','$title','$userdata[username]','" .
        time() . "','$boardid','" . $userdata['userid'] . "')");
    $db->unbuffered_query("UPDATE cc" . $n . "_users SET newallianzmessage='1' WHERE allianzid='" . $userdata['allianzid'] .
        "'");

    redirect($modul_name, 'alliance', 'use_bord', array('boardid' => $boardid));
}

if ($action == "delpost")
{

    if ($userdata['is_ali_admin'] == 0)
    {
        error_page("$ln_allianz_php_2");
    }

    $id = intval($_GET['id']);
    if (!$id)
    {
        error_page("Fehler keine ID");
    }
    $result = $db->query("SELECT * FROM cc" . $n . "_amessage WHERE messageid='$id' AND allianzid='" . $userdata['allianzid'] .
        "'");
    $row = $db->fetch_array($result);
    if ($row['fromuserid'] != $userdata['userid'] && $userdata['is_ali_admin'] == 0)
    {
        error_page($ln_allianz_e_2);
    }
    $db->query("DELETE FROM cc" . $n . "_amessage WHERE messageid='$id' AND allianzid='$userdata[allianzid]'");
    redirect($modul_name, 'alliance', 'use_bord');
}


if ($action == "bewerben")
{
    $id = intval($_GET['id']);

    if (intval($userdata['allianzid']) > 0)
    {
        show_error('ln_allianz_e_7', $modul_name);
    }


    $result_e = $db->query("SELECT * FROM cc" . $n . "_allianz WHERE aid ='$id' ");
    while ($row_e = $db->fetch_array($result_e))
    {
        $a_name = $row_e['name'];
    }

    $tpl->assign('id', $id);
    $tpl->assign('aliname', $a_name);

    template_out('ali_application.html', $modul_name);

    exit();
}
if ($action == "bewerben_go")
{
    $id = intval($_GET['id']);

    if (intval($userdata['allianzid']) > 0)
    {
        show_error('ln_allianz_e_7', $modul_name);
    }

    $b_text = c_trim($_POST['b_text']);
    $uid = $userdata['userid'];
    $uid_name = $userdata['username'];
    $b_date = time();
    $ali_name = allianz($id);

    $ad_id = 0;
    $result = $db->query("SELECT * FROM cc" . $n . "_users WHERE allianzid='$id' and is_ali_admin ='1' ");
    while ($row = $db->fetch_array($result))
    {
        $ad_id = $row['userid'];
    }
    if ($ad_id > 0)
    {

        $db->unbuffered_query("Insert INTO cc" . $n .
            "_allianz_bewerbung (allianz_id ,bewerber_id ,datum,bewerber_text) VALUES('$id','$uid','$b_date','$b_text')");
        // benachrichtigung des admins
        $bewerbungs_text = "$ln_allianz_php_5";

        make_ingamemail($userdata['userid'], $ad_id, $ln_allianz_b_in_1, $bewerbungs_text);
        make_ingamemail($userdata['userid'], $userdata['userid'], $ln_allianz_b_in_1, "Deine Bewerbung bei " . $ali_name .
            " wurde abgesendet");


    }
    else
    {
        show_error('ln_allianz_php_7', $modul_name);
    }

    $modul = get_modulname(6);
    redirect($modul[0], $modul[1], 'main');
}

if ($action == "bewerben_accept")
{
    $id = intval($_GET['id']);
    $my_aid = $userdata['allianzid'];
    $bewerber_id = 0;
    $uid = $userdata['userid'];
    $uid_name = $userdata['username'];
    $result = $db->query("SELECT * FROM cc" . $n . "_allianz_bewerbung WHERE bewerbung_id ='$id' and allianz_id  ='$my_aid' ");
    while ($row = $db->fetch_array($result))
    {

        $bewerber_allianz_id = $row['allianz_id'];
        $bewerber_id = $row['bewerber_id'];
        if ($bewerber_allianz_id > 0)
        {

            $result_pw = $db->query("SELECT password FROM cc" . $n . "_allianz WHERE aid ='$bewerber_allianz_id'");
            $row_pw = $db->fetch_array($result_pw);
            $kennwort = $row_pw['password'];

            $bewerbungs_text = "$ln_allianz_php_8: $kennwort";

            make_ingamemail($uid, $bewerber_id, $ln_allianz_php_9, $bewerbungs_text);


            $db->unbuffered_query("Delete from cc" . $n . "_allianz_bewerbung where bewerbung_id  ='$id' ");

            redirect($modul_name, 'alliance', 'main');
        }
    }
}

if ($action == "bewerben_cancel")
{
    $id = intval($_GET['id']);
    $my_aid = $userdata['allianzid'];
    $bewerber_id = 0;
    $uid = $userdata['userid'];
    $uid_name = $userdata['username'];
    $result = $db->query("SELECT * FROM cc" . $n . "_allianz_bewerbung WHERE bewerbung_id ='$id' and allianz_id  ='$my_aid' ");
    while ($row = $db->fetch_array($result))
    {

        $bewerber_allianz_id = $row['allianz_id'];
        $bewerber_id = $row['bewerber_id'];
        if ($bewerber_allianz_id > 0)
        {

            $bewerbungs_text = "$ln_allianz_php_10";
            make_ingamemail($uid, $bewerber_id, $ln_allianz_php_11, $bewerbungs_text);

            $db->unbuffered_query("Delete from cc" . $n . "_allianz_bewerbung where bewerbung_id  ='$id' ");
            redirect($modul_name, 'alliance', 'main');
        }
    }
}

if ($action == "change_forum_del")
{
    // Thx to [GodLesZ]

    if (is_allowed("change_forum") == 0)
    {
        error_page($ln_allianz_php_2);
    }

    $ali_id = $userdata['allianzid'];
    $forumID = intval($_GET['forumid']);

    $db->query("DELETE FROM `cc" . $n . "_forum` WHERE `si_forum_id` = '" . $forumID . "'");
    $db->query("DELETE FROM `cc" . $n . "_forum_last` WHERE `forum_id` = '" . $forumID . "'");
    $db->query("DELETE FROM `cc" . $n . "_forum_posts` WHERE `si_forum_id` = '" . $forumID . "'");
    $db->query("DELETE FROM `cc" . $n . "_forum_topics` WHERE `si_forum_id` = '" . $forumID . "'");

    redirect($modul_name, 'alliance', 'change_forum');
}

if ($action == "get_info")
{
    $id = intval($_GET['id']);

    if (!$id)
    {
        show_error('ln_allianz_e_4', $modul_name);
    }
    $result = $db->query("SELECT * FROM cc" . $n . "_allianz WHERE aid='$id'");
    $row = $db->fetch_array($result);

    $banner = trim($row['imageurl']);
    if ($banner == "")
    {
        $banner = LITO_IMG_PATH_URL . $modul_name . "/no_ali_banner.png";
    }

    $description = bb2html($row['text_long']);

    $ibit = "";
    $result = $db->query("SELECT userid,username,is_ali_admin,status FROM cc" . $n . "_users WHERE allianzid='$id' ORDER BY is_ali_admin DESC");
    while ($i = $db->fetch_array($result))
    {

        $ibit .= generate_messagelink_smal($i[username]) . " " . generate_userlink($i[userid], $i[username]);
        if ($i['is_ali_admin'] == 1)
        {
            $ibit .= " (Leiter)";
        }
        $ibit .= " $img<br>";
    }

    $tpl->assign('is_in_ali', (intval($userdata['allianzid']) > 0 ? 1 : 0));
    $tpl->assign('banner', $banner);
    $tpl->assign('name', $row['name']);
    $tpl->assign('text', $row['text']);
    $tpl->assign('points', intval($row['points']));
    $tpl->assign('ibit', $ibit);
    $tpl->assign('description', $description);
    $tpl->assign('ali_id', $row['aid']);

    template_out('alliance.html', $modul_name);
}
