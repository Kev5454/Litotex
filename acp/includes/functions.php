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

if ( !function_exists( 'session_unregister' ) )
{
    function session_unregister( $name )
    {
        unset( $_SESSION[$name] );
    }
}

function _mkdir( $directory,$public_access = true,$mode = 0777 )
{
    $dirName = LITO_ROOT_PATH . $directory . ( substr( $directory,-1 ) == DIRECTORY_SEPARATOR ? '' : DIRECTORY_SEPARATOR );
    if ( !is_dir( $dirName ) )
    {
        mkdir( $dirName );
    }
    chmod( $dirName,$mode );

    if ( $public_access == false )
    {
        copy( LITO_ROOT_PATH . 'includes' . DIRECTORY_SEPARATOR . '.htaccess',$dirName . '.htaccess' );
    }
}



function redirect($module, $file, $action = 'main', $vars = array())
{
    global $litotex_url;

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

    header("LOCATION: " . $litotex_url . "acp/modules/" . $module . "/" . $file . ".php?action=" . $action . $get);
    exit();
}


function removeDirectory($dir)
{
    if (!file_exists($dir))
    {
        return true;
    }
    if (!is_dir($dir) || is_link($dir))
    {
        return unlink($dir);
    }
    foreach (scandir($dir) as $item)
    {
        if ($item == '.' || $item == '..')
        {
            continue;
        }
        if (!removeDirectory($dir . DIRECTORY_SEPARATOR . $item, false))
        {
            chmod($dir . DIRECTORY_SEPARATOR . $item, 0777);
            if (!removeDirectory($dir . DIRECTORY_SEPARATOR . $item, false)) return false;
        }
    }
    return rmdir($dir);
}

function scanDirectory($rootDir, $results = array())
{
    $invisibleFileNames = array(
        ".",
        "..",
        );
    $dirContent = scandir($rootDir);
    foreach ($dirContent as $key => $content)
    {
        $path = $rootDir . $content;
        if (!in_array($content, $invisibleFileNames))
        {
            if (is_dir($path))
            {
                $results[] = $path . DIRECTORY_SEPARATOR;
                $results = scanDirectory($path . DIRECTORY_SEPARATOR, $results);
            }
            elseif (is_file($path))
            {
                $results[] = $path;
            }
        }
    }

    return $results;
}


function _copy($source, $dest)
{
    if (is_link($source))
    {
        return symlink(readlink($source), $dest);
    }

    if (is_file($source))
    {
        return copy($source, $dest);
    }

    if (!is_dir($dest))
    {
        mkdir($dest);
    }

    $dir = dir($source);
    while (false !== $entry = $dir->read())
    {
        // Skip pointers
        if ($entry == '.' || $entry == '..')
        {
            continue;
        }

        _copy("$source/$entry", "$dest/$entry");
    }

    $dir->close();
    return true;
}


function addDirectoryToZip($zip, $dir, $base)
{
    $newFolder = str_replace($base, '', $dir);
    $zip->addEmptyDir($newFolder);
    foreach (glob($dir . '/*') as $file)
    {
        if (is_dir($file))
        {
            $zip = $this->addDirectoryToZip($zip, $file, $base);
        }
        else
        {
            $newFile = str_replace($base, '', $file);
            $zip->addFile($file, $newFile);
        }
    }
    return $zip;
}

function setDownloadFile($filename, $downloadFileName = null)
{
    $downloadFileName = ($downloadFileName == null ? basename($downloadFileName) : $downloadFileName);

    if (!file_exists($filename))
    {
        echo 'Datei wurde nicht gefunden!';
        exit();
    }

    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"" . $downloadFileName . "\"");
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: " . filesize($filename));
    ob_end_flush();
    readfile($filename);
    exit();
}

/** error_page function show an error by text **/

function error_msg($message)
{
    global $tpl;
    $tpl->assign('LITO_ERROR', $message);
    template_out("error.html", "acp_core");
    exit();
}


function get_footer()
{
    global $db, $time_start;
    $time_end = explode(' ', substr(microtime(), 1));
    $time_end = $time_end[1] + $time_end[0];
    $run_time = $time_end - $time_start;
    return "time: " . number_format($run_time, 7, '.', '') . " sec <br/>query count: " . $db->number_of_querys();

}


function c_trim($string)
{
    return trim(filter_var($string, FILTER_SANITIZE_STRING));
}


// Funtion zum Traces von Informationen
// Diese Sind im Admin bereich sichtbar
// $error_type ist ebenfalls unter dem Admin Programm sichtbar
function Trace_Msg($message, $error_type)
{
    global $db, $n, $userdata;

    $message = addslashes($message);
    $db->query("INSERT INTO cc" . $n . "_debug(db_time, db_text ,db_type ,fromuserid) VALUES ('" . time() . "','$message','" .
        $error_type . "','" . $userdata['userid'] . "')");

    return;
}

function get_navigation()
{

    include (LITO_MODUL_PATH . 'acp_navigation/navigate.php');

    $navi = new navigation();
    $rrr = $navi->make_navigate();
    return $rrr;
}


function template_out($template_name, $from_modulname)
{
    global $tpl, $lang_suffix;

    $ret = is_modul_name_aktive($from_modulname);
    if ($ret == 0)
    {
        error_msg("Dieses Modul wurde vom Administrator deaktiviert.<br>Module has been disabled by the administrator.");
        exit();
    }

    $lang_file = LITO_LANG_PATH . $from_modulname . '/lang_' . $lang_suffix . '.php';
    $tpl->config_load($lang_file);
    $tpl->assign('LITO_NAVIGATION', get_navigation());


    $tpl->assign('LITO_GLOBAL_IMAGE_URL', LITO_GLOBAL_IMAGE_URL);
    $tpl->assign('LITO_IMG_PATH', LITO_IMG_PATH_URL . $from_modulname . DIRECTORY_SEPARATOR);
    $tpl->assign('LITO_MAIN_CSS', LITO_MAIN_CSS);
    $tpl->assign('GAME_FOOTER_MSG', get_footer());
    $tpl->assign('LITO_ROOT_PATH_URL', LITO_ROOT_PATH_URL);
    $tpl->assign('LITO_MODUL_PATH_URL', LITO_MODUL_PATH_URL);
    $tpl->assign('LITO_BASE_MODUL_URL', LITO_MODUL_PATH_URL);


    $tpl->display(LITO_THEMES_PATH . $from_modulname . DIRECTORY_SEPARATOR . $template_name);
}

function is_modul_name_aktive($modul_name)
{
    global $db, $n, $userdata;
    $result = $db->query("SELECT activated FROM cc" . $n . "_modul_admin where modul_name ='$modul_name' LIMIT 1");
    $row = $db->fetch_array($result);
    return intval($row['activated']);
}


function is_modul_id_aktive($modul_id)
{
    global $db, $n, $userdata;
    $result = $db->query("SELECT activated FROM cc" . $n . "_modul_admin where modul_admin_id ='$modul_id' LIMIT 1");
    $row = $db->fetch_array($result);
    return intval($row['activated']);

}

function is_modul_installed($modul_name, $modul_version)
{

    global $tpl, $db, $n;
    $sql = "SELECT modul_admin_id FROM cc" . $n . "_modul_admin where modul_name='" . $db->escape_string($modul_name) .
        "' and current_version ='" . $db->escape_string($modul_version) . "' LIMIT 1";
    $result = $db->query($sql);
    $row = $db->fetch_array($result);
    return $row['modul_admin_id'];
}

function make_soldier_option_choice($name, $sel_name)
{
    global $db, $n;
    global $op_set_n_res1;
    global $op_set_n_res2;
    global $op_set_n_res3;
    global $op_set_n_res4;

    $out = "<select name=\"$name\" class=\"combo\">";
    $out .= "<option value=\"0\">keine Optionen</option>";
    $result = $db->query("SELECT * FROM cc" . $n . "_soldiers_option order by s_option_id");
    while ($row = $db->fetch_array($result))
    {
        $name_description = $row['description'];
        $name_description = str_replace("op_set_n_res1", $op_set_n_res1, $name_description);
        $name_description = str_replace("op_set_n_res2", $op_set_n_res2, $name_description);
        $name_description = str_replace("op_set_n_res3", $op_set_n_res3, $name_description);
        $name_description = str_replace("op_set_n_res4", $op_set_n_res4, $name_description);


        if (trim($sel_name) == trim($row['tabless']))
        {
            $out .= "<option value=\"" . $row['tabless'] . "\" selected>" . $name_description . "</option>";
        }
        else
        {
            $out .= "<option value=\"" . $row['tabless'] . "\">" . $name_description . "</option>";
        }

    }
    $out .= "</select>";
    return $out;
}


function make_explore_option_choice($name, $sel_name)
{
    global $db, $n;
    global $op_set_n_res1;
    global $op_set_n_res2;
    global $op_set_n_res3;
    global $op_set_n_res4;

    $out = "<select name=\"$name\" class=\"combo\">";
    $out .= "<option value=\"0\">keine Optionen</option>";
    $result = $db->query("SELECT * FROM cc" . $n . "_explore_option order by e_option_id");
    while ($row = $db->fetch_array($result))
    {
        $name_description = $row['description'];
        $name_description = str_replace("op_set_n_res1", $op_set_n_res1, $name_description);
        $name_description = str_replace("op_set_n_res2", $op_set_n_res2, $name_description);
        $name_description = str_replace("op_set_n_res3", $op_set_n_res3, $name_description);
        $name_description = str_replace("op_set_n_res4", $op_set_n_res4, $name_description);


        if (trim($sel_name) == trim($row['tabless']))
        {
            $out .= "<option value=\"" . $row['tabless'] . "\" selected>" . $name_description . "</option>";
        }
        else
        {
            $out .= "<option value=\"" . $row['tabless'] . "\">" . $name_description . "</option>";
        }

    }
    $out .= "</select>";
    return $out;
}

function make_soldier_type_choice($name, $sel_type)
{
    $out = "<select name=\"$name\" class=\"combo\">";
    if ($sel_type == 0)
    {
        $out .= " <option value=\"0\" selected>Angriff</option>";
    }
    else
    {
        $out .= " <option value=\"0\">Angriff</option>";
    }
    if ($sel_type == 1)
    {
        $out .= " <option value=\"1\" selected>Verteidigung</option>";
    }
    else
    {
        $out .= " <option value=\"1\">Verteidigung</option>";
    }
    if ($sel_type == 2)
    {
        $out .= " <option value=\"2\" selected>Landerweierung</option>";
    }
    else
    {
        $out .= " <option value=\"2\">Landerweierung</option>";
    }
    if ($sel_type == 3)
    {
        $out .= " <option value=\"3\" selected>Spion</option>";
    }
    else
    {
        $out .= " <option value=\"3\">Spion</option>";
    }


    $out .= "</select>";

    return $out;

}


function make_race_choice($name, $sel_id)
{
    global $db, $n, $userdata, $tpl;

    $out = "<select name=\"$name\" class=\"combo\">";
    $out .= "<option value=\"0\">nicht festgelegt</option>";
    $result = $db->query("SELECT * FROM cc" . $n . "_rassen order by rassenid");
    while ($row = $db->fetch_array($result))
    {

        if (intval($sel_id) == intval($row['rassenid']))
        {
            $out .= "<option value=\"" . $row['rassenid'] . "\" selected>" . $row['rassenname'] . "</option>";
        }
        else
        {
            $out .= "<option value=\"" . $row['rassenid'] . "\">" . $row['rassenname'] . "</option>";
        }

    }
    $out .= "</select>";
    return $out;
}

function get_buildings_tabless_name($name)
{
    global $db, $n, $userdata, $tpl;
    $result = $db->query("SELECT tabless FROM cc" . $n . "_buildings_option WHERE tabless='" . $name . "'");
    $row = $db->fetch_array($result);
    return $row['tabless'];
}
function get_soldiers_tabless_name($name)
{
    global $db, $n, $userdata, $tpl;
    $result = $db->query("SELECT tabless FROM cc" . $n . "_soldiers_option WHERE tabless='" . $name . "'");
    $row = $db->fetch_array($result);
    return $row['tabless'];
}

function get_explore_tabless_name($name)
{
    global $db, $n, $userdata, $tpl;
    $result = $db->query("SELECT tabless FROM cc" . $n . "_explore_option WHERE tabless='" . $name . "'");
    $row = $db->fetch_array($result);
    return $row['tabless'];


}

function make_build_option_choice($name, $sel_name)
{
    global $db, $n, $userdata, $tpl;
    global $op_set_n_res1;
    global $op_set_n_res2;
    global $op_set_n_res3;
    global $op_set_n_res4;

    $out = "<select name=\"$name\" class=\"combo\">";
    $out .= "<option value=\"0\">keine Optionen</option>";
    $result = $db->query("SELECT * FROM cc" . $n . "_buildings_option order by b_option_id");
    while ($row = $db->fetch_array($result))
    {
        $name_description = $row['description'];
        $name_description = str_replace("op_set_n_res1", $op_set_n_res1, $name_description);
        $name_description = str_replace("op_set_n_res2", $op_set_n_res2, $name_description);
        $name_description = str_replace("op_set_n_res3", $op_set_n_res3, $name_description);
        $name_description = str_replace("op_set_n_res4", $op_set_n_res4, $name_description);


        if (trim($sel_name) == trim($row['tabless']))
        {
            $out .= "<option value=\"" . $row['tabless'] . "\" selected>" . $name_description . "</option>";
        }
        else
        {
            $out .= "<option value=\"" . $row['tabless'] . "\">" . $name_description . "</option>";
        }

    }
    $out .= "</select>";
    return $out;
}

function if_spalte_exist($spaltenname, $tabelnnenname)
{
    global $db, $n, $userdata, $tpl;
    $Sql = "show columns from $tabelnnenname like '$spaltenname'";
    $result = $db->query($Sql);
    $result = $db->num_rows($result);
    return $result;
}

function compare_versions_sinus($local_version, $remote_version)
{
    // Copyright by sinus
    //0 = error
    //1= $local_version < $remote_version
    //2= $local_version = $remote_version
    //3= $local_version > $remote_version
    // Variablen definieren
    $aNewExpL = $aExpL = explode(".", $local_version);
    $aNewExpR = $aExpR = explode(".", $remote_version);
    $iExpL = count($aExpL);
    $iExpR = count($aExpR);
    $iMax = intval(max($iExpL, $iExpR));

    // Alle Paare durchlaufen, pr�fen und auff�llen
    for ($x = 0; $x != $iMax; $x++)
    {
        // Lokalen Versionseintrag
        if (isset($aExpL[$x]))
        {
            if (!is_numeric($aExpL[$x])) return 0;
        }
        else  $aNewExpL[] = 0;
        // Remote Versionseintrag
        if (isset($aExpR[$x]))
        {
            if (!is_numeric($aExpR[$x])) return 0;
        }
        else  $aNewExpR[] = 0;
    }

    // Versionsvergleich
    if (implode(".", $aNewExpL) != implode(".", $aNewExpR))
    {
        for ($x = 0; $x != $iMax; $x++)
        {
            if ($aNewExpL[$x] != $aNewExpR[$x])
            {
                if ($aNewExpL[$x] > $aNewExpR[$x]) return 3; // Lokal > Remote
                else  return 1; // Lokal < Remote
            }
        }
    }
    else  return 2; // Lokal = Remote
}


function get_user_right($forum_id)
{
    global $db, $n, $userdata;
    $ali_id = $userdata['allianzid'];

    $result_last = $db->query("SELECT alli_id  FROM cc" . $n . "_forum where si_forum_id ='$forum_id'");
    $row_last = $db->fetch_array($result_last);

    if ($row_last['alli_id'] == $ali_id)
    {
        return 1;
    }
    else
    {
        return 0;
    }
}

function get_forum_from_id($forum_id)
{
    global $db, $n, $userdata;
    $result_last = $db->query("SELECT si_forum_name FROM cc" . $n . "_forum where si_forum_id ='$forum_id'");
    $row_last = $db->fetch_array($result_last);
    return $row_last['si_forum_name'];
}
function get_topic_from_id($Topic_id)
{
    global $db, $n, $userdata;
    $result_last = $db->query("SELECT si_topic_title  FROM cc" . $n . "_forum_topics where si_topic_id  ='$Topic_id'");
    $row_last = $db->fetch_array($result_last);
    return $row_last['si_topic_title'];
}
function get_last_id_from_topic($forum_id, $topic_id)
{
    global $db, $n, $userdata;
    $result_last = $db->query("SELECT si_post_id  FROM cc" . $n . "_forum_posts where si_forum_id  ='$forum_id' and si_topic_id ='$topic_id' order by si_post_id DESC Limit 1");
    $row_last = $db->fetch_array($result_last);
    return $row_last['si_post_id'];
}

// **************************************************
//              neue nachrichten suchen
// **************************************************
//letzte ID_dieses Forums suchen
function get_last_post_id_forum($forum_id)
{
    global $db, $n, $userdata;
    $result_last = $db->query("SELECT si_post_id  FROM cc" . $n . "_forum_posts where si_forum_id  ='$forum_id' order by si_post_id DESC Limit 1");
    $row_last = $db->fetch_array($result_last);
    return intval($row_last['si_post_id']);
}
// letzte ID aus den angeschauten beiträgen suchen
function get_last_show_id_forum($forum_id)
{
    global $db, $n, $userdata;
    $uid = $userdata['userid'];
    $result_last = $db->query("SELECT post_id FROM cc" . $n . "_forum_last where forum_id ='$forum_id' and user_id=$uid order by post_id DESC Limit 1");
    $row_last = $db->fetch_array($result_last);
    return intval($row_last['post_id']);
}

function get_last_post_id_forum_topic($forum_id, $topic_id)
{
    global $db, $n, $userdata;
    $result_last = $db->query("SELECT si_post_id  FROM cc" . $n . "_forum_posts where si_forum_id  ='$forum_id' and si_topic_id ='$topic_id' order by si_post_id DESC Limit 1");
    $row_last = $db->fetch_array($result_last);
    return intval($row_last['si_post_id']);
}
// letzte ID aus den angeschauten beiträgen suchen
function get_last_show_id_forum_topic($forum_id, $topic_id)
{
    global $db, $n, $userdata;
    $uid = $userdata['userid'];
    $result_last = $db->query("SELECT post_id FROM cc" . $n . "_forum_last where forum_id ='$forum_id' and topic_id  ='$topic_id' and user_id=$uid order by post_id DESC Limit 1");
    $row_last = $db->fetch_array($result_last);
    return intval($row_last['post_id']);
}