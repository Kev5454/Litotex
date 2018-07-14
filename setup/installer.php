<?php

/**
 * Litotex - Browsergame Engine
 * Copyright 2017 Das litotex.info Team, All Rights Reserved
 *
 * Website: http://www.litotex.info
 * License: GNU GENERAL PUBLIC LICENSE v3 (https://litotex.info/showthread.php?tid=3)
 *
 */

session_start();

if ( !defined( 'DIRECTORY_SEPARATOR' ) )
{
    define( 'DIRECTORY_SEPARATOR','/' );
}
define( "LITO_ROOT_PATH",dirname( dirname( dirname( __file__ ) ) ) . DIRECTORY_SEPARATOR );


header( 'Content-Type: text/html; charset=ISO-8859-1' );

$cur_pos = ( isset( $_REQUEST['id'] ) ? filter_var( $_REQUEST['id'],FILTER_SANITIZE_NUMBER_INT,array( 'options' => array( 'default' => 0 ) ) ) : 0 );
$action = ( isset( $_REQUEST['action'] ) ? filter_var( $_REQUEST['action'],FILTER_SANITIZE_STRING,array( 'options' => array( 'default' => "files" ) ) ) : "files" );

if ( $action == "files" )
{
    if ( $cur_pos == 0 )
    {
        function _mkdir( $directory,$public_access = true )
        {
            $dirName = LITO_ROOT_PATH . $directory . DIRECTORY_SEPARATOR;
            mkdir( $dirName );
            chmod( $dirName,0777 );

            if ( $public_access == false )
            {
                copy( LITO_ROOT_PATH . 'includes' . DIRECTORY_SEPARATOR . '.htaccess',$dirName . '.htaccess' );
            }
        }

        _mkdir( 'alli_flag' );
        _mkdir( 'backup',false );
        _mkdir( 'battle_kr' );
        _mkdir( 'cache',false );
        _mkdir( 'image_user' );
        _mkdir( 'images_sig' );
        _mkdir( 'images_tmp' );
        _mkdir( 'templates_c',false );
        _mkdir( 'templates_c/standard' );
        _mkdir( 'acp/cache',false );
        _mkdir( 'acp/templates_c',false );
        _mkdir( 'acp/templates_c/standard' );
        _mkdir( 'acp/tmp' );

        $inhalt = file_get_contents( LITO_ROOT_PATH . 'dirliste.json' );
        $inhalt = json_decode( $inhalt );
        foreach ( $inhalt as $directory )
        {
            $directory = str_replace( DIRECTORY_SEPARATOR . 'setup_tmp','',$directory );
            if ( is_dir( $directory ) )
            {
                continue;
            }

            mkdir( $directory );
        }
    }

    $inhalt = file_get_contents( LITO_ROOT_PATH . 'fileliste.json' );
    $inhalt = json_decode( $inhalt );


    $oldFileName = $inhalt[$cur_pos];
    $newFileName = str_replace( DIRECTORY_SEPARATOR . 'setup_tmp','',$oldFileName );

    echo ( "installiere: " . str_replace( LITO_ROOT_PATH,'.' . DIRECTORY_SEPARATOR,$newFileName ) );

    if ( !file_exists( $oldFileName ) )
    {
        echo ( "Source-Datei konnte nicht gefunden werden!" );
        exit();
    }

    if ( !copy( $oldFileName,$newFileName ) )
    {
        echo ( "Datei konnte nicht kopiert werden!" );
        exit();
    }
}
elseif ( $action == "db" )
{
    require ( LITO_ROOT_PATH . 'includes' . DIRECTORY_SEPARATOR . 'class_db_mysql.php' );

    $mysqli = new db( $_SESSION['sql_server'],$_SESSION['sql_user'],$_SESSION['sql_kennwo'],$_SESSION['sql_db'],$_SESSION['sql_port'],true );
    $result = $mysqli->connect();
    if ( $result !== true )
    {
        echo "Es konnte keine Verbindung zum SQL Server hergestellt werden.<br>Error:" . $result;
        $mysqli->close();
        exit();
    }
    $mysqli->unbuffered_query( "SET character_set_client = 'utf8'" );
    $mysqli->unbuffered_query( "SET character_set_connection = 'utf8'" );

    $lines = file( LITO_ROOT_PATH . 'setup/db_clean.sql' );
    $countLines = count( $lines );
    $lineIndex = 0;

    $_SESSION['line'] = ( !isset( $_SESSION['line'] ) ? 0 : $_SESSION['line'] );
    $toexec = "";
    for ( $lineIndex = $_SESSION['line']; $lineIndex < $countLines; $lineIndex++ )
    {
        if ( empty( $lines[$lineIndex] ) || substr( $lines[$lineIndex],0,2 ) == '--' )
        {
            continue;
        }

        $toexec .= $lines[$lineIndex];

        if ( substr( trim( $lines[$lineIndex] ),-1,1 ) == ';' )
        {
            break;
        }
    }

    $toexec = str_replace( '{#SERVERID#}',$_SESSION['serverID'],$toexec );
    if ( $mysqli->query( $toexec ) !== true )
    {
        echo "Beim importieren ist ein Fehler aufgetreten<br>" . $toexec . "<br>" . $mysqli->error( $toexec );
        exit();
    }

    $_SESSION['line'] = $lineIndex + 1;
    echo ( "Führt Mysql-Operation aus: " . substr( $toexec,0,100 ) . "..." );
    exit();
}

exit();
