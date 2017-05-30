<?PHP

/*
************************************************************
Litotex Browsergame - Engine
http://www.Litotex.de
http://www.freebg.de

Copyright (c) 2008 FreeBG Team
************************************************************
Hinweis:
Diese Software ist urheberrechtlich gesch�tzt.

F�r jegliche Fehler oder Sch�den, die durch diese Software
auftreten k�nnten, �bernimmt der Autor keine Haftung.

Alle Copyright - Hinweise innerhalb dieser Datei 
d�rfen WEDER entfernt, NOCH ver�ndert werden. 
************************************************************
Released under the GNU General Public License 
************************************************************  

*/

require ('../../includes/global.php');

$modul_name = "acp_login";
$action = (isset($_REQUEST['action']) ? $_REQUEST['action'] : 'main');


function login_error($error_msg)
{
    global $tpl;
    $modul_name = "acp_login";

    $tpl->assign('if_disable_menu', 1);
    $tpl->assign('LITO_ERROR_MSG', $error_msg);
    $tpl->assign('if_login_error', 1);
    template_out('login.html', $modul_name);
}


if ($action == "main")
{
    //$tpl ->display("login/login.html");
    $tpl->assign('if_disable_menu', 1);

    template_out('login.html', $modul_name);
    exit();
}


if ($action == "submit")
{
    if (isset($_SESSION['userid']) && is_int($_SESSION['userid']) && $_SESSION['userid'] >= 0)
    {
        $row = $db->select("SELECT * FROM cc" . $n . "_users WHERE userid='" . $_SESSION['userid'] . "'");
        if (!$row['serveradmin'])
        {
            $grp = $db->select("SELECT `perm_lvl`, `id` FROM `cc" . $n . "_user_groups` WHERE `id` = '" . $row['group'] . "'");
            if (!$grp)
            {
                login_error('Schwerer Fehler! Die Usergruppe konnte nicht gefunden werden! Sie haben keine Berechtigungen f&uuml;r diesen Bereich!');
                exit;
            }
            if ($grp['perm_lvl'] <= 0)
            {
                login_error('Sie haben keine Berechtigungen f&uuml;r diesen Bereich!');
                exit();
            }
        }
    }
    else
    {

        $tpl->configLoad(LITO_LANG_PATH . 'acp_login/lang_' . $lang_suffix . '.php');
        if (!isset($_POST['username']) || !isset($_POST['password']))
        {
            header("LOCATION: " . getSiteUrl('acp_login', 'login.php', '?action=main'));
            exit();
        }

        $username = strtolower(trim($_POST['username']));
        $password = $_POST['password'];

        $row = $db->select("SELECT * FROM cc" . $n . "_users WHERE username='$username'");
        if (strtolower($row['username']) != $username)
        {
            trace_msg("login ERROR '$username' wrong username", 2);
            login_error($tpl->getConfigVars('LOGIN_ERROR_2'));
            exit();
        }

        if (password_verify($password, $row['password']) !== true)
        {
            trace_msg("login ERROR '$username' wrong password", 2);
            login_error($tpl->getConfigVars('LOGIN_ERROR_2'));
            exit();
        }

        if (!$row['serveradmin'])
        {
            $grp = $db->select("SELECT `perm_lvl`, `id` FROM `cc" . $n . "_user_groups` WHERE `id` = '" . $row['group'] . "'");
            if (!$grp)
            {
                login_error('Schwerer Fehler! Die Usergruppe konnte nicht gefunden werden! Sie haben keine Berechtigungen f&uuml;r diesen Bereich!');
                exit;
            }
            if ($grp['perm_lvl'] <= 0)
            {
                login_error('Sie haben keine Berechtigungen f&uuml;r diesen Bereich!');
                exit();
            }
        }

        $_SESSION['userid'] = (int)$row['userid'];

        trace_msg("login OK '$username' ", 2);
        $db->update("UPDATE cc" . $n . "_users SET lastlogin='" . time() . "', ip='" . getenv("REMOTE_ADDR") .
            "' WHERE username='$username'");
    }

    header("LOCATION: " . getSiteUrl('acp_core', 'admin.php'));
    exit();
}
