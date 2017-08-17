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
}
if (version_compare(phpversion(), '7.0.0') >= 0)
{
    define('PHP7', true);
}

if (!function_exists('session_unregister'))
{
    function session_unregister($name)
    {
        unset($_SESSION[$name]);
    }
}

if (!defined('DIRECTORY_SEPARATOR'))
{
    define('DIRECTORY_SEPARATOR', '/');
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


session_start();


$def_folder = time();
mkdir($def_folder);
if (!file_exists($def_folder))
{
    echo ("Das Setup hat keine Schreibrechte im aktuellen Ordner und muss daher abgebrochen werden.<br>Eventuell ist Safe_Mode aktiviert.");
    exit();
}
else
{
    rmdir($def_folder);
}

$step = (isset($_REQUEST['step']) ? filter_var($_REQUEST['step'], FILTER_SANITIZE_NUMBER_INT, array('options' => array('default' =>
            1))) : 1);

if (isset($_REQUEST['nounpack']))
{
    $_SESSION['no_unpack'] = 1;
}
else
{
    if (!isset($_SESSION['no_unpack']))
    {
        $_SESSION['no_unpack'] = 0;
    }
}

$lito_version = "0.7.2";
$max_step = 7;
$filecounter = 0;

define("LITO_ROOT_PATH", dirname(__file__) . DIRECTORY_SEPARATOR);
define("LITO_SETUP_PATH", LITO_ROOT_PATH . 'setup_tmp' . DIRECTORY_SEPARATOR);
define("LITO_GAME_ZIP_PATH", LITO_ROOT_PATH . 'game.zip');


if ($step == 1)
{

    if ($_SESSION['no_unpack'] == 0)
    {
        if (!is_file(LITO_GAME_ZIP_PATH))
        {
            echo ("Nicht alle für die Installation vorhandenen Dateien sind verfügbar. (0x0001)<br>Die Installation wurde abgebrochen.<br>Bitte wende dich an http://www.litotex.info");
            exit();
        }
    }

    if (!is_dir(LITO_SETUP_PATH))
    {

        mkdir(LITO_SETUP_PATH);
        chmod(LITO_SETUP_PATH, 0777);
        if (!is_dir(LITO_SETUP_PATH))
        {
            echo ("Bitte lege einen Ordner '" . LITO_SETUP_PATH . "' an, welcher für die Installation Schreibrechte hat (777).");
            exit();
        }
    }

    if (!is_writable(LITO_SETUP_PATH))
    {
        echo ("Bitte setzen Sie auf den Folgenden Ordner '" . LITO_SETUP_PATH . "' die Berechtigung auf 0777.");
        exit();
    }


    if ($_SESSION['no_unpack'] == 0)
    {
        removeDirectory(LITO_SETUP_PATH);

        $zip = new ZipArchive();
        if ($zip->open(LITO_GAME_ZIP_PATH) === true)
        {
            $zip->extractTo(LITO_SETUP_PATH);
            $zip->close();
            $_SESSION['no_unpack'] = 1;
        }
        else
        {
            echo ('Es ist ein Fehler bei der Entpackung aufgetretten! (0x0002)<br>Die Installation wurde abgebrochen.<br>Bitte wende Sie sich an http://www.litotex.info');
            exit();
        }
    }

    if (!is_file(LITO_SETUP_PATH . "setup/class_template.php"))
    {
        echo ("Setup konnte nicht geladen werden. (0x0003)<br>Die Installation wurde abgebrochen.<br>Bitte wende Sie sich an http://www.litotex.info");
        exit();
    }

    require (LITO_SETUP_PATH . 'setup/class_template.php');
    $tpl = new tpl(1, 1);


    $over = "Litotex Setup -= Version:" . $lito_version . " =-";
    $content = "<span class=\"normalfont\">Dieses Setup installiert die <br>Litotex Browsergameengine auf ihrem Rechner.<br><br>Bitte wenden Sie sich im Falle von Fragen und Anregungen an unser <a href=\"http://litotex.info\" target=\"_blank\">Litotex Forum</a></span> ";
    $content .= "<br><br>";
    $content .= "<span class=\"normalfont\">Litotex ist eine OpenSource Software und steht unter der GPL.<br></span> ";
    $content .= "<span class=\"normalfont\">Den genauen Wortlaut können sie der Datei LICENSE.TXT entnehmen. Eine deutsche Übersetzung ist <a href=\"http://www.gnu.de/documents/gpl.de.html\" target=\"_blank\">hier </a>einsehbar.  <br><br></span> ";
    $content .= "<span class=\"normalfont\">Mit der Installation dieser Software erklären Sie sich mit den Lizenzbestimmungen der GPL sowie nachfolgenden Pukten, einverstanden.</span><br>";

    $content .= "<span class=\"smallfont\">*)den Urheberrechtshinweis im Footer nicht zu entfernen, durch andere technische Möglichkeiten auszublenden oder unsichtbar zu machen.<br>";
    $content .= "*)den Urheberrechtshinweis in allen Templates, in der Form der von Litotex ausgelieferten Layoutstrukturierung anzuzeigen.<br></span> ";
    $content .= "<br><br><span class=\"normalfont_o\">Für Fragen stehen wir im Forum jederzeit zur Verfügung.</span> ";
    $over_one = "<b>Herzlich Willkommen </b> Schritt " . $step . " von " . $max_step . " (Einleitung)";

    $action = "setup.php?step=2";
    $button = "<input type=\"submit\" class=\"buttons\" name=\"submit\" value=\"weiter\">";

    session_unregister('error_msg');

    eval("\$tpl->output(\"" . $tpl->get("setup") . "\");");
    exit();
}
elseif ($step == 2)
{

    require (LITO_SETUP_PATH . 'setup/class_template.php');
    $tpl = new tpl(1, 1);

    $_SESSION['error_msg'] = "";

    $over = "Litotex Setup -= Version:" . $lito_version . " =-";
    $over_one = "<b>Herzlich Willkommen </b> Schritt " . $step . " von " . $max_step . " (Daten kopieren)";

    $content = "";

    $content .= "<span class=\"normalfont\">Ordner Test war erfolgreich.<br>";
    $content .= "Die Installation kann fortgesetzt werden.<br>";
    $content .= "<br><br>";
    $content .= "Im nächsten Schritt werden die Dateien kopiert.<br>Das Kopieren kann einige Zeit in Anspruch nehmen.</span><br><br><br>";
    $content .= "<table border=\"0\" width=\"100%\"><tr><td >";

    $content .= "<div align=\"center\"><div class=\"normalfont_o\" id =\"resp_id\">kopieren der Dateien</div></div>";

    $content .= "</td></tr><tr> <td><div align=\"center\">";

    $content .= "<div class =\"barContainer\" id=\"barContainer\">";
    $content .= "<div class =\"progressBar\" id=\"progressBar\"></div>";
    $content .= "<div class=\"progress_text\">";
    $content .= "<div class =\"percent\" id=\"percent\"></div>";
    $content .= "</div>";
    $content .= "</div>";
    $content .= "</div></td></tr></table>";


    $action = "setup.php?step=3";
    $action = "";
    $button = "<input type=\"submit\" id=\"submit\"  class=\"buttons\" onclick=\"startinstall();return false;\" value=\"weiter\">";


    $dirs = array();
    $files = array();
    $all = scanDirectory(LITO_SETUP_PATH);

    foreach ($all as $path)
    {
        if (is_dir($path))
        {
            $dirs[] = $path;
        }
        else
        {
            $files[] = $path;
        }
    }
    $filecounter = count($files);

    file_put_contents(LITO_ROOT_PATH . 'dirliste.json', json_encode($dirs));
    file_put_contents(LITO_ROOT_PATH . 'fileliste.json', json_encode($files));

    $_SESSION['error_msg'] = "";
    eval("\$tpl->output(\"" . $tpl->get("setup") . "\");");
    exit();

}
elseif ($step == 3)
{

    require (LITO_SETUP_PATH . 'setup/class_template.php');

    $tpl = new tpl(1, 1);

    $over = "Litotex Setup -= Version:" . $lito_version . " =-";
    $over_one = "<b>Herzlich Willkommen </b> Schritt " . $step . " von " . $max_step . " (Konfiguration Datenbank)";
    $content = "";

    if (is_file(LITO_ROOT_PATH . "dirliste.json"))
    {
        unlink(LITO_ROOT_PATH . "dirliste.json");
    }
    if (is_file(LITO_ROOT_PATH . "fileliste.json"))
    {
        unlink(LITO_ROOT_PATH . "fileliste.json");
    }

    $error_msg = "";
    $sql_server = "localhost";
    $sql_user = "";
    $sql_kennwo = "";
    $sql_port = "3306";
    $sql_db = "lito";

    if (isset($_SESSION['error_msg']))
    {
        $error_msg = $_SESSION['error_msg'];
    }

    if (isset($_SESSION['sql_server']))
    {
        $sql_server = $_SESSION['sql_server'];
    }
    if (isset($_SESSION['sql_user']))
    {
        $sql_user = $_SESSION['sql_user'];
    }

    if (isset($_SESSION['sql_kennwo']))
    {
        $sql_kennwo = $_SESSION['sql_kennwo'];
    }

    if (isset($_SESSION['sql_port']))
    {
        $sql_port = $_SESSION['sql_port'];
    }

    if (isset($_SESSION['sql_db']))
    {
        $sql_db = $_SESSION['sql_db'];
    }

    $content .= "<span class=\"normalfont\">Alle Dateien wurden erfolgreich installiert.<br><br>";
    $content .= "<span class=\"normalfont\">Bitte trage hier die Verbindungsdaten zum MySQL Server ein,<br>";
    $content .= "<span class=\"normalfont\">um die Datenbank installieren zu können.<br><br><br>";
    $content .= "<table width=\"100%\" align=\"center\">";
    $content .= "<tr><td width=\"50%\"><span class=\"normalfont_o\">MySQL Server</span></td><td width=\"50%\"> <input name=\"sql_server\" type=\"text\" class=\"textinput\" value=\"$sql_server\" size=\"40\" maxlength=\"50\" /></td></tr>";
    $content .= "<tr><td><span class=\"normalfont_o\">MySQL Datenbank</span></td><td><input name=\"sql_db\" type=\"text\" class=\"textinput\" value=\"$sql_db\" size=\"10\" /></td></tr>";
    $content .= "<tr><td ><span class=\"normalfont_o\"> MySQL Username</span></td><td> <input name=\"sql_username\" type=\"text\" class=\"textinput\" value=\"$sql_user\" size=\"40\" /></td></tr>";
    $content .= "<tr><td><span class=\"normalfont_o\">MySQL Kennwort</span></td><td><input name=\"sql_kennw\" type=\"text\" class=\"textinput\" value=\"$sql_kennwo\" size=\"40\" /></td></tr>";
    $content .= "<tr><td><span class=\"normalfont_o\">MySQL Port</span></td><td><input name=\"sql_port\" type=\"text\" class=\"textinput\" value=\"$sql_port\" size=\"10\" /></td></tr>";
    $content .= "</table>";

    if ($error_msg != "")
    {
        $content .= "<br><br><span class=\"error\">Fehler:" . $error_msg . "</span>";
    }


    $action = "setup.php?step=4";
    $button = "<input type=\"submit\" class=\"buttons\" name=\"submit\" value=\"weiter\">";


    eval("\$tpl->output(\"" . $tpl->get("setup") . "\");");
    exit();

}
elseif ($step == 4)
{
    require (LITO_SETUP_PATH . 'setup/class_template.php');

    $_SESSION['error_msg'] = "";
    $sql_server = $_POST['sql_server'];
    $sql_user = $_POST['sql_username'];
    $sql_kennwo = $_POST['sql_kennw'];
    $sql_port = $_POST['sql_port'];
    $sql_db = $_POST['sql_db'];

    if ($sql_user == "")
    {
        $_SESSION['error_msg'] = "Bitte SQL Username eintragen";
        header("LOCATION: setup.php?step=4");
        exit();
    }
    else
    {
        $_SESSION['sql_user'] = $sql_user;

    }

    if ($sql_server == "")
    {
        $_SESSION['error_msg'] = "Bitte Seervernamen eintragen";
        header("LOCATION: setup.php?step=4");
        exit();
    }
    else
    {
        $_SESSION['sql_server'] = $sql_server;
    }

    if ($sql_kennwo == "")
    {
        $_SESSION['error_msg'] = "Bitte Kennwort eintragen";
        header("LOCATION: setup.php?step=4");
        exit();
    }
    else
    {
        $_SESSION['sql_kennwo'] = $sql_kennwo;
    }

    if ($sql_port == "")
    {
        $_SESSION['error_msg'] = "Bitte Serverport eintragen";
        header("LOCATION: setup.php?step=4");
        exit();
    }
    else
    {
        $_SESSION['sql_port'] = $sql_port;
    }
    if ($sql_db == "")
    {
        $_SESSION['error_msg'] = "Bitte eine Datenbank angeben.";
        header("LOCATION: setup.php?step=4");
        exit();
    }
    else
    {
        $_SESSION['sql_db'] = $sql_db;
    }


    $tpl = new tpl(1, 1);


    $over = "Litotex Setup -= Version:" . $lito_version . " =-";
    $over_one = "<b>Herzlich Willkommen </b> Schritt " . $step . " von " . $max_step . " (Datenbank installieren)";
    $content = "";


    $mysqli = new mysqli($sql_server, $sql_user, $sql_kennwo, $sql_db, $sql_port);
    if ($mysqli->connect_error)
    {
        $_SESSION['error_msg'] = "Es konnte keine Verbindung zum SQL Server hergestellt werden.<br>Error:" . $mysqli->
            connect_error;
        $mysqli->close();
        header("LOCATION: setup.php?step=4");
        exit();
    }

    $count = 0;
    $sqlfile = file_get_contents(LITO_SETUP_PATH . 'setup/db_clean.sql');
    $mysqli->multi_query($sqlfile);

    $_SESSION['error_msg'] = "";
    $content .= "<span class=\"normalfont\">Die Verbindung zum SQL Server wurde erfolgreich hergestellt.<br><br><br></span>";
    $content .= "<span class=\"normalfont_o\">Es wurden " . $count .
        " Einträge in der Datenbank vorgenommen.<br><br></span>";
    $content .= "<span class=\"normalfont\">Die Litotex Datenbank wurde erfolgreich angelegt.<br><br>";
    $content .= "<span class=\"normalfont\">Bitte warten sie noch 60 sekunden bevor sie auf weiter drücken! Da ein Multi-Query mal ein wenig länger dauern kann!!!<br><br>";
    $content .= "Im nächsten Schritt erfolgt das Installieren der Spieldateien.<br></span>";
    $button = "<input type=\"submit\" class=\"buttons\" name=\"submit\" value=\"weiter\">";
    $action = "setup.php?step=5";
    $button = "<input type=\"submit\" class=\"buttons\" name=\"submit\" value=\"weiter\">";

    $mysqli->close();
    eval("\$tpl->output(\"" . $tpl->get("setup") . "\");");
    exit();
}
elseif ($step == 5)
{
    require (LITO_SETUP_PATH . 'setup/class_template.php');

    if (isset($_SESSION['error_msg']))
    {
        $error_msg = $_SESSION['error_msg'];
    }

    $tpl = new tpl(1, 1);

    chmod(LITO_ROOT_PATH . "options/options.php", 0777);

    removeDirectory(LITO_ROOT_PATH . "setup");

    $game_path = LITO_ROOT_PATH;
    $current_url = 'http';
    if ($_SERVER["HTTPS"] == "on")
    {
        $current_url .= "s";
    }
    $current_url .= "://" . $_SERVER["SERVER_NAME"] . dirname($_SERVER['REQUEST_URI']);


    $over = "Litotex Setup -= Version:" . $lito_version . " =-";
    $over_one = "<b>Herzlich Willkommen </b> Schritt " . $step . " von " . $max_step . " (Spieleinstellungen eingeben)";
    $content = "";
    $content .= "<span class=\"normalfont\">Bitte tragen Sie Ihre Administrationsdaten hier ein:<br><br></span>";
    $content .= "<table  width=\"400\" align=\"center\"><tr><td width=\"30%\"><span class=\"normalfont_o\">Admin Name</span></td>";
    $content .= "<td width=\"70%\"><input name=\"admin_name\" type=\"text\"  class=\"textinput\" value=\"\"></td>";
    $content .= "</tr><tr><td><span class=\"normalfont_o\">Admin Kennwort</span></td>";
    $content .= "<td><input type=\"text\" name=\"admin_kwort\" class=\"textinput\"></td>";

    $content .= "<tr><td width=\"30%\"><span class=\"normalfont_o\">Game URL</span></td><td width=\"70%\"><input size=\"42\" name=\"game_url\" type=\"text\"   value=\"$current_url\"></td></tr>";
    $content .= "<tr><td width=\"30%\"><span class=\"normalfont_o\">absoluter Path</span></td><td width=\"70%\"><input size=\"42\" name=\"game_path\" type=\"text\" value=\"" .
        $game_path . "\"></td></tr>";

    $content .= "</tr></table>";
    $content .= "<br><br>";
    $content .= "Als Game URL bitte die komplette Adresse (incl. http://www.) eingeben, unter welche das Spiel und die Installation zu erreichen ist.<br>";
    $content .= "Als absoluter Path bitte den kompletten Path, unter welchen das Spiel installiert ist, eingeben.<br>";
    $content .= "<span class=\"normalfont_o\">Die hier automatisch eingetragenen Werte sind automatisch ermittelt, und können daher abweichen.</span>";
    if ($error_msg != "")
    {
        $content .= "<br><br><span class=\"error\">Fehler:" . $error_msg . "</span>";
    }

    $button = "<input type=\"submit\" class=\"buttons\" name=\"submit\" value=\"weiter\">";
    $action = "setup.php?step=6";

    eval("\$tpl->output(\"" . $tpl->get("setup") . "\");");
    exit();
}
elseif ($step == 6)
{
    require (LITO_SETUP_PATH . 'setup/class_template.php');

    $tpl = new tpl(1, 1);
    $_SESSION['error_msg'] = "";

    $admin_name = filter_var($_POST['admin_name'], FILTER_SANITIZE_STRING);
    $admin_kwort = filter_var($_POST['admin_kwort'], FILTER_SANITIZE_STRING);


    $game_path = $_POST['game_path'];
    $game_url = $_POST['game_url'];

    if ($admin_name == "")
    {
        $_SESSION['error_msg'] = "Bitte einen Adminstratornamen angeben!";
        header("LOCATION: setup.php?step=6");
        exit();
    }
    if ($admin_kwort == "")
    {
        $_SESSION['error_msg'] = "Bitte einen Adminstratorkennwort angeben!";
        header("LOCATION: setup.php?step=6");
        exit();
    }
    if ($game_path == "")
    {
        $_SESSION['error_msg'] = "Bitte einen Gamepath angeben!";
        header("LOCATION: setup.php?step=6");
        exit();
    }
    if ($game_url == "")
    {
        $_SESSION['error_msg'] = "Bitte eine Game URL angeben!";
        header("LOCATION: setup.php?step=6");
        exit();
    }

    $option_f_name = LITO_ROOT_PATH . "includes/config.php";

    if (substr($game_path, -(strlen(DIRECTORY_SEPARATOR))) != DIRECTORY_SEPARATOR)
    {
        $game_path = $game_path . DIRECTORY_SEPARATOR;
    }
    if (substr($game_url, -(strlen(DIRECTORY_SEPARATOR))) != DIRECTORY_SEPARATOR)
    {
        $game_url = $game_url . DIRECTORY_SEPARATOR;
    }


    $fp = fopen($option_f_name, "w");
    $fw = fwrite($fp, "<?PHP
			    \$dbhost = \"" . $_SESSION['sql_server'] . "\";
			    \$dbuser = \"" . $_SESSION['sql_user'] . "\";
			    \$dbpassword = \"" . $_SESSION['sql_kennwo'] . "\";
			    \$dbbase = \"" . $_SESSION['sql_db'] . "\";
			    \$dbport = \"" . $_SESSION['sql_port'] . "\";
			    \$litotex_path = \"" . $game_path . "\";
			    \$litotex_url = \"" . $game_url . "\";
			    \$n = 1;");
    fclose($fp);


    $sql_server = $_SESSION['sql_server'];
    $sql_user = $_SESSION['sql_user'];
    $sql_kennwo = $_SESSION['sql_kennwo'];
    $sql_db = $_SESSION['sql_db'];
    $sql_port = $_SESSION['sql_port'];

    $mysqli = new mysqli($sql_server, $sql_user, $sql_kennwo, $sql_db, $sql_port);
    if ($mysqli->connect_error)
    {
        $_SESSION['error_msg'] = "Es konnte keine Verbindung zum SQL Server hergestellt werden.<br>Error:" . $mysqli->
            connect_error;
        $mysqli->close();
        header("LOCATION: setup.php?step=4");
        exit();
    }

    $n = 1;


    list($usec, $sec) = explode(' ', microtime());
    mt_srand((float)$sec + ((float)$usec * 100000));
    $op_api_key = md5(mt_rand());

    $n = 1;
    $mysqli->query('UPDATE `cc' . $n . '_menu_admin_opt` SET `value` = \'' . $op_api_key . '\' WHERE `varname` = \'op_update_key\';');


    $result = $mysqli->query("SELECT * FROM cc" . $n . "_crand ORDER BY rand()");
    $land = $result->fetch_assoc();

    $md5_pw = md5($admin_kwort);
    $mysqli->query("INSERT INTO cc" . $n . "_users (username,email,password, serveradmin,register_date) VALUES ('" . $admin_name .
        "','','$md5_pw',  1,'" . time() . "')");
    $userid_r = $mysqli->insert_id;

    $mysqli->query("INSERT INTO cc" . $n .
        "_countries (res1,res2,res3,res4,userid,lastressources,picid,x,y,size) VALUES ('4000','4000','4000','4000','$userid_r','" .
        time() . "','1','$land[x]','$land[y]','500')");
    $islandid_r = $mysqli->insert_id;

    $mysqli->query("UPDATE cc" . $n . "_crand SET used='1' WHERE x='" . $land['x'] . "' AND y='" . $land['y'] . "'");

    $mysqli->query("UPDATE cc" . $n . "_users SET activeid='$islandid_r' WHERE userid='$userid_r'");
    $mysqli->query('UPDATE `cc' . $n . '_buildings` SET `buildpic` = \'' . $game_url . 'images/standard/buildings/keins.png\'');
    $mysqli->query('UPDATE `cc' . $n . '_soldiers` SET `solpic` = \'' . $game_url . 'images/standard/build_units/keins.png\'');
    $mysqli->query('UPDATE `cc' . $n . '_explore` SET `explorePic` = \'' . $game_url . 'images/standard/exploring/keins.png\'');
    $mysqli->close();

    $over = "Litotex Setup -= Version:" . $lito_version . " =-";
    $over_one = "<b>Herzlich Willkommen </b> Schritt " . $step . " von " . $max_step . " (Ende)";
    $content = "";

    $content .= "<span class=\"normalfont_o\"><b>Herzlichen Glückwunsch</b><br><br></span>";
    $content .= "<span class=\"normalfont\">Die Installation ist fast abgeschlossen.<br>";
    $content .= "<br><br>";
    $content .= "Zum Login bitte <a href=\"" . $game_url . "index.php\" target=\"_blank\">HIER</a> klicken .<br>";
    $content .= "Das ACP (AdminControlCenter) befindet sich <a href=\"" . $game_url . "acp/index.php\" target=\"_blank\">HIER</a> ";
    $content .= ".<br>";
    $content .= "Hilfe und Erweiterungen gibt es in unserem <a href=\"http://www.litotex.info\" target=\"_blank\">FORUM</a>.</span> ";
    $content .= "<br><br>";
    $content .= "<span class=\"normalfont_o\"><Viel Spass wünscht das Litotex Team.</span>";
    $content .= "<br><br>";
    $content .= "Bitte auf 'Abschließen' klicken umd die temporären Installationsdateien vom Server zu löschen.";
    $button = "<input type=\"submit\" class=\"buttons\" name=\"submit\" value=\"Abschließen\">";
    $action = "setup.php?step=7";
    eval("\$tpl->output(\"" . $tpl->get("setup") . "\");");
    exit();

}
elseif ($step == 7)
{
    session_destroy();
    removeDirectory(LITO_SETUP_PATH);
    if (file_exists(LITO_GAME_ZIP_PATH))
    {
        unlink(LITO_GAME_ZIP_PATH);
    }
    if (file_exists(LITO_ROOT_PATH . 'setup.php'))
    {
        unlink(LITO_ROOT_PATH . 'setup.php');
    }
    echo ("Die temporäre Installationsdateien wurden vom Server gelöscht.");
    exit();
}
