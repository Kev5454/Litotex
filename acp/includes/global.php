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

if ( version_compare( phpversion(),'5.2.0' ) <= 0 )
{
    echo 'Sie benötigen mindestens PHP 5.2.0\n um diese Engine nutzen zu können!';
    exit();
}

if ( !defined( 'DIRECTORY_SEPARATOR' ) )
{
    define( 'DIRECTORY_SEPARATOR','/' );
}

define( 'LITO_VERSION','0.7.4' );

if ( session_id() == "" )
{
    session_name( "litoid" );
    session_start();
}

ini_set( 'display_errors',1 );
ini_set( 'display_startup_errors',1 );
error_reporting( E_ALL );

$sid = session_id();
//$Database index
$n = 1;

if ( isset( $_SESSION['litotex_start_acp'] ) )
{
    $litotex_path = $_SESSION['litotex_start_acp'];
    $litotex_url = $_SESSION['litotex_start_url'];
}
else
{

    if ( is_file( '../includes/config.php' ) )
    {
        require ( '../includes/config.php' );
    }
    elseif ( is_file( '../config.php' ) )
    {
        require ( '../config.php' );
    }
    else
    {
        header( "LOCATION: ./../../index.php" );
        exit();
    }

    $_SESSION['litotex_start_acp'] = $litotex_path;
    $_SESSION['litotex_start_url'] = $litotex_url;
}
$dir = dirname( dirname( dirname( __file__ ) ) );

$basedir = str_replace( "\\","/",$_SERVER["SCRIPT_FILENAME"] );
$basedir = substr( $basedir,0,strrpos( $basedir,"/" ) ) . "/";


define( "LITO_THEMES",'standard' );
// e.g.  standard

define( "LITO_ROOT_PATH",$litotex_path );
// e.g.  /srv/www/vhosts/freebg.de/subdomains/dev/httpdocs/
define( "LITO_ROOT_PATH_URL",$litotex_url );
// e.g.  http://dev.freebg.de/

define( "LITO_THEMES_PATH",$litotex_path . 'acp/themes/' . LITO_THEMES . '/' );
// e.g.  srv/www/vhosts/freebg.de/subdomains/dev/httpdocs/acp/themes/standard/
define( "LITO_THEMES_PATH_URL",$litotex_url . 'acp/themes/' . LITO_THEMES . '/' );
// e.g.  http://dev.freebg.de/acp/themes/standard/

define( "LITO_IMG_PATH",$litotex_path . 'acp/images/' . LITO_THEMES . '/' );
// e.g.  /srv/www/vhosts/freebg.de/subdomains/dev/httpdocs/acp/images/standard/
define( "LITO_IMG_PATH_URL",$litotex_url . 'acp/images/' . LITO_THEMES . '/' );
// e.g.  http://dev.freebg.de/acp/images/standard/

define( "LITO_MODUL_PATH",$litotex_path . 'acp/modules/' );
// e.g.  /srv/www/vhosts/freebg.de/subdomains/dev/httpdocs/acp/modules/
define( "LITO_MODUL_PATH_URL",$litotex_url . 'acp/modules/' );
// e.g.  http://dev.freebg.de/acp/modules/

define( "LITO_LANG_PATH",$litotex_path . 'acp/lang/' );
// e.g.  /srv/www/vhosts/freebg.de/subdomains/dev/httpdocs/acp/lang/
define( "LITO_LANG_PATH_URL",$litotex_url . 'acp/lang/' );
// e.g.  http://dev.freebg.de/acp/lang/

define( "LITO_INCLUDES_PATH",$litotex_path . 'includes/' );
define( "LITO_MAIN_CSS",$litotex_url . 'acp/css/litotex.css' );
define( "LITO_JS_URL",$litotex_url . 'acp/js/' );
define( "LITO_GLOBAL_IMAGE_URL",$litotex_url . 'acp/images/' );

$lang_suffix = "de";


require ( LITO_ROOT_PATH . 'options/options.php' );
require ( LITO_INCLUDES_PATH . 'config.php' );
require ( LITO_INCLUDES_PATH . 'class_db_mysql.php' );
require ( 'functions.php' );
require ( LITO_INCLUDES_PATH . 'smarty/SmartyBC.class.php' ); // Smarty class laden und pr�fen

if ( intval( $op_use_ftp_mode ) == 1 )
{
    define( "C_FTP_METHOD",'1' );
}


$db = new db( $dbhost,$dbuser,$dbpassword,$dbbase );
if ( $db->connect() !== true )
{
    echo 'Datenbank konnte keine Verbindung aufbauen!';
    exit();
}

$db->unbuffered_query( "SET character_set_client = 'utf8'" );
$db->unbuffered_query( "SET character_set_connection = 'utf8'" );

$time_start = explode( ' ',substr( microtime(),1 ) );
$time_start = $time_start[1] + $time_start[0];

$tpl = new SmartyBC;
$tpl->template_dir = LITO_THEMES_PATH;
$tpl->compile_dir = LITO_ROOT_PATH . 'acp' . DIRECTORY_SEPARATOR . 'templates_c' . DIRECTORY_SEPARATOR . LITO_THEMES;
$tpl->cache_dir = LITO_ROOT_PATH . 'acp' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . LITO_THEMES;

if ( !is_dir( $tpl->compile_dir ) )
{
    _mkdir( 'acp' . DIRECTORY_SEPARATOR . 'templates_c' . DIRECTORY_SEPARATOR . LITO_THEMES,false );
}
if ( !is_dir( $tpl->cache_dir ) )
{
    _mkdir( 'acp' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . LITO_THEMES,false );
}

setlocale( LC_ALL,array(
    'de_DE',
    'de_DE@euro',
    'de',
    'ger' ) );

if ( isset( $_SESSION['userid'] ) )
{
    $result = $db->query( "SELECT * FROM cc" . $n . "_users WHERE userid='" . $_SESSION['userid'] . "'" );
    $userdata = $db->fetch_array( $result );


    $tpl->assign( 'if_user_login',1 );
    $tpl->assign( 'LOGIN_USERNAME',$userdata['username'] );
}
else
{
    $tpl->assign( 'if_user_login',0 );
    $tpl->assign( 'LOGIN_USERNAME',"unbekannt" );
}

$tpl->assign( 'if_login_error',0 );
$tpl->assign( 'if_disable_menu',0 );
$tpl->assign( 'menu_name','' );

$tpl->assign( 'GAME_TITLE_TEXT',$op_set_gamename );

$tpl->assign( 'GLOBAL_RES1_NAME',$op_set_n_res1 );
$tpl->assign( 'GLOBAL_RES2_NAME',$op_set_n_res2 );
$tpl->assign( 'GLOBAL_RES3_NAME',$op_set_n_res3 );
$tpl->assign( 'GLOBAL_RES4_NAME',$op_set_n_res4 );
