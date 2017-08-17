<?php

/**
 * Litotex - Browsergame Engine
 * Copyright 2017 Das litotex.info Team, All Rights Reserved
 *
 * Website: http://www.litotex.info
 * License: GNU GENERAL PUBLIC LICENSE v3 (https://litotex.info/showthread.php?tid=3)
 *
 */

$time_start_all = explode(' ', substr(microtime(), 1));
$time_start_all = $time_start_all[1] + $time_start_all[0];

require ("./includes/config.php");
require ("./includes/class_db_mysql.php");
require ("./options/options.php");
require ("./includes/functions.php");

$key = (isset($_REQUEST['key']) ? filter_var($_REQUEST['key'], FILTER_SANITIZE_STRING) : null);
$server = (isset($_REQUEST['sid']) ? filter_var($_REQUEST['sid'], FILTER_SANITIZE_STRING) : null);
$type = (isset($_REQUEST['t']) ? filter_var($_REQUEST['t'], FILTER_SANITIZE_STRING) : 'all');

if (!isset($op_update_key) || empty($key) || empty($server) || strlen($key) != 32 || $op_update_key != $key || ($type !=
    'all' && $type != 'points' && $type != 'res'))
{
    exit();
}


$db = new db($dbhost, $dbuser, $dbpassword, $dbbase, $dbport);
$n = (int)$server;

if (intval($op_res_reload_type) == 1 && ($type == 'all' || $type == 'res'))
{
    require ('./includes/resreload.php');
}

if ($type == 'all' || $type == 'points')
{
    require ('./includes/update.php');
}

$time_end_all = explode(' ', substr(microtime(), 1));
$time_end_all = $time_end_all[1] + $time_end_all[0];
$run_time_all = $time_end_all - $time_start_all;
$end_msg = "All Cronhobs DONE  time: " . number_format($run_time_all, 5, '.', '') . " sec. ";

Trace_msg("$end_msg", 888);
