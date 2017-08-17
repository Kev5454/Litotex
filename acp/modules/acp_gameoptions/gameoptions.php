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
$modul_name = "acp_gameoptions";

require ($_SESSION['litotex_start_acp'] . 'acp/includes/perm.php');

if ($action == "main")
{

    $result = $db->query("SELECT admin_sub_name ,admin_sub_id  FROM cc" . $n . "_menu_admin_sub where admin_sub_id ='10'");
    $row = $db->fetch_array($result);
    $menu_name = $row['admin_sub_name'];

    $result = $db->query("SELECT count( op_id ) AS anz FROM cc" . $n . "_menu_admin_opt");
    $row = $db->fetch_array($result);
    $variablen_count = $row['anz'];


    $sql = "SELECT * from  cc" . $n . "_menu_admin_sub where menu_admin_id 	 = '6' and sub_name_sort >'1' ";
    $result_users = $db->query($sql);


    $como = "<select class=\"combo\" name=\"selkat\">";
    while ($row_g = $db->fetch_array($result_users))
    {
        $como .= "<option value=\"" . $row_g['admin_sub_id'] . "\">" . $row_g['admin_sub_name'] . "</option>";
    }
    $como .= "</select>";
    $tpl->assign('menu_name', $menu_name);
    $tpl->assign('var_count', $variablen_count);
    $tpl->assign('combo_cat', $como);
    template_out('gameoptions.html', $modul_name);
}
elseif ($action == "new_save")
{
    $new_var_name = filter_var($_POST['varname'], FILTER_SANITIZE_STRING);
    $new_var_titel = filter_var($_POST['vartitel'], FILTER_SANITIZE_STRING);
    $new_var_type = filter_var($_POST['select_type'], FILTER_SANITIZE_STRING);
    $new_var_kat = filter_var($_POST['selkat'], FILTER_SANITIZE_NUMBER_INT);

    $first_char = substr($new_var_name, 0, 3);

    if ($first_char <> "op_")
    {
        error_msg("Es ist ein Fehler aufgetrten.<br>Bitte darauf achten, das alle Variablen mit 'op_' beginnen !!! ");
        exit;
    }

    if (empty($new_var_name) || empty($new_var_titel) || empty($new_var_type) || $new_var_kat <= 0)
    {
        error_msg("Es ist ein Fehler aufgetrten.");
        exit();
    }
    $db->unbuffered_query("INSERT INTO cc" . $n . "_menu_admin_opt SET varname='$new_var_name' , title='$new_var_titel',type='$new_var_type', admin_sub_id='$new_var_kat', save='1'");

    template_out('gameoptions.html', $modul_name);
}
elseif ($action == "sel_cat")
{
    $menu_number = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    if ($menu_number <= 0)
    {
        redirect('gameoptions', 'gameoptions', 'main');
        exit();
    }

    $result = $db->query("SELECT admin_sub_name ,admin_sub_id  FROM cc" . $n . "_menu_admin_sub where admin_sub_id ='" . $menu_number .
        "'");
    $row = $db->fetch_array($result);
    $menu_name = $row['admin_sub_name'];


    $result = $db->query("SELECT * FROM cc" . $n . "_menu_admin_opt WHERE invisable=0 AND admin_sub_id='" . $menu_number .
        "'");
    if ($db->num_rows($result) <= 0)
    {
        error_msg("Keine Daten vorhanden.");
        exit();
    }

    while ($row = $db->fetch_array($result))
    {
        if ($row['type'] == "truefalse")
        {
            $yes = (($row['value'] == 1) ? " checked" : "");
            $no = (($row['value'] == 0) ? " checked" : "");
            $type = "Ja <input class=\"radio\" type=\"radio\" name=\"" . $row['varname'] . "\" value=\"1\"" . $yes .
                "> Nein <input type=\"radio\" name=\"" . $row['varname'] . "\" value=\"0\"" . $no . ">";
        }
        elseif ($row['type'] == "text")
        {
            $type = "<input type=\"text\" class=\"textinput\" value=\"" . $row['value'] . "\" name=\"" . $row['varname'] . "\" size=\"55\">";
        }
        elseif ($row['type'] == "textarea")
        {
            $type = "<textarea rows=\"10\" id=\"" . $row['varname'] . "\"  class=\"textarea\" cols=\"50\" name=\"" . $row['varname'] .
                "\">" . $row['value'] . "</textarea>";
        }
        else
        {
            $type = "unbekannter Typ";
        }

        $option_bit[] = $row['title'];
        $option_bit[] = $type;
    }


    $tpl->assign('menu_name', $menu_name);
    $tpl->assign('save_id', $menu_number);
    $tpl->assign('data', $option_bit);
    $tpl->assign('tr', array('bgcolor="#eeeeee"', 'bgcolor="#dddddd"'));
    template_out('gameoptions_save.html', $modul_name);
}
elseif ($action == "submitOptions")
{
    $optiongroupid = filter_var($_REQUEST['id'], FILTER_SANITIZE_NUMBER_INT);

    $result = $db->query("SELECT * FROM cc" . $n . "_menu_admin_opt where admin_sub_id='$optiongroupid' ");
    while ($row = $db->fetch_array($result))
    {

        $db->query("UPDATE cc" . $n . "_menu_admin_opt SET value='" . $_POST[$row['varname']] . "' WHERE varname='" . $row['varname'] .
            "' AND admin_sub_id='" . $optiongroupid . "'");
    }


    require (LITO_ROOT_PATH . "includes/class_options.php");
    $option = new option(LITO_ROOT_PATH . "options/");
    $option->write();

    redirect('acp_gameoptions', 'gameoptions', 'sel_cat', array('id' => $optiongroupid));
}
