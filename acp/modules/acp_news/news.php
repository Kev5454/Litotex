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
$modul_name = "acp_news";

require ( $_SESSION['litotex_start_acp'] . 'acp/includes/global.php' );
require ( $_SESSION['litotex_start_acp'] . 'acp/includes/perm.php' );

if ( $action == "main" )
{
    $menu_name = "News";
    $new_found = array();
    $result_news = $db->query( "SELECT * FROM cc" . $n . "_news order by news_id " );

    while ( $row_g = $db->fetch_array( $result_news ) )
    {
        $tt_text = $row_g['text'];
        $tt_text = str_replace( "\"","\'",$tt_text );
        $new_found[] = array(
            $row_g['news_id'],
            $row_g['date'],
            $tt_text,
            $row_g['heading'],
            $row_g['activated'],
            );
    }
    $tpl->assign( 'menu_name',$menu_name );
    $tpl->assign( 'daten',$new_found );
    template_out( 'news.html',$modul_name );
}
elseif ( $action == "new" )
{
    $menu_name = "News eintragen";

    $tpl->assign( 'NEWS_OVER',"" );
    $tpl->assign( 'NEWS_TEXT_LANG',"" );
    $tpl->assign( 'menu_name',$menu_name );
    $tpl->assign( 'ACTION_SAVE','save' );
    template_out( 'news_new.html',$modul_name );
}
elseif ( $action == "save" )
{
    if ( empty( $_POST['new_news'] ) || empty( $_POST['new_news'] ) )
    {
        error_msg( $l_emptyfield_error );
    }
    else
    {
        $text = filter_var( $_POST['new_news'],FILTER_SANITIZE_STRING );
        $heading = filter_var( $_POST['heading'],FILTER_SANITIZE_STRING );

        $text = $db->escape_string( trim( $text ) );
        $heading = ( empty( $heading ) ? 'Kein Inhalt' : $db->escape_string( trim( $heading ) ) );

        $date = date( "d.m.Y, H:i" );
        $order = array( "\r\n","</p>" );
        $replace = '<p>';
        $text = str_replace( $order,$replace,$text );
        $text = nl2br( $text );
        $db->query( "INSERT into cc" . $n . "_news (user_id,heading,date,text) VALUES('$_SESSION[userid]','$heading','$date','" . $text . "')" );
    }
    redirect( $modul_name,'news','main' );
}
elseif ( $action == "edit" )
{
    $news_id = filter_var( $_GET['id'],FILTER_SANITIZE_NUMBER_INT );

    $result_news = $db->query( "SELECT * FROM cc" . $n . "_news where news_id ='$news_id'" );
    $row = $db->fetch_array( $result_news );

    $tpl->assign( 'NEWS_OVER',$row['heading'] );
    $tpl->assign( 'NEWS_TEXT_LANG',$row['text'] );
    $tpl->assign( 'ACTION_SAVE','update&id=' . $news_id );
    template_out( 'news_new.html',$modul_name );
}
elseif ( $action == "update" )
{
    $news_id = filter_var( $_GET['id'],FILTER_SANITIZE_NUMBER_INT );

    if ( empty( $_POST['new_news'] ) || empty( $_POST['new_news'] ) )
    {
        error_msg( $l_emptyfield_error );
    }
    else
    {
        $text = filter_var( $_POST['new_news'],FILTER_SANITIZE_STRING );
        $heading = filter_var( $_POST['heading'],FILTER_SANITIZE_STRING );

        $text = $db->escape_string( trim( $text ) );
        $heading = ( empty( $heading ) ? 'Kein Inhalt' : $db->escape_string( trim( $heading ) ) );

        $date = date( "d.m.Y, H:i" );
        $db->query( "update cc" . $n . "_news set user_id='$_SESSION[userid]' ,heading= '$heading' ,date='$date',text='" . nl2br( $text ) . "' where news_id ='$news_id'" );
    }
    redirect( $modul_name,'news','main' );
}
elseif ( $action == "activate" )
{
    $news_id = filter_var( $_GET['id'],FILTER_SANITIZE_NUMBER_INT );

    $result_news = $db->query( "SELECT activated FROM cc" . $n . "_news where news_id ='$news_id'" );
    $row = $db->fetch_array( $result_news );

    if ( $row['activated'] == 0 )
    {
        $sql = "update cc" . $n . "_news set activated='1' where news_id ='$news_id'";
    }
    else
    {
        $sql = "update cc" . $n . "_news set activated='0' where news_id ='$news_id'";
    }

    $db->query( $sql );
    redirect( $modul_name,'news','main' );
}
elseif ( $action == "delete" )
{
    $news_id = filter_var( $_GET['id'],FILTER_SANITIZE_NUMBER_INT );

    $db->query( "delete from cc" . $n . "_news where news_id ='$news_id'" );

    redirect( $modul_name,'news','main' );
}
