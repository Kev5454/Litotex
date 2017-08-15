<?php

/*
************************************************************
Litotex BrowsergameEngine
https://litotex.info
http://www.Litotex.de
http://www.freebg.de

Copyright (c) 2017 K. Wehmeyer
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

$modul_name = "acp_units";
require ($_SESSION['litotex_start_acp'] . 'acp/includes/perm.php');
$menu_name = "Einheiteneditor";
$tpl->assign('menu_name', $menu_name);

if ($action == "cp")
{ //By GH1234 AK Jonas Schwabe
    if (!isset($_POST['cpfrom']) || !isset($_POST['cpto']))
    {
        error_msg('Es wurden nicht alle nötigen Daten übergeben.');
        exit;
    }
    $from = filter_var($_GET['cpfrom'], FILTER_SANITIZE_NUMBER_INT);
    $to = filter_var($_GET['cpto'], FILTER_SANITIZE_NUMBER_INT);
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

    $sql = '';
    $db->query("DELETE FROM `cc" . $n . "_soldiers` WHERE `race` = '" . $to . "'");
    $units_q = $db->query("SELECT * FROM `cc" . $n . "_soldiers` WHERE `race` = '" . $from . "'");
    while ($unit = $db->fetch_array($units_q))
    {
        $sql .= "INSERT INTO `cc" . $n .
            "_soldiers` (`name`, `tabless`, `res1`, `res2`, `res3`, `res4`, `stime`, `description`, `AP`, `VP`, `race`, `traveltime`, `required`, `required_level`, `points`, `solpic`, `sol_type`) VALUES ('" .
            $db->escape_string($unit['name']) . "', '" . $unit['tabless'] . "', '" . $unit['res1'] . "', '" . $unit['res2'] . "', '" .
            $unit['res3'] . "', '" . $unit['res4'] . "', '" . $unit['stime'] . "', '" . $db->escape_string($unit['description']) .
            "', '" . $unit['AP'] . "', '" . $unit['VP'] . "', '" . $to . "', '" . $unit['traveltime'] . "', '" . $unit['required'] .
            "', '" . $unit['required_level'] . "', '" . $unit['points'] . "', '" . $unit['solpic'] . "', '" . $unit['sol_type'] .
            "');";
    }
    $db->multi_query($sql);
    redirect($modul_name, 'edit_units', 'main');
}
elseif ($action == "main")
{

    $ras = "";
    $build_option = "";
    $sol_type_def = "";
    $load_name = "";
    $load_ress_1 = "";
    $load_ress_2 = "";
    $load_ress_3 = "";
    $load_ress_4 = "";
    $load_size = "";
    $load_points = "";
    $load_buildtime = "";
    $load_ap = "";
    $load_vp = "";
    $load_req_level = "";
    $load_b_pic = "";
    $description = "";
    $error_buildings = "";
    $id = "";
    $positions = 0;


    $make_aktion = "action=new&cxid=$sid";
    $build_option = make_soldier_option_choice("build_option", "");
    $required = make_explore_option_choice("required", "");
    $sol_type_def = make_soldier_type_choice("sol_type", "");
    $not_show = 0;

    $load_b_pic = LITO_IMG_PATH_URL . "acp_units/keins.png";


    $result = $db->query("SELECT * FROM cc" . $n . "_rassen");
    $race_count = 0;
    $out_race = array();
    while ($row_ras = $db->fetch_array($result))
    {
        $race_count++;
        $out_race[] = $row_ras;
    }
    $tpl->assign('race_all', $out_race);


    $result_options = $db->query("SELECT * FROM cc" . $n . "_soldiers_option");
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
            $result = $db->query("SELECT * FROM cc" . $n . "_soldiers where race='$i' and tabless='$name_tag' and required !='' and required_level > 0");
            $count = 0;
            while ($row_ras = $db->fetch_array($result))
            {
                $new_b_id = $row_ras['sid'];
                $del_link = "<a href=\"edit_units.php?action=del&del_id=$new_b_id\"><img src=\"" . LITO_IMG_PATH_URL .
                    "acp_units/delete.png\" alt=\"l&ouml;schen\" title =\"l&ouml;schen\" border=\"0\"></a> ";
                $value[$i] = $del_link . "<a href=\"edit_units.php?id=" . $new_b_id . "\">" . $row_ras['name'] . "</a> ";
            }
        }
        $out_units[] = $value;
    }

    $tpl->assign('units', $out_units);
    $lade_id = (isset($_GET['id']) ? filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT) : 0);
    if ($lade_id > 0)
    {
        $not_show = 1;
        $result_loader = $db->query("SELECT * FROM cc" . $n . "_soldiers where sid='$lade_id '");

        while ($row_load = $db->fetch_array($result_loader))
        {
            $load_name = $row_load['name'];
            $load_ress_1 = $row_load['res1'];
            $load_ress_2 = $row_load['res2'];
            $load_ress_3 = $row_load['res3'];
            $load_ress_4 = $row_load['res4'];
            $load_size = $row_load['traveltime'];
            $load_points = $row_load['points'];
            $load_buildtime = $row_load['stime'];
            $positions = $row_load['p'];
            $load_b_pic = (empty($row_load['solpic']) ? LITO_IMG_PATH_URL . "acp_units/keins.png" : $row_load['solpic']);

            $load_ap = $row_load['AP'];
            $load_vp = $row_load['VP'];
            $load_req_level = $row_load['required_level'];
            $required = make_explore_option_choice("required", $row_load['required']);
            $ras = make_race_choice("rasse", $row_load['race']);
            $build_option = make_soldier_option_choice("build_option", $row_load['tabless']);
            $make_aktion = "action=update&cxid=$sid&id=$lade_id";
            $description = $row_load['description'];
            $sol_type_def = make_soldier_type_choice("sol_type", $row_load['sol_type']);
        }
    }
    else
    {
        $ras = make_race_choice("rasse", 0);
    }

    //load error buildings
    $result_error = $db->query("SELECT * FROM cc" . $n .
        "_soldiers where race='' or tabless='' or tabless='0' or required='' or required_level='0'");
    $count = 0;
    $error_buildings = "";
    while ($row_b_error = $db->fetch_array($result_error))
    {
        $name = $row_b_error['name'];
        $name_id = $row_b_error['sid'];
        $del_link = "<a href=\"edit_units.php?action=del&del_id=$name_id\"><img src=\"" . LITO_IMG_PATH_URL .
            "acp_units/delete.png\" alt=\"l&ouml;schen\" title =\"l&ouml;schen\" border=\"0\"></a> ";
        $new_name = $del_link . "<a href=\"edit_units.php?id=$name_id\">$name</a> ";
        $error_buildings .= $new_name . "<br><br>";

    }

    $tpl->assign('ras', $ras);
    $tpl->assign('build_option', $build_option);
    $tpl->assign('sol_type_def', $sol_type_def);
    $tpl->assign('load_name', $load_name);
    $tpl->assign('load_ress_1', $load_ress_1);
    $tpl->assign('load_ress_2', $load_ress_2);
    $tpl->assign('load_ress_3', $load_ress_3);
    $tpl->assign('load_ress_4', $load_ress_4);
    $tpl->assign('load_size', $load_size);
    $tpl->assign('load_points', $load_points);
    $tpl->assign('load_buildtime', $load_buildtime);
    $tpl->assign('load_ap', $load_ap);
    $tpl->assign('load_vp', $load_vp);
    $tpl->assign('load_req_level', $load_req_level);
    $tpl->assign('load_b_pic', $load_b_pic);
    $tpl->assign('description', $description);
    $tpl->assign('make_aktion', $make_aktion);
    $tpl->assign('required', $required);
    $tpl->assign('error_buildings', $error_buildings);
    $tpl->assign('positions', $positions);

    template_out('edit_units.html', $modul_name);
}
elseif ($action == "update")
{
    $name = filter_var($_POST['buildingname'], FILTER_SANITIZE_STRING);

    $rasse = filter_var($_POST['rasse'], FILTER_SANITIZE_NUMBER_INT);

    $gold = filter_var($_POST['kost1'], FILTER_SANITIZE_NUMBER_INT);
    $stone = filter_var($_POST['kost2'], FILTER_SANITIZE_NUMBER_INT);
    $oil = filter_var($_POST['kost3'], FILTER_SANITIZE_NUMBER_INT);
    $exp = filter_var($_POST['kost4'], FILTER_SANITIZE_NUMBER_INT);

    $reisezeit = filter_var($_GET['traveltime'], FILTER_SANITIZE_NUMBER_INT);
    $einmheiten_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

    $ap = filter_var($_POST['value_ap'], FILTER_SANITIZE_NUMBER_INT);
    $vp = filter_var($_POST['value_vp'], FILTER_SANITIZE_NUMBER_INT);
    $point = filter_var($_POST['points'], FILTER_SANITIZE_NUMBER_INT);
    $build_time = filter_var($_POST['buildtime'], FILTER_SANITIZE_NUMBER_INT);


    $required = filter_var($_POST['required'], FILTER_SANITIZE_STRING);
    $required_level = filter_var($_POST['required_level'], FILTER_SANITIZE_NUMBER_INT);
    $build_pic = filter_var($_POST['buildpic'], FILTER_SANITIZE_STRING);

    $b_option = filter_var($_POST['build_option'], FILTER_SANITIZE_STRING);
    $description = filter_var($_POST['descr'], FILTER_SANITIZE_STRING);
    $positions = filter_var($_POST['positions'], FILTER_SANITIZE_NUMBER_INT);
    $sol_type = filter_var($_POST['sol_type'], FILTER_SANITIZE_NUMBER_INT);

    $kurz = get_soldiers_tabless_name($b_option);

    $db->multi_query("UPDATE cc" . $n . "_soldiers set tabless='' and race='' where tabless='$b_option' and race='$rasse';UPDATE cc" .
        $n . "_soldiers SET p='$positions',sol_type='$sol_type', solpic='$build_pic',points='$point',required_level='$required_level',tabless='$kurz',description='$description',name='$name',res1='$gold',res2='$stone',res3='$oil',res4='$exp',stime='$build_time',race='$rasse',traveltime='$reisezeit',AP='$ap',VP='$vp',required='$required' WHERE sid='$einmheiten_id';");

    redirect($modul_name, 'edit_units', 'main');
}
elseif ($action == "new")
{
    $name = filter_var($_POST['buildingname'], FILTER_SANITIZE_STRING);

    $rasse = filter_var($_POST['rasse'], FILTER_SANITIZE_NUMBER_INT);

    $gold = filter_var($_POST['kost1'], FILTER_SANITIZE_NUMBER_INT);
    $stone = filter_var($_POST['kost2'], FILTER_SANITIZE_NUMBER_INT);
    $oil = filter_var($_POST['kost3'], FILTER_SANITIZE_NUMBER_INT);
    $exp = filter_var($_POST['kost4'], FILTER_SANITIZE_NUMBER_INT);

    $reisezeit = filter_var($_GET['traveltime'], FILTER_SANITIZE_NUMBER_INT);

    $ap = filter_var($_POST['value_ap'], FILTER_SANITIZE_NUMBER_INT);
    $vp = filter_var($_POST['value_vp'], FILTER_SANITIZE_NUMBER_INT);
    $point = filter_var($_POST['points'], FILTER_SANITIZE_NUMBER_INT);
    $build_time = filter_var($_POST['buildtime'], FILTER_SANITIZE_NUMBER_INT);


    $required = filter_var($_POST['required'], FILTER_SANITIZE_STRING);
    $required_level = filter_var($_POST['required_level'], FILTER_SANITIZE_NUMBER_INT);
    $build_pic = filter_var($_POST['buildpic'], FILTER_SANITIZE_STRING);

    $b_option = filter_var($_POST['build_option'], FILTER_SANITIZE_STRING);
    $description = filter_var($_POST['descr'], FILTER_SANITIZE_STRING);

    $sol_type = filter_var($_POST['sol_type'], FILTER_SANITIZE_NUMBER_INT);

    $kurz = get_soldiers_tabless_name($b_option);

    $db->multi_query("UPDATE cc" . $n . "_soldiers set tabless='' and race='' where tabless='$b_option' and race='$rasse';insert Into cc" .
        $n . "_soldiers (sol_type,description,tabless,required_level,points,solpic,name,res1,res2,res3,res4,stime,race,traveltime,AP,VP,required)VALUES ('$sol_type','$description','$kurz','$required_level','$point','$build_pic','$name','$gold','$stone','$oil','$exp','$build_time','$rasse','$reisezeit','$ap','$vp','$required');");

    if (if_spalte_exist($kurz, "cc" . $n . "_countries") == 0)
    {
        $update = $db->query("ALTER TABLE cc" . $n . "_countries ADD " . $kurz . " INT( 10 ) NOT NULL DEFAULT '0'");
    }
    redirect($modul_name, 'edit_units', 'main');
}
elseif ($action == "del")
{
    $del_id = filter_var($_GET['del_id'], FILTER_SANITIZE_NUMBER_INT);

    if ($del_id <= 0)
    {
        admin_error_page("$ln_error_20");
        exit();
    }

    $result = $db->query("SELECT tabless,sid FROM cc" . $n . "_soldiers WHERE sid='" . $del_id . "'");
    $row = $db->fetch_array($result);
    if (!empty($row['tabless']))
    {
        $db->query("delete from cc" . $n . "_soldiers where sid='" . $del_id . "'");
    }
    redirect($modul_name, 'edit_units', 'main');
}
