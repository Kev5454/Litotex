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

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($db))
{

    require ("./config.php");
    require ("./class_db_mysql.php");
    require ("./../options/options.php");
    require ("./functions.php");


    $key = (isset($_REQUEST['key']) ? filter_var($_REQUEST['key'], FILTER_SANITIZE_STRING) : null);
    $server = (isset($_REQUEST['sid']) ? filter_var($_REQUEST['sid'], FILTER_SANITIZE_STRING) : null);
    if (!isset($op_update_key) || empty($key) || empty($server) || strlen($key) != 32 || $op_update_key != $key)
    {
        exit();
    }
    $db = new db($dbhost, $dbuser, $dbpassword, $dbbase, $dbport);
    $n = (int)$server;
}

$time_start = time();
trace_msg("point update start:" . date("d.m.Y (H:i:s)", $time_start), 999);


$tmm = $time_start - 2592000;
trace_msg("point update time dif:" . date("d.m.Y (H:i:s)", $tmm), 999);
$db->query("UPDATE cc" . $n . "_users SET lastpoints=points, points='0' WHERE points>'0'");
$db->query("UPDATE cc" . $n . "_users SET status='3' WHERE lastactive<'$tmm' AND (status<'4' OR status>'5')");
$db->query("UPDATE cc" . $n . "_users SET status='0' WHERE lastactive>='$tmm' AND status='3'");
$db->query("UPDATE cc" . $n . "_allianz SET points='0' WHERE points>'0'");

$db->query("UPDATE cc" . $n . "_countries SET points='0' WHERE points>'0'");
// berechnung punkte gebäude
$update_counter = 0;
$result_b = $db->query("SELECT * FROM cc" . $n . "_buildings");

$sql = '';
while ($row_b = $db->fetch_array($result_b))
{
    $update_counter++;
    $cur_race = $row_b['race'];
    $cur_tabless = $row_b['tabless'];
    $cur_points = intval($row_b['points']);
    trace_msg("point buildings $cur_tabless [race:$cur_race ] = $cur_points", 999);

    if ($cur_points <= 0)
    {
        trace_msg("ERROR no points for $cur_tabless [race:$cur_race ] ", 999);
    }
    else
    {
        $sql .= "UPDATE cc" . $n . "_countries SET points=points+$cur_tabless * $cur_points WHERE race='" . $cur_race . "';";
    }
}
$db->multi_query($sql);

// berechnung punkte forschungen
$result_b = $db->query("SELECT * FROM cc" . $n . "_explore");
$sql = '';
while ($row_b = $db->fetch_array($result_b))
{
    $cur_race = $row_b['race'];
    $cur_tabless = $row_b['tabless'];
    $cur_points = intval($row_b['points']);
    if ($cur_points <= 0)
    {
        trace_msg("ERROR no points for $cur_tabless [race:$cur_race ] ", 999);
    }
    else
    {
        $sql .= "UPDATE cc" . $n . "_countries SET points=points+$cur_tabless * $cur_points WHERE race='" . $cur_race . "';";
    }
}
$db->multi_query($sql);
// berechnung punkte einheiten
$result_b = $db->query("SELECT * FROM cc" . $n . "_soldiers");
$sql = '';
while ($row_b = $db->fetch_array($result_b))
{
    $cur_race = $row_b['race'];
    $cur_tabless = $row_b['tabless'];
    $cur_points = intval($row_b['points']);
    if ($cur_points <= 0)
    {
        trace_msg("ERROR no points for $cur_tabless [race:$cur_race ] ", 999);
    }
    else
    {
        $sql .= "UPDATE cc" . $n . "_countries SET points=points+$cur_tabless * $cur_points WHERE race='" . $cur_race . "';";
    }
}

$db->multi_query($sql);
// berechnung punkte einheiten in gruppen

$result_b = $db->query("SELECT * FROM cc" . $n . "_countries");
$sql = '';
while ($row_b = $db->fetch_array($result_b))
{
    $cur_punkte = 0;
    $cur_race = $row_b['race'];
    $result_c = $db->query("SELECT * FROM cc" . $n . "_groups WHERE islandid = " . $row_b['islandid'] . "");
    while ($row_c = $db->fetch_array($result_c))
    {
        $result_d = $db->query("SELECT * FROM cc" . $n . "_groups_inhalt WHERE group_id = " . $row_c['groupid'] . "");
        while ($row_d = $db->fetch_array($result_d))
        {
            $result_e = $db->query("SELECT * FROM cc" . $n . "_soldiers WHERE tabless = '" . $row_d['type'] . "' AND race='" . $cur_race .
                "'");
            $row_e = $db->fetch_array($result_e);
            $anzahl = $row_d['anzahl'];
            $punkte = $row_e['points'];

            $cur_punkte += ($anzahl * $punkte);
        }
    }

    if ($cur_points <= 0)
    {
        trace_msg("ERROR no points for groups [race:$cur_race ] ", 999);
    }
    else
    {
        $sql .= "UPDATE cc" . $n . "_countries SET points=points+$cur_punkte WHERE islandid = '" . $row_b['islandid'] . "';";
    }
}
$db->multi_query($sql);

// eintragen der punkte bei user


$result = $db->query("SELECT * FROM cc" . $n . "_users");
$sql = '';
while ($row = $db->fetch_array($result))
{
    $cur_uid = $row['userid'];
    $result_points = $db->query("SELECT sum(points) as point_all FROM cc" . $n . "_countries WHERE userid='" . $cur_uid .
        "'");
    $row_p = $db->fetch_array($result_points);
    $ret_all = $row_p['point_all'];
    $sql .= "UPDATE cc" . $n . "_users SET points='" . $ret_all . "' WHERE userid='" . $cur_uid . "' and serveradmin='0';";
}
$db->multi_query($sql);


// eintragen der punkte bei ally
$result = $db->query("SELECT * FROM cc" . $n . "_allianz");
$sql = '';
while ($row = $db->fetch_array($result))
{
    $cur_aid = $row['aid'];
    $result_points = $db->query("SELECT sum(points) as point_all FROM cc" . $n . "_users WHERE allianzid='" . $cur_aid . "'");
    $row_p = $db->fetch_array($result_points);
    $ret_all = $row_p['point_all'];
    $sql .= "UPDATE cc" . $n . "_allianz SET points='" . $ret_all . "' WHERE aid='" . $cur_aid . "';";
}
$db->multi_query($sql);


$end_time = $time_start;
$dauer = $end_time - $time_start;
trace_msg("point update done with $dauer sec ($update_counter calculations)", 999);
print ("point update done with $dauer sec ($update_counter calculations) <br>");

//***************************
//MINIMAP
//***************************

$resultx = $db->query("SELECT max(x) AS x FROM cc" . $n . "_crand");
$map_x = $db->fetch_array($resultx);

$resulty = $db->query("SELECT max(y) AS y FROM cc" . $n . "_crand");
$map_y = $db->fetch_array($resulty);

$x_size = $map_x['x']; // games size x
$y_size = $map_y['y']; // games size y
$pixelsize = 5; // wide per country
$grid = 1; // grid on or off
$filename = $litotex_path . "images/minimap/map_mini.png";

$image = imagecreatetruecolor($x_size * $pixelsize, $x_size * $pixelsize);
$background_color = imagecolorallocate($image, 230, 229, 159);
$grid_color = imagecolorallocate($image, 0, 0, 0);
$country_color = imagecolorallocate($image, 255, 0, 0);

for ($x = 0; $x <= $x_size; $x++)
{
    for ($y = 0; $y <= $y_size; $y++)
    {
        $new_x_pos = $x * $pixelsize;
        $new_y_pos = $y * $pixelsize;
        $new_x_pos_end = $new_x_pos + $pixelsize;
        $new_y_pos_end = $new_y_pos + $pixelsize;
        imagefilledrectangle($image, $new_x_pos, $new_y_pos, $new_x_pos_end, $new_y_pos_end, $background_color);
    }
}


$all_land_show = $db->query("SELECT * FROM cc" . $n . "_crand where used = '1' ORDER BY x,y ASC ");
while ($row = $db->fetch_array($all_land_show))
{
    $new_x_pos = $row['x'] * $pixelsize;
    $new_y_pos = $row['y'] * $pixelsize;
    $new_x_pos_end = $new_x_pos + $pixelsize;
    $new_y_pos_end = $new_y_pos + $pixelsize;
    imagefilledrectangle($image, $new_x_pos, $new_y_pos, $new_x_pos_end, $new_y_pos_end, $country_color);
}

if ($grid == 1)
{
    for ($x = 0; $x <= $x_size; $x++)
    {
        for ($y = 0; $y <= $y_size; $y++)
        {
            $new_x_pos = $x * $pixelsize;
            $new_y_pos = $y * $pixelsize;
            $new_x_pos_end = $new_x_pos + $pixelsize;
            $new_y_pos_end = $new_y_pos + $pixelsize;
            if ($grid == 1)
            {
                imagerectangle($image, $new_x_pos, $new_y_pos, $new_x_pos_end, $new_y_pos_end, $grid_color);
            }
        }
    }
    $new_x_pos = (++$x) * $pixelsize;
    $new_y_pos = (++$y) * $pixelsize;
    imagerectangle($image, $new_x_pos, $new_y_pos, $new_x_pos + $pixelsize, $new_y_pos + $pixelsize, $grid_color);
}


$all_land_show = $db->query("SELECT * FROM cc" . $n . "_crand where used = '1' ORDER BY x,y ASC ");
while ($row = $db->fetch_array($all_land_show))
{
    $new_x_pos = $row['x'] * $pixelsize;
    $new_y_pos = $row['y'] * $pixelsize;
    $new_x_pos_end = $new_x_pos + $pixelsize;
    $new_y_pos_end = $new_y_pos + $pixelsize;
    imagefilledrectangle($image, $new_x_pos, $new_y_pos, $new_x_pos_end, $new_y_pos_end, $country_color);
}

// delete old file
if (file_exists($filename))
{
    unlink($filename);
}
// write new file
imagepng($image, $filename);
