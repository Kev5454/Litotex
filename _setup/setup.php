<?php

function startsWith($string, $searchString)
{
    // search backwards starting from haystack length characters from the end
    return $searchString === "" || strrpos($string, $searchString, -strlen($string)) !== false;
}

function endsWith($string, $searchString)
{
    // search forward starting from end minus needle length characters
    return $searchString === "" || (($temp = strlen($string) - strlen($searchString)) >= 0 && strpos($string, $searchString,
        $temp) !== false);
}

function startsWithArray($string, array $searchString)
{
    $return = array();
    foreach ($searchString as $search)
    {
        $return[$search] = ($search === "" || strrpos($string, $search, -strlen($string)) !== false);
    }
    return $return;
}

function endsWithArray($string, array $searchString)
{
    $return = array();
    foreach ($searchString as $search)
    {
        $return[$search] = ($search === "" || (($temp = strlen($string) - strlen($search)) >= 0 && strpos($string, $search, $temp)
            !== false));
    }
    return $return;
}

/**
 * Copy a file, or recursively copy a folder and its contents
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.0.1
 * @link        http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
 * @param       string   $source    Source path
 * @param       string   $dest      Destination path
 * @return      bool     Returns TRUE on success, FALSE on failure
 */
function copyr($source, $dest)
{
    // Simple copy for a file
    if (is_file($source))
    {
        return copy($source, $dest);
    }

    // Make destination directory
    if (!is_dir($dest))
    {
        mkdir($dest);
    }

    // Loop through the folder
    $dir = dir($source);
    while (false !== $entry = $dir->read())
    {
        // Skip pointers
        if ($entry == '.' || $entry == '..')
        {
            continue;
        }

        // Deep copy directories
        copyr("$source/$entry", "$dest/$entry");
    }

    // Clean up
    $dir->close();
    return true;
}

function _rmdir($src, $recursiv = true)
{
    if (endsWith($src, '/'))
    {
        $src = substr($src, 0, -1);
    }
    foreach (scandir($src) as $item)
    {
        if ($item == '.' || $item == '..')
        {
            continue;
        }
        $full = $src . '/' . $item;

        if (is_file($full) || is_link($full))
        {
            chmod($full, 0777);
            unlink($full);
        }
        elseif (is_dir($full))
        {
            if (!_rmdir($full))
            {
                chmod($full, 0777);
                if (!_rmdir($full))
                {
                    return false;
                }
            }
        }
    }
    return rmdir($src);
}

function _scandir($src, $_arrayFiles = array())
{
    if (endsWith($src, '/'))
    {
        $src = substr($src, 0, -1);
    }

    foreach (scandir($src) as $item)
    {
        if ($item == '.' || $item == '..')
        {
            continue;
        }
        $full = $src . '/' . $item;

        if (is_file($full) || is_link($full))
        {
            $_arrayFiles[] = $full;
        }
        elseif (is_dir($full))
        {
            $_arrayFiles = _scandir($full, $_arrayFiles);
        }
    }
    return $_arrayFiles;
}

function getBaseUrl()
{
    // output: /myproject/index.php
    $currentPath = $_SERVER['PHP_SELF'];

    // output: Array ( [dirname] => /myproject [basename] => index.php [extension] => php [filename] => index )
    $pathInfo = pathinfo($currentPath);

    // output: localhost
    $hostName = $_SERVER['HTTP_HOST'];

    // output: http://
    $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://');

    // return: http://localhost/myproject/
    if (substr($pathInfo['dirname'], -1) != '/')
    {
        $pathInfo['dirname'] = $pathInfo['dirname'] . '/';
    }
    return $protocol . $hostName . $pathInfo['dirname'];
}


/*
* Find files in a directory matching a pattern
*
*
* Paul Gregg <pgregg@pgregg.com>
* 20 March 2004,  Updated 20 April 2004
* Updated 18 April 2007 to add the ability to sort the result set
* Updated 9 June 2007 to prevent multiple calls to sort during recursion
* Updated 12 June 2009 to allow for sorting by extension and prevent following
* symlinks by default
* Version: 2.3
* This function is backwards capatible with any code written for a
* previous version of preg_find()
*
* Open Source Code:   If you use this code on your site for public
* access (i.e. on the Internet) then you must attribute the author and
* source web site: http://www.pgregg.com/projects/php/preg_find/preg_find.phps
* Working examples: http://www.pgregg.com/projects/php/preg_find/
*
*/

define('PREG_FIND_RECURSIVE', 1);
define('PREG_FIND_DIRMATCH', 2);
define('PREG_FIND_FULLPATH', 4);
define('PREG_FIND_NEGATE', 8);
define('PREG_FIND_DIRONLY', 16);
define('PREG_FIND_RETURNASSOC', 32);
define('PREG_FIND_SORTDESC', 64);
define('PREG_FIND_SORTKEYS', 128);
define('PREG_FIND_SORTBASENAME', 256); # requires PREG_FIND_RETURNASSOC
define('PREG_FIND_SORTMODIFIED', 512); # requires PREG_FIND_RETURNASSOC
define('PREG_FIND_SORTFILESIZE', 1024); # requires PREG_FIND_RETURNASSOC
define('PREG_FIND_SORTDISKUSAGE', 2048); # requires PREG_FIND_RETURNASSOC
define('PREG_FIND_SORTEXTENSION', 4096); # requires PREG_FIND_RETURNASSOC
define('PREG_FIND_FOLLOWSYMLINKS', 8192);

// PREG_FIND_RECURSIVE   - go into subdirectorys looking for more files
// PREG_FIND_DIRMATCH    - return directorys that match the pattern also
// PREG_FIND_DIRONLY     - return only directorys that match the pattern (no files)
// PREG_FIND_FULLPATH    - search for the pattern in the full path (dir+file)
// PREG_FIND_NEGATE      - return files that don't match the pattern
// PREG_FIND_RETURNASSOC - Instead of just returning a plain array of matches,
//                         return an associative array with file stats
// PREG_FIND_FOLLOWSYMLINKS - Recursive searches (from v2.3) will no longer
//                            traverse symlinks to directories, unless you
//                            specify this flag. This is to prevent nasty
//                            endless loops.
//
// You can also request to have the results sorted based on various criteria
// By default if any sorting is done, it will be sorted in ascending order.
// You can reverse this via use of:
// PREG_FIND_SORTDESC    - Reverse order of sort
// PREG_FILE_SORTKEYS    - Sort on the keyvalues or non-assoc array results
// The following sorts *require* PREG_FIND_RETURNASSOC to be used as they are
// sorting on values stored in the constructed associative array
// PREG_FIND_SORTBASENAME - Sort the results in alphabetical order on filename
// PREG_FIND_SORTMODIFIED - Sort the results in last modified timestamp order
// PREG_FIND_SORTFILESIZE  - Sort the results based on filesize
// PREG_FILE_SORTDISKUSAGE - Sort based on the amount of disk space taken
// PREG_FIND_SORTEXTENSION - Sort based on the filename extension
// to use more than one simply seperate them with a | character


// Search for files matching $pattern in $start_dir.
// if args contains PREG_FIND_RECURSIVE then do a recursive search
// return value is an associative array, the key of which is the path/file
// and the value is the stat of the file.
function preg_find($pattern, $start_dir = '.', $args = null)
{

    static $depth = -1;
    ++$depth;

    $files_matched = array();

    $fh = opendir($start_dir);

    while (($file = readdir($fh)) !== false)
    {
        if (strcmp($file, '.') == 0 || strcmp($file, '..') == 0) continue;
        $filepath = $start_dir . '/' . $file;
        if (preg_match($pattern, ($args & PREG_FIND_FULLPATH) ? $filepath : $file))
        {
            $doadd = is_file($filepath) || (is_dir($filepath) && ($args & PREG_FIND_DIRMATCH)) || (is_dir($filepath) && ($args &
                PREG_FIND_DIRONLY));
            if ($args & PREG_FIND_DIRONLY && $doadd && !is_dir($filepath)) $doadd = false;
            if ($args & PREG_FIND_NEGATE) $doadd = !$doadd;
            if ($doadd)
            {
                if ($args & PREG_FIND_RETURNASSOC)
                { // return more than just the filenames
                    $fileres = array();
                    if (function_exists('stat'))
                    {
                        $fileres['stat'] = stat($filepath);
                        $fileres['du'] = $fileres['stat']['blocks'] * 512;
                    }
                    if (function_exists('fileowner')) $fileres['uid'] = fileowner($filepath);
                    if (function_exists('filegroup')) $fileres['gid'] = filegroup($filepath);
                    if (function_exists('filetype')) $fileres['filetype'] = filetype($filepath);
                    if (function_exists('mime_content_type')) $fileres['mimetype'] = mime_content_type($filepath);
                    if (function_exists('dirname')) $fileres['dirname'] = dirname($filepath);
                    if (function_exists('basename')) $fileres['basename'] = basename($filepath);
                    if (($i = strrpos($fileres['basename'], '.')) !== false) $fileres['ext'] = substr($fileres['basename'], $i + 1);
                    else  $fileres['ext'] = '';
                    if (isset($fileres['uid']) && function_exists('posix_getpwuid')) $fileres['owner'] = posix_getpwuid($fileres['uid']);
                    $files_matched[$filepath] = $fileres;
                }
                else  array_push($files_matched, $filepath);
            }
        }
        if (is_dir($filepath) && ($args & PREG_FIND_RECURSIVE))
        {
            if (!is_link($filepath) || ($args & PREG_FIND_FOLLOWSYMLINKS)) $files_matched = array_merge($files_matched, preg_find($pattern,
                    $filepath, $args));
        }
    }

    closedir($fh);

    // Before returning check if we need to sort the results.
    if (($depth == 0) && ($args & (PREG_FIND_SORTKEYS | PREG_FIND_SORTBASENAME | PREG_FIND_SORTMODIFIED |
        PREG_FIND_SORTFILESIZE | PREG_FIND_SORTDISKUSAGE)))
    {
        $order = ($args & PREG_FIND_SORTDESC) ? 1 : -1;
        $sortby = '';
        if ($args & PREG_FIND_RETURNASSOC)
        {
            if ($args & PREG_FIND_SORTMODIFIED) $sortby = "['stat']['mtime']";
            if ($args & PREG_FIND_SORTBASENAME) $sortby = "['basename']";
            if ($args & PREG_FIND_SORTFILESIZE) $sortby = "['stat']['size']";
            if ($args & PREG_FIND_SORTDISKUSAGE) $sortby = "['du']";
            if ($args & PREG_FIND_SORTEXTENSION) $sortby = "['ext']";
        }
        $filesort = create_function('$a,$b', "\$a1=\$a$sortby;\$b1=\$b$sortby; if (\$a1==\$b1) return 0; else return (\$a1<\$b1) ? $order : 0- $order;");
        uasort($files_matched, $filesort);
    }
    --$depth;
    return $files_matched;

}
if (!function_exists('session_unregister'))
{
    function session_unregister($key)
    {
        unset($_SESSION[$key]);
    }
}

session_start();

$def_folder = time();
@mkdir($def_folder);

if (!file_exists($def_folder))
{
    echo ("Das Setup hat keine Schreibrechte im aktuellen Ordner und muss daher abgebrochen werden.<br>Eventuell ist Safe_Mode aktiviert.");
    exit();
}
else
{
    rmdir($def_folder);
}


$step = (int)(isset($_REQUEST['step']) ? $_REQUEST['step'] : 1);
$_SESSION['no_unpack'] = (isset($_REQUEST['nounpack']) ? 1 : 0);


define("LITO_ROOT_PATH", dirname(__file__) . '/');
define("LITO_SETUP_TEMP", LITO_ROOT_PATH . 'setup_tmp/');
define("LITO_GAME_ZIP", LITO_ROOT_PATH . 'game.zip');

$lito_version = "0.7.1";
$max_step = 7;
$filecounter = 0;

if ($step == 1)
{
    unset($_SESSION);
    if ($_SESSION['no_unpack'] == 0)
    {
        if (!is_file(LITO_GAME_ZIP))
        {
            echo ("Nicht alle für die Installation vorhandenen Dateien sind verfügbar.<br>Die Installation wurde abgebrochen.<br>Bitte wende dich an http://www.freebg.de");
            exit();
        }
    }

    if (!is_dir(LITO_SETUP_TEMP))
    {

        mkdir(LITO_SETUP_TEMP);
        chmod(LITO_SETUP_TEMP, 0777);
        if (!is_dir(LITO_SETUP_TEMP))
        {
            echo ("Bitte lege einen Ordner '" . LITO_SETUP_TEMP . "' an, welcher für die Installation Schreibrechte hat (777).");
            exit();
        }
    }

    if (!is_writable(LITO_SETUP_TEMP))
    {
        echo ("Der Ordner '" . LITO_SETUP_TEMP .
            "' hat keine Schreibrechte.<br>Bitte setze die Rechte des Ordners mittels FTP auf 777");
        exit();
    }


    if ($_SESSION['no_unpack'] == 0)
    {
        $ret = _rmdir(LITO_SETUP_TEMP);

        $zip = new ZipArchive;
        if ($zip->open(LITO_GAME_ZIP) === true)
        {
            $zip->extractTo(LITO_SETUP_TEMP);
            $zip->close();
        }
        else
        {
            echo ("Setup konnte nicht geladen werden. Error Code 0x6726<br>");
            exit();
        }
    }

    if (!is_file(LITO_SETUP_TEMP . "setup/class_template.php"))
    {
        echo ("Setup konnte nicht geladen werden. Error Code 0x6727<br>");
        exit();
    }

    require (LITO_SETUP_TEMP . "setup/class_template.php");
    $tpl = new tpl(1, 1);


    $over = "Litotex Setup -= Version:" . $lito_version . " =-";
    $content = "<span class=\"normalfont\">Dieses Setup installiert die <br>Litotex Browsergameengine auf ihrem Rechner.<br><br>Bitte wenden Sie sich im Falle von Fragen und Anregungen an unser <a href=\"http://www.litotex.info\" target=\"_blank\">Litotex Forum</a></span> ";
    $content .= "<br><br>";
    $content .= "<span class=\"normalfont\">Litotex ist eine OpenSource Software und steht unter der GPL.<br></span> ";
    $content .= "<span class=\"normalfont\">Den genauen Wortlaut können sie der Datei LICENSE.TXT entnehmen. Eine deutsche Übersetzung ist <a href=\"http://www.gnu.de/documents/gpl.de.html\" target=\"_blank\">hier </a>einsehbar.  <br><br></span> ";
    $content .= "<span class=\"normalfont\">Mit der Installation dieser Software erklären Sie sich mit den Lizenzbestimmungen der GPL sowie nachfolgenden Pukten, einverstanden.</span><br>";

    $content .= "<span class=\"smallfont\">*)den Urheberrechtshinweis im Footer nicht zu entfernen, durch andere technische Möglichkeiten auszublenden oder unsichtbar zu machen.<br>";
    $content .= "*)den Urheberrechtshinweis in allen Templates, in der Form der von Litotex ausgelieferten Layoutstrukturierung anzuzeigen.<br></span> ";
    $content .= "<br><br><span class=\"normalfont_o\">Für Fragen stehen wir im Forum oder unter info@makrotex.de jederzeit zur Verfügung.</span> ";
    $over_one = "<b>Herzlich Willkommen </b> Schritt " . $step . " von " . $max_step . " (Einleitung)";

    $action = "setup.php?step=2";
    $button = "<input type=\"submit\" class=\"buttons\" name=\"submit\" value=\"weiter\">";


    eval("\$tpl->output(\"" . $tpl->get("setup") . "\");");
    exit();
    echo ("stop");
}


if ($step == 2)
{

    require (LITO_SETUP_TEMP . "setup/class_template.php");
    $tpl = new tpl(1, 1);

    $_SESSION['error_msg'] = "";

    $over = "Litotex Setup -= Version:" . $lito_version . " =-";
    $over_one = "<b>Herzlich Willkommen </b> Schritt " . $step . " von " . $max_step . " (Daten kopieren)";
    $content = '';

    if (!mkdir(LITO_ROOT_PATH . 'test/'))
    {
        $content .= "<br><br><span class=\"error\">Fehler beim ermitteln des Verzeichnisses !!!</span><br><br>";
        $content .= "<span class=\"normalfont\">Bitte folgende Daten überprüfen:<br>";
        $content .= "Installationsordner: " . LITO_ROOT_PATH . "<br>";
        $content .= "<br>In einigen Fällen hat sich gezeigt, das die für die installation benötigten rechte fehlen.<br>";
        $content .= "</span><br><br>";
        $action = "setup.php?step=2";
        $button = "<input type=\"submit\" class=\"buttons\" name=\"submit\" value=\"zurück\">";
    }
    else
    {
        _rmdir(LITO_ROOT_PATH . 'test/');

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
        $button = "<input type=\"submit\" id=\"submit\"  class=\"buttons\" onclick=\"startinstall();return false;\" value=\"weiter\">";

        if (!is_file(LITO_ROOT_PATH . 'filelist.txt'))
        {
            $_files = _scandir(LITO_SETUP_TEMP);

            $filecounter = 0;
            $dirs = array();

            $fpFiles = fopen(LITO_ROOT_PATH . 'filelist.txt', 'w');
            $fpDir = fopen(LITO_ROOT_PATH . 'dirlist.txt', 'w');

            $ignoreDirs = array(
                substr(LITO_SETUP_TEMP, 0, -1) => false,
                LITO_SETUP_TEMP . 'setup' => true,
                LITO_SETUP_TEMP . 'setup/template' => true,
                );
            foreach ($_files as $file)
            {
                $dirname = dirname($file);
                if (isset($ignoreDirs[$dirname]) && $ignoreDirs[$dirname] === true)
                {
                    continue;
                }

                if (!isset($dirs[$dirname]) && !isset($ignoreDirs[$dirname]))
                {
                    $_dirname = str_replace(LITO_SETUP_TEMP, '', $dirname);
                    $_dirname = str_replace('./', '', $_dirname);
                    fputs($fpDir, $_dirname . "\n");

                    $dirs[$dirname] = $_dirname;
                }
                $file = str_replace(LITO_SETUP_TEMP, '', $file);
                $file = str_replace('./', '', $file);
                fputs($fpFiles, $file . "\n");
                $filecounter++;
            }
            fclose($fpFiles);
            fclose($fpDir);
        }
    }

    eval("\$tpl->output(\"" . $tpl->get("setup") . "\");");
    exit();
}

if ($step == 3)
{

    require (LITO_SETUP_TEMP . "setup/class_template.php");

    $tpl = new tpl(1, 1);


    $over = "Litotex Setup -= Version:" . $lito_version . " =-";
    $over_one = "<b>Herzlich Willkommen </b> Schritt " . $step . " von " . $max_step . " (Konfiguration Datenbank)";

    $content = "";

    if (is_file(LITO_ROOT_PATH . "dirlist.txt"))
    {
        unlink(LITO_ROOT_PATH . "dirlist.txt");
    }
    if (is_file(LITO_ROOT_PATH . "filelist.txt"))
    {
        unlink(LITO_ROOT_PATH . "filelist.txt");
    }

    chmod(LITO_ROOT_PATH . "options/options.php", 0777);

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
    $content .= "<tr><td ><span class=\"normalfont_o\"> MySQL Username</span></td><td> <input name=\"sql_username\" type=\"text\" class=\"textinput\" value=\"$sql_user\" size=\"40\" /></td></tr>";
    $content .= "<tr><td><span class=\"normalfont_o\">MySQL Kennwort</span></td><td><input name=\"sql_kennw\" type=\"text\" class=\"textinput\" value=\"$sql_kennwo\" size=\"40\" /></td></tr>";
    $content .= "<tr><td><span class=\"normalfont_o\">MySQL Port</span></td><td><input name=\"sql_port\" type=\"text\" class=\"textinput\" value=\"$sql_port\" size=\"10\" /></td></tr>";
    $content .= "<tr><td><span class=\"normalfont_o\">MySQL Datenbank</span></td><td><input name=\"sql_db\" type=\"text\" class=\"textinput\" value=\"$sql_db\" size=\"10\" /></td></tr>";
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
if ($step == 4)
{
    require (LITO_SETUP_TEMP . "setup/class_template.php");

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

    try
    {
        $link = new PDO('mysql:host=' . $sql_server . ';dbname=' . $sql_db, $sql_user, $sql_kennwo, array(
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ));
    }
    catch (PDOException $e)
    {
        $_SESSION['error_msg'] = "Es konnte keine Verbindung zum SQL Server hergestellt werden.<br>Error:" . $e->getMessage();
        header("LOCATION: setup.php?step=4");
        exit();
    }
    $_SESSION['error_msg'] = "";

    $sqlCode = file_get_contents(LITO_SETUP_TEMP . "setup/db_clean.sql");
    try
    {
        $link->exec($sqlCode);
    }
    catch (PDOException $e)
    {
        $content .= "Beim importieren ist ein Fehler aufgetreten<br>" . $e->getMessage();
        $_SESSION['error_msg'] = $e->getMessage();
    }

    $content .= "<span class=\"normalfont\">Die Verbindung zum SQL Server wurde erfolgreich hergestellt.<br><br><br></span>";
    $content .= "<span class=\"normalfont_o\">Alle Daten wurden in der Datenbank gespeichert<br><br></span>";
    $content .= "<span class=\"normalfont\">Die Litotex Datenbank wurde erfolgreich angelegt.<br><br>";
    $content .= "Im nächsten Schritt erfolgt das Installieren der Spieldateien.<br></span>";
    $button = "<input type=\"submit\" class=\"buttons\" name=\"submit\" value=\"weiter\">";
    $action = "setup.php?step=5";
    $button = "<input type=\"submit\" class=\"buttons\" name=\"submit\" value=\"weiter\">";

    eval("\$tpl->output(\"" . $tpl->get("setup") . "\");");

    $link = null;
    exit();
}

if ($step == 5)
{
    require (LITO_SETUP_TEMP . "setup/class_template.php");

    if (isset($_SESSION['error_msg']))
    {
        $error_msg = $_SESSION['error_msg'];
    }

    $tpl = new tpl(1, 1);

    $game_path = dirname(__file__) . '/';
    $current_url = getBaseUrl();


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
if ($step == 6)
{
    require (LITO_SETUP_TEMP . "setup/class_template.php");

    $tpl = new tpl(1, 1);
    $_SESSION['error_msg'] = "";

    $admin_name = trim($_POST['admin_name']);
    $admin_kwort = trim($_POST['admin_kwort']);


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

    if (substr($game_path, -1) != '/')
    {
        $game_path = $game_path . "/";
    }
    if (substr($game_url, -1) != '/')
    {
        $game_url = $game_url . "/";
    }


    $fp = fopen($option_f_name, "w");
    $fw = fwrite($fp, "
<?php
\$dbhost = \"" . $_SESSION['sql_server'] . "\";
\$dbuser = \"" . $_SESSION['sql_user'] . "\";
\$dbpassword = \"" . $_SESSION['sql_kennwo'] . "\";
\$dbbase = \"" . $_SESSION['sql_db'] . "\";

\$litotex_path = \"" . $game_path . "\";
\$litotex_url = \"" . $game_url . "\";
\$n = 1;
?>");


    $sql_server = $_SESSION['sql_server'];
    $sql_user = $_SESSION['sql_user'];
    $sql_kennwo = $_SESSION['sql_kennwo'];
    $sql_port = $_SESSION['sql_port'];
    $sql_db = $_SESSION['sql_db'];

    try
    {
        $link = new PDO('mysql:host=' . $sql_server . ';dbname=' . $sql_db, $sql_user, $sql_kennwo, array(
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ));
    }
    catch (PDOException $e)
    {
        $_SESSION['error_msg'] = "Es konnte keine Verbindung zum SQL Server hergestellt werden.<br>Error:" . $e->getMessage();
        header("LOCATION: setup.php?step=4");
        exit();
    }

    $n = 1;
    $result = $link->query("SELECT * FROM cc" . $n . "_crand ORDER BY rand()");
    $land = $result->fetch(PDO::FETCH_ASSOC);
    $password = password_hash($admin_kwort, PASSWORD_DEFAULT);


    $link->exec("INSERT INTO cc" . $n . "_users (username,email,password, serveradmin,register_date) VALUES ('" . $admin_name .
        "','','$password',  1,'" . time() . "')");
    $userid_r = $link->lastInsertId();
    $link->exec("INSERT INTO cc" . $n .
        "_countries (res1,res2,res3,res4,userid,lastressources,picid,x,y,size) VALUES ('4000','4000','4000','4000','$userid_r','" .
        time() . "','1','$land[x]','$land[y]','500')");
    $islandid_r = $link->lastInsertId();

    $link->exec("UPDATE cc" . $n . "_crand SET used='1' WHERE x='" . $land['x'] . "' AND y='" . $land['y'] . "'; 
                  UPDATE cc" . $n . "_users SET activeid='$islandid_r' WHERE userid='$userid_r';");

    $over = "Litotex Setup -= Version:" . $lito_version . " =-";
    $over_one = "<b>Herzlich Willkommen </b> Schritt " . $step . " von " . $max_step . " (Ende)";
    $content = "";

    $content .= "<span class=\"normalfont_o\"><b>Herzlichen Glückwunsch</b><br><br></span>";
    $content .= "<span class=\"normalfont\">Die Installation ist erfolgreich abgeschlossen.<br>";
    $content .= "Bitte löschen Sie die Datei setup.php von Ihren Server<br>";
    $content .= "<br><br>";
    $content .= "Zum Login bitte <a href=\"index.php\" target=\"_blank\">HIER</a> klicken .<br>";
    $content .= "Das ACP (AdminControlCenter) befindet sich <a href=\"./acp/index.php\" target=\"_blank\">HIER</a> ";
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
if ($step == 7)
{
    if (is_dir(LITO_SETUP_TEMP))
    {
        _rmdir(LITO_SETUP_TEMP);
    }
    if (is_file(LITO_ROOT_PATH . 'setup.php'))
    {
        unlink(LITO_ROOT_PATH . 'setup.php');
    }
    if (is_file(LITO_GAME_ZIP))
    {
        unlink(LITO_GAME_ZIP);
    }
    echo ("Die temporäre Installationsdateien wurden vom Server gelöscht.");
    exit();
}
