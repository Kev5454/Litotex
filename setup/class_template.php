<?php

/**
 * Litotex - Browsergame Engine
 * Copyright 2017 Das litotex.info Team, All Rights Reserved
 *
 * Website: http://www.litotex.info
 * License: GNU GENERAL PUBLIC LICENSE v3 (https://litotex.info/showthread.php?tid=3)
 *
 */

class tpl
{

    public $templatefolder = ""; //The default templatefolder is emtpy
    public $expression = "";
    public $packid = 1;


    public function __construct($packid)
    {
        $this->packid = $packid;  
    }


    public function get($templatefile)
    {

        $t_name = "./setup_tmp/setup/template/" . $this->packid . "_" . $templatefile . ".php";


        if (file_exists($t_name))
        {
            @include ($t_name);
        }
        else
        {
            $this->template2error($templatefile);
            exit();
        }
        return $template[$templatefile];
    }


    public function output($template)
    {
        print ($template);
    }


    public function template2error($templatefile)
    {
        echo "Template '$templatefile' not found!\n<br>";
    }

}