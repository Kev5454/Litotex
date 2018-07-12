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
    public $packid = 1;

    public function __construct($packid)
    {
        $this->packid = $packid;
    }


    public function output($templatefile)
    {
        global $filecounter, $action, $over, $over_one, $content, $button;
        $t_name = LITO_SETUP_PATH . "setup/template/" . $this->packid . "_" . $templatefile . ".php";
        if (file_exists($t_name))
        {
            include ($t_name);
        }
        else
        {
            echo "Template '$templatefile' not found!\n<br>";
            exit();
        }
    }
}
