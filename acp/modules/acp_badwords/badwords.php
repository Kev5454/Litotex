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
if ( !isset( $_SESSION['litotex_start_acp'] ) || !isset( $_SESSION['userid'] ) )
{
    header( 'LOCATION: ./../../index.php' );
    exit();
}


$action = ( isset( $_REQUEST['action'] ) ? filter_var( $_REQUEST['action'],FILTER_SANITIZE_STRING ) : 'main' );

$modul_name = "acp_badwords";
$menu_name = "Badwordmanager";

require ( $_SESSION['litotex_start_acp'] . 'acp/includes/global.php' );
require ( $_SESSION['litotex_start_acp'] . 'acp/includes/perm.php' );

$tpl->assign( 'menu_name',$menu_name );
if ( $action == 'main' )
{
    $badwords = array();
    $words = $db->query( "SELECT `badword_id`, `badword`, `in_mail` FROM `cc" . $n . "_badwords`" );
    $i = 0;
    while ( $badword = $db->fetch_array( $words ) )
    {
        $badwords[$i]['id'] = $badword['badword_id'];
        $badwords[$i]['title'] = $badword['badword'];
        $badwords[$i]['in_mail'] = $badword['in_mail'];
        $i++;
    }

    $changeID = ( isset( $_GET['changeID'] ) ? intval( $_GET['changeID'] ) : 0 );
    $tpl->assign( 'change',$changeID );
    $tpl->assign( 'badwords',$badwords );
    template_out( 'list.html',$modul_name );
}
elseif ( $action == 'delete' )
{
    if ( !isset( $_GET['id'] ) || $_GET['id'] < 0 )
    {
        error_msg( 'Keine ID &uuml;bergeben!' );
        exit;
    }

    $id = filter_var( $_GET['id'],FILTER_SANITIZE_NUMBER_INT );
    $db->query( "DELETE FROM `cc" . $n . "_badwords` WHERE `badword_id` = '" . $id . "'" );

    redirect( $modul_name,'badwords','main' );
}
elseif ( $action == 'change' )
{
    if ( !isset( $_GET['id'] ) )
    {
        error_msg( 'Keine ID &uuml;bergeben!' );
        exit;
    }

    $id = filter_var( $_GET['id'],FILTER_SANITIZE_NUMBER_INT );
    redirect( $modul_name,'badwords','main',array( 'changeID' => $id ) );
}
elseif ( $action == 'save' )
{
    if ( !isset( $_GET['id'] ) )
    {
        error_msg( 'Keine ID &uuml;bergeben!' );
        exit;
    }
    if ( !isset( $_POST['title'] ) )
    {
        error_msg( 'Kein Titel &uuml;bergeben!' );
        exit;
    }
    $_POST['in_mail'] = ( !isset( $_POST['in_mail'] ) ? 0 : 1 );

    $id = filter_var( $_GET['id'],FILTER_SANITIZE_NUMBER_INT );
    $db->query( "UPDATE `cc" . $n . "_badwords` SET `badword` = '" . $db->escape_string( $_POST['title'] ) . "', `in_mail` = '" . $db->escape_string( $_POST['in_mail'] ) . "' WHERE `badword_id` = '" . $id . "'" );


    redirect( $modul_name,'badwords','main' );
}
elseif ( $action == 'new' )
{
    if ( !isset( $_POST['title'] ) )
    {
        error_msg( 'Kein Titel &uuml;bergeben!' );
        exit;
    }
    $_POST['in_mail'] = ( !isset( $_POST['in_mail'] ) ? 0 : 1 );

    $db->query( "INSERT INTO `cc" . $n . "_badwords` (`badword`, `in_mail`) VALUES ('" . $db->escape_string( $_POST['title'] ) . "', '" . $db->escape_string( $_POST['in_mail'] ) . "')" );

    redirect( $modul_name,'badwords','main' );
}
