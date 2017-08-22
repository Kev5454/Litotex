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
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (version_compare(phpversion(), '5.2.0') <= 0)
{
    echo 'Sie benötigen mindestens PHP 5.2.0\n um diese Engine nutzen zu können!';
    exit();
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

define('LITO_VERSION', '0.7.3');
define("LITO_ROOT_PATH", dirname(__file__) . DIRECTORY_SEPARATOR);
define("LITO_SETUP_FILE", LITO_ROOT_PATH . 'setup.php');
define("LITO_SETUP_PATH", LITO_ROOT_PATH . 'setup_tmp' . DIRECTORY_SEPARATOR);
define("LITO_GAME_ZIP_PATH", LITO_ROOT_PATH . 'game.zip');


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
            chmod($dir . DIRECTORY_SEPARATOR . $item, 0775);
            if (!removeDirectory($dir . DIRECTORY_SEPARATOR . $item, false))
            {
                return false;
            }
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


function _mkdir($directory, $public_access = true)
{
    $dirName = LITO_ROOT_PATH . $directory . (substr($directory, -1) == DIRECTORY_SEPARATOR ? '' : DIRECTORY_SEPARATOR);
    if (!is_dir($dirName))
    {
        mkdir($dirName);
    }
    chmod($dirName, 0777);

    if ($public_access == false && !file_exists($dirName . '.htaccess'))
    {
        $fileName = (file_exists(LITO_SETUP_PATH . 'includes' . DIRECTORY_SEPARATOR . '.htaccess') ? LITO_SETUP_PATH :
            LITO_ROOT_PATH) . 'includes' . DIRECTORY_SEPARATOR . '.htaccess';
        copy($fileName, $dirName . '.htaccess');
    }
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

$max_step = 8;
$filecounter = 0;


if ($step == 1)
{

    if (!is_file(LITO_GAME_ZIP_PATH))
    {
        echo ("Nicht alle für die Installation vorhandenen Dateien sind verfügbar. (0x0001)<br>Die Installation wurde abgebrochen.<br>Bitte wende dich an http://www.litotex.info");
        exit();
    }

    if (is_dir(LITO_SETUP_PATH))
    {
        removeDirectory(LITO_SETUP_PATH);
    }

    mkdir(LITO_SETUP_PATH);
    chmod(LITO_SETUP_PATH, 0777);
    if (!is_dir(LITO_SETUP_PATH))
    {
        echo ("Bitte lege einen Ordner '" . LITO_SETUP_PATH . "' an, welcher für die Installation Schreibrechte(0777) hat.");
        exit();
    }

    if (!is_writable(LITO_SETUP_PATH))
    {
        echo ("Bitte setzen Sie auf den Folgenden Ordner '" . LITO_SETUP_PATH . "' die Berechtigung auf 0777.");
        exit();
    }

    $zip = new ZipArchive();
    if ($zip->open(LITO_GAME_ZIP_PATH) === true)
    {
        $zip->extractTo(LITO_SETUP_PATH);
        $zip->close();
    }
    else
    {
        echo ('Es ist ein Fehler bei der Entpackung aufgetretten! (0x0002)<br>Die Installation wurde abgebrochen.<br>Bitte wende Sie sich an http://www.litotex.info');
        exit();
    }

    if (!is_file(LITO_SETUP_PATH . "setup/class_template.php"))
    {
        echo ("Setup konnte nicht geladen werden. (0x0003)<br>Die Installation wurde abgebrochen.<br>Bitte wende Sie sich an http://www.litotex.info");
        exit();
    }

    require (LITO_SETUP_PATH . 'setup/class_template.php');
    $tpl = new tpl(1);


    $over = "Litotex Setup -= Version:" . LITO_VERSION . " =-";
    $_SESSION['error_msg'] = "";
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

    $tpl->output('setup');
}
elseif ($step == 2)
{

    require (LITO_SETUP_PATH . 'setup/class_template.php');
    $tpl = new tpl(1);

    $over = "Litotex Setup -= Version:" . LITO_VERSION . " =-";
    $over_one = "<b>Herzlich Willkommen </b> Schritt " . $step . " von " . $max_step . " (Daten kopieren)";

    $_SESSION['error_msg'] = "";
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

    _mkdir('alli_flag');
    _mkdir('backup', false);
    _mkdir('battle_kr');
    _mkdir('cache', false);
    _mkdir('image_user');
    _mkdir('images_sig');
    _mkdir('images_tmp');
    _mkdir('templates_c', false);
    _mkdir('templates_c/standard');
    _mkdir('acp');
    _mkdir('acp/cache', false);
    _mkdir('acp/templates_c', false);
    _mkdir('acp/templates_c/standard');
    _mkdir('acp/tmp');

    foreach ($dirs as $directory)
    {
        $directory = str_replace(DIRECTORY_SEPARATOR . 'setup_tmp', '', $directory);
        if (file_exists($directory))
        {
            continue;
        }

        mkdir($directory);
        chmod($directory, 0755);
    }
    file_put_contents(LITO_ROOT_PATH . 'fileliste.json', json_encode($files));

    $tpl->output('setup');
}
elseif ($step == 3)
{

    require (LITO_SETUP_PATH . 'setup/class_template.php');

    $tpl = new tpl(1);

    $over = "Litotex Setup -= Version:" . LITO_VERSION . " =-";
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
    $serverID = "1";

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

    if (isset($_SESSION['serverID']))
    {
        $serverID = $_SESSION['serverID'];
    }
    $_SESSION['error_msg'] = "";

    $content .= "<span class=\"normalfont\">Alle Dateien wurden erfolgreich installiert.<br><br>";
    $content .= "<span class=\"normalfont\">Bitte trage hier die Verbindungsdaten zum MySQL Server ein,<br>";
    $content .= "<span class=\"normalfont\">um die Datenbank installieren zu können.<br><br><br>";
    $content .= "<table width=\"100%\" align=\"center\">";
    $content .= "<tr><td width=\"50%\"><span class=\"normalfont_o\">MySQL Server</span></td><td width=\"50%\"> <input name=\"sql_server\" type=\"text\" class=\"textinput\" value=\"$sql_server\" size=\"40\" maxlength=\"50\" /></td></tr>";
    $content .= "<tr><td><span class=\"normalfont_o\">MySQL Datenbank</span></td><td><input name=\"sql_db\" type=\"text\" class=\"textinput\" value=\"$sql_db\" size=\"10\" /></td></tr>";
    $content .= "<tr><td ><span class=\"normalfont_o\"> MySQL Username</span></td><td> <input name=\"sql_username\" type=\"text\" class=\"textinput\" value=\"$sql_user\" size=\"40\" /></td></tr>";
    $content .= "<tr><td><span class=\"normalfont_o\">MySQL Kennwort</span></td><td><input name=\"sql_kennw\" type=\"text\" class=\"textinput\" value=\"$sql_kennwo\" size=\"40\" /></td></tr>";
    $content .= "<tr><td><span class=\"normalfont_o\">MySQL Port</span></td><td><input name=\"sql_port\" type=\"text\" class=\"textinput\" value=\"$sql_port\" size=\"10\" /></td></tr>";
    $content .= "<tr><td><span class=\"normalfont_o\">Litotex ServerID</span></td><td><input name=\"serverID\" type=\"text\" class=\"textinput\" value=\"$serverID\" size=\"10\" /></td></tr>";
    $content .= "</table>";

    if ($error_msg != "")
    {
        $content .= "<br><br><span class=\"error\">Fehler:" . $error_msg . "</span>";
    }


    $action = "setup.php?step=4";
    $button = "<input type=\"submit\" class=\"buttons\" name=\"submit\" value=\"weiter\">";

    $tpl->output('setup');
}
elseif ($step == 4)
{
    require (LITO_SETUP_PATH . 'setup/class_template.php');
    require (LITO_ROOT_PATH . 'includes' . DIRECTORY_SEPARATOR . 'class_db_mysql.php');

    $_SESSION['error_msg'] = "";
    $sql_server = $_POST['sql_server'];
    $sql_user = $_POST['sql_username'];
    $sql_kennwo = $_POST['sql_kennw'];
    $sql_port = $_POST['sql_port'];
    $sql_db = $_POST['sql_db'];
    $serverID = $_POST['serverID'];

    if (empty($sql_user))
    {
        $_SESSION['error_msg'] = "Bitte SQL Username eintragen";
        header("LOCATION: setup.php?step=4");
        exit();
    }
    else
    {
        $_SESSION['sql_user'] = $sql_user;

    }

    if (empty($sql_server))
    {
        $_SESSION['error_msg'] = "Bitte Seervernamen eintragen";
        header("LOCATION: setup.php?step=4");
        exit();
    }
    else
    {
        $_SESSION['sql_server'] = $sql_server;
    }

    if (empty($sql_kennwo))
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
    if (empty($sql_db))
    {
        $_SESSION['error_msg'] = "Bitte eine Datenbank angeben.";
        header("LOCATION: setup.php?step=4");
        exit();
    }
    else
    {
        $_SESSION['sql_db'] = $sql_db;
    }

    if (empty($serverID))
    {
        $_SESSION['error_msg'] = "Bitte die Server-ID angeben.";
        header("LOCATION: setup.php?step=4");
        exit();
    }
    else
    {
        $_SESSION['serverID'] = $serverID;
    }


    $tpl = new tpl(1);


    $over = "Litotex Setup -= Version:" . LITO_VERSION . " =-";
    $over_one = "<b>Herzlich Willkommen </b> Schritt " . $step . " von " . $max_step . " (Datenbank installieren)";
    $content = "";

    $mysqli = new db($sql_server, $sql_user, $sql_kennwo, $sql_db, $sql_port, true);
    $result = $mysqli->connect();
    if ($result !== true)
    {
        $_SESSION['error_msg'] = "Es konnte keine Verbindung zum SQL Server hergestellt werden.<br>Error:" . $result;
        $mysqli->close();
        header("LOCATION: setup.php?step=3");
        exit();
    }
    $mysqli->unbuffered_query("SET character_set_client = 'utf8'");
    $mysqli->unbuffered_query("SET character_set_connection = 'utf8'");

    $count = 0;
    $lines = file(LITO_SETUP_PATH . 'setup/db_clean.sql');
    $toexec = '';

    foreach ($lines as $line)
    {
        if (substr($line, 0, 2) == '--' || $line == '') continue;

        $toexec .= $line;
        if (substr(trim($line), -1, 1) == ';')
        {
            $toexec = str_replace('{#SERVERID#}', $serverID, $toexec);
            if ($mysqli->unbuffered_query($toexec) !== true)
            {
                $_SESSION['error_msg'] = "Beim importieren ist ein Fehler aufgetreten<br>" . $toexec . "<br>" . $mysqli->error;
                header("LOCATION: setup.php?step=3");
                exit();
            }
            $toexec = '';
            $count++;
        }
    }

    $_SESSION['error_msg'] = "";
    $content .= "<span class=\"normalfont\">Die Verbindung zum SQL Server wurde erfolgreich hergestellt.<br><br><br></span>";
    $content .= "<span class=\"normalfont_o\">Es wurden " . $count .
        " Einträge in der Datenbank vorgenommen.<br><br></span>";
    $content .= "<span class=\"normalfont\">Die Litotex Datenbank wurde erfolgreich angelegt.<br><br>";
    $content .= "Im nächsten Schritt erfolgt das Installieren der Spieldateien.<br></span>";
    $button = "<input type=\"submit\" class=\"buttons\" name=\"submit\" value=\"weiter\">";
    $action = "setup.php?step=5";
    $button = "<input type=\"submit\" class=\"buttons\" name=\"submit\" value=\"weiter\">";

    $mysqli->close();
    $tpl->output('setup');
}
elseif ($step == 5)
{
    require (LITO_SETUP_PATH . 'setup/class_template.php');

    if (isset($_SESSION['error_msg']))
    {
        $error_msg = $_SESSION['error_msg'];
    }

    $tpl = new tpl(1);

    removeDirectory(LITO_ROOT_PATH . "setup");

    $game_path = LITO_ROOT_PATH;
    $current_url = 'http';
    if ($_SERVER["HTTPS"] == "on")
    {
        $current_url .= "s";
    }
    $current_url .= "://" . $_SERVER["SERVER_NAME"] . dirname($_SERVER['REQUEST_URI']) . DIRECTORY_SEPARATOR;

    $_SESSION['error_msg'] = "";
    $over = "Litotex Setup -= Version:" . LITO_VERSION . " =-";
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

    $tpl->output('setup');
}
elseif ($step == 6)
{
    require (LITO_SETUP_PATH . 'setup/class_template.php');

    $admin_name = filter_var($_POST['admin_name'], FILTER_SANITIZE_STRING);
    $admin_kwort = filter_var($_POST['admin_kwort'], FILTER_SANITIZE_STRING);


    $game_path = $_POST['game_path'];
    $game_url = $_POST['game_url'];

    if (empty($admin_name))
    {
        $_SESSION['error_msg'] = "Bitte einen Adminstratornamen angeben!";
        header("LOCATION: setup.php?step=5");
        exit();
    }
    else
    {
        $_SESSION['admin_name'] = $admin_name;
    }

    if (empty($admin_kwort))
    {
        $_SESSION['error_msg'] = "Bitte einen Adminstratorkennwort angeben!";
        header("LOCATION: setup.php?step=5");
        exit();
    }
    else
    {
        $_SESSION['admin_kwort'] = $admin_kwort;
    }

    if (empty($game_path))
    {
        $_SESSION['error_msg'] = "Bitte einen Gamepath angeben!";
        header("LOCATION: setup.php?step=5");
        exit();
    }
    else
    {
        $_SESSION['game_path'] = $game_path;
    }
    if (empty($game_url))
    {
        $_SESSION['error_msg'] = "Bitte eine Game URL angeben!";
        header("LOCATION: setup.php?step=5");
        exit();
    }
    else
    {
        $_SESSION['game_url'] = $game_url;
    }
    $serverID = $_SESSION['serverID'];

    if (substr($game_path, -(strlen(DIRECTORY_SEPARATOR))) != DIRECTORY_SEPARATOR)
    {
        $game_path = $game_path . DIRECTORY_SEPARATOR;
    }
    if (substr($game_url, -(strlen(DIRECTORY_SEPARATOR))) != DIRECTORY_SEPARATOR)
    {
        $game_url = $game_url . DIRECTORY_SEPARATOR;
    }


    $fp = fopen(LITO_ROOT_PATH . "includes/config.php", "w");
    $fw = fwrite($fp, "<?PHP
			    \$dbhost = \"" . $_SESSION['sql_server'] . "\";
			    \$dbuser = \"" . $_SESSION['sql_user'] . "\";
			    \$dbpassword = \"" . $_SESSION['sql_kennwo'] . "\";
			    \$dbbase = \"" . $_SESSION['sql_db'] . "\";
			    \$dbport = \"" . $_SESSION['sql_port'] . "\";
			    \$litotex_path = \"" . $game_path . "\";
			    \$litotex_url = \"" . $game_url . "\";
			    \$n = " . $serverID . ";");
    fclose($fp);

    $tpl = new tpl(1);
    $error_msg = $_SESSION['error_msg'];
    $_SESSION['error_msg'] = "";

    $over = "Litotex Setup -= Version:" . LITO_VERSION . " =-";
    $over_one = "<b>Herzlich Willkommen </b> Schritt " . $step . " von " . $max_step . " (Spieleinstellungen eingeben)";
    $content = "";
    $content .= "<span class=\"normalfont\">Bitte tragen Sie Ihre Spieledaten hier ein:<br><br></span>";
    $content .= "<table  width=\"400\" align=\"center\"><tr><td width=\"30%\"><span class=\"normalfont_o\">Game Name</span></td>";
    $content .= "<td width=\"70%\"><input name=\"game_name\" type=\"text\"  class=\"textinput\" value=\"\"></td>";
    $content .= "</tr><tr><td><span class=\"normalfont_o\">Game Auhtor</span></td>";
    $content .= "<td><input type=\"text\" name=\"game_author\" class=\"textinput\"></td>";

    $content .= "<tr><td width=\"30%\"><span class=\"normalfont_o\">Admin Email</span></td><td width=\"70%\"><input size=\"42\" name=\"admin_email\" type=\"text\" ></td></tr>";
    $content .= "<tr><td width=\"30%\"><span class=\"normalfont_o\">Support Email</span></td><td width=\"70%\"><input size=\"42\" name=\"support_email\" type=\"text\"></td></tr>";

    $content .= "</tr></table>";
    if ($error_msg != "")
    {
        $content .= "<br><br><span class=\"error\">Fehler:" . $error_msg . "</span>";
    }

    $button = "<input type=\"submit\" class=\"buttons\" name=\"submit\" value=\"weiter\">";
    $action = "setup.php?step=7";

    $tpl->output('setup');
}
elseif ($step == 7)
{
    require (LITO_SETUP_PATH . 'setup/class_template.php');
    require (LITO_ROOT_PATH . 'includes' . DIRECTORY_SEPARATOR . 'class_options.php');
    require (LITO_ROOT_PATH . 'includes' . DIRECTORY_SEPARATOR . 'class_db_mysql.php');

    $tpl = new tpl(1);

    $game_name = filter_var($_POST['game_name'], FILTER_SANITIZE_STRING);
    $game_author = filter_var($_POST['game_author'], FILTER_SANITIZE_STRING);
    $admin_email = filter_var($_POST['admin_email'], FILTER_SANITIZE_EMAIL);
    $support_email = filter_var($_POST['support_email'], FILTER_SANITIZE_EMAIL);

    if (empty($game_name))
    {
        $_SESSION['error_msg'] = "Bitte einen Game Namen angeben!";
        header("LOCATION: setup.php?step=6");
        exit();
    }

    if (empty($game_author))
    {
        $_SESSION['error_msg'] = "Bitte einen Author angeben!";
        header("LOCATION: setup.php?step=6");
        exit();
    }

    if (empty($admin_email))
    {
        $_SESSION['error_msg'] = "Bitte einen Admin E-mail Adresse angeben!";
        header("LOCATION: setup.php?step=6");
        exit();
    }

    if (empty($support_email))
    {
        $_SESSION['error_msg'] = "Bitte einen Support E-mail Adresse angeben!";
        header("LOCATION: setup.php?step=6");
        exit();
    }


    $sql_server = $_SESSION['sql_server'];
    $sql_user = $_SESSION['sql_user'];
    $sql_kennwo = $_SESSION['sql_kennwo'];
    $sql_db = $_SESSION['sql_db'];
    $sql_port = $_SESSION['sql_port'];
    $serverID = $_SESSION['serverID'];

    $admin_name = $_SESSION['admin_name'];
    $admin_kwort = $_SESSION['admin_kwort'];
    $game_path = $_SESSION['game_path'];
    $game_url = $_SESSION['game_url'];

    $mysqli = new db($sql_server, $sql_user, $sql_kennwo, $sql_db, $sql_port, true);
    $result = $mysqli->connect();
    if ($result !== true)
    {
        $_SESSION['error_msg'] = "Es konnte keine Verbindung zum SQL Server hergestellt werden.<br>Error:" . $result;
        $mysqli->close();
        header("LOCATION: setup.php?step=4");
        exit();
    }


    list($usec, $sec) = explode(' ', microtime());
    mt_srand((float)$sec + ((float)$usec * 100000));
    $op_api_key = md5(mt_rand());


    $result = $mysqli->query("SELECT * FROM cc" . $serverID . "_crand ORDER BY rand()");
    $land = $result->fetch_array();

    $md5_pw = md5($admin_kwort);
    $mysqli->query("INSERT INTO cc" . $serverID . "_users (username,email,password, serveradmin,register_date) VALUES ('" .
        $admin_name . "','','$md5_pw',  1,'" . time() . "')");
    $userid_r = $mysqli->insert_id();

    $mysqli->query("INSERT INTO cc" . $serverID .
        "_countries (res1,res2,res3,res4,userid,lastressources,picid,x,y,size) VALUES ('4000','4000','4000','4000','$userid_r','" .
        time() . "','1','$land[x]','$land[y]','500')");
    $islandid_r = $mysqli->insert_id();

    $sql = 'UPDATE cc' . $serverID . '_crand SET used=\'1\' WHERE x=\'' . $land['x'] . '\' AND y=\'' . $land['y'] . '\';';
    $sql .= 'UPDATE cc' . $serverID . '_users SET activeid=\'' . $islandid_r . '\' WHERE userid=\'' . $userid_r . '\';';
    $sql .= 'UPDATE `cc' . $serverID . '_buildings` SET `buildpic` = \'' . $game_url . 'images/standard/buildings/keins.png\';';
    $sql .= 'UPDATE `cc' . $serverID . '_soldiers` SET `solpic` = \'' . $game_url . 'images/standard/build_units/keins.png\';';
    $sql .= 'UPDATE `cc' . $serverID . '_explore` SET `explorePic` = \'' . $game_url . 'images/standard/exploring/keins.png\';';
    $sql .= 'UPDATE `cc' . $serverID . '_menu_admin_opt` SET `value` = \'' . $op_api_key . '\' WHERE `varname` = \'op_update_key\';';
    $sql .= 'UPDATE `cc' . $serverID . '_menu_admin_opt` SET `value` = \'' . $game_name . '\' WHERE `varname` = \'op_set_gamename\';';
    $sql .= 'UPDATE `cc' . $serverID . '_menu_admin_opt` SET `value` = \'' . $game_url . '\' WHERE `varname` = \'op_set_game_url\';';
    $sql .= 'UPDATE `cc' . $serverID . '_menu_admin_opt` SET `value` = \'' . $admin_email . '\' WHERE `varname` = \'op_admin_email\';';
    $sql .= 'UPDATE `cc' . $serverID . '_menu_admin_opt` SET `value` = \'' . $support_email . '\' WHERE `varname` = \'op_support_email\';';
    $sql .= 'UPDATE `cc' . $serverID . '_menu_admin_opt` SET `value` = \'' . $game_author . '\' WHERE `varname` = \'op_set_game_author\';';
    $mysqli->multi_query($sql);

    $option_w = new option(LITO_ROOT_PATH . 'options' . DIRECTORY_SEPARATOR);
    $option_w->writeByDB($mysqli, $serverID);

    $mysqli->close();

    $over = "Litotex Setup -= Version:" . LITO_VERSION . " =-";
    $over_one = "<b>Herzlich Willkommen </b> Schritt " . $step . " von " . $max_step . " (Ende)";

    $_SESSION['error_msg'] = "";
    $content = "";
    $content .= "<span class=\"normalfont_o\"><b>Herzlichen Glückwunsch</b><br><br></span>";
    $content .= "<span class=\"normalfont\">Die Installation ist fast abgeschlossen.<br>";
    $content .= "<br><br>";
    $content .= "Zum Login bitte <a href=\"" . $game_url . "index.php\" target=\"_blank\">HIER</a> klicken .<br>";
    $content .= "Das ACP (AdminControlCenter) befindet sich <a href=\"" . $game_url . "acp/index.php\" target=\"_blank\">HIER</a> ";
    $content .= ".<br>";
    $content .= "Hilfe und Erweiterungen gibt es in unserem <a href=\"http://www.litotex.info\" target=\"_blank\">FORUM</a>.</span> ";
    $content .= "<br><br>";
    $content .= "<span class=\"normalfont_o\">Viel Spass wünscht das Litotex Team.</span>";
    $content .= "<br><br>";
    $content .= "Bitte auf 'Abschließen' klicken umd die temporären Installationsdateien vom Server zu löschen.";

    $button = "<input type=\"submit\" class=\"buttons\" name=\"submit\" value=\"Abschließen\">";
    $action = "setup.php?step=8";

    $tpl->output('setup');
}
elseif ($step == 8)
{
    session_destroy();
    removeDirectory(LITO_SETUP_PATH);
    if (file_exists(LITO_GAME_ZIP_PATH))
    {
        unlink(LITO_GAME_ZIP_PATH);
    }
    if (file_exists(LITO_SETUP_FILE))
    {
        unlink(LITO_SETUP_FILE);
    }
    echo ("Die tempor&auml;re Installationsdateien wurden vom Server gel&ouml;scht.");
    exit();
}
