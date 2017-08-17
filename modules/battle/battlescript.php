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
Diese Software ist urheberechtlich gesch�tzt.

F�r jegliche Fehler oder Sch�den, die durch diese Software
auftreten k�nnten, �bernimmt der Autor keine Haftung.

Alle Copyright - Hinweise Innerhalb dieser Datei 
d�rfen NICHT entfernt und NICHT ver�ndert werden. 
************************************************************
Released under the GNU General Public License 
************************************************************  
*/
class kampfs
{


    private $angreifer = null;
    private $verteidiger = null;
    private $angreifer_org = null;
    private $verteidiger_org = null;
    private $anzahl_angreifer = 0;
    private $anzahl_verteidiger = 0;
    private $anzahl_angreifer_vor = 0;
    private $anzahl_verteidiger_vor = 0;
    private $trace_on = 0;
    private $trace_filename = "";
    private $all_trace = "";
    /************************************/


    public function __construct()
    {
        $this->anzahl_angreifer_end = 0;
        $this->anzahl_verteidiger_end = 0;
        $this->trace_file;

    }

    public function set_trace($filename)
    {
        $this->trace_on = 1;
        $this->trace_filename = $filename;

    }

    public function setunits_angreifer($unit_id, $ap, $vp, $ap_single, $vp_single, $unit_count, $unit_tabless)
    {

        $this->trace_ks("setunits_angreifer $unit_id, $ap, $vp,$ap_single,$vp_single,$unit_count,$unit_tabless\n");
        $this->angreifer[] = array(
            "unitid" => $unit_id,
            "angr" => $ap,
            "vert" => $vp,
            "ap_single" => $ap_single,
            "vp_single" => $vp_single,
            "unit_count" => $unit_count,
            "unit_type" => $unit_tabless,
            "new_units_count" => $unit_count);
        $this->angreifer_org[] = array(
            "unitid" => $unit_id,
            "angr" => $ap,
            "vert" => $vp,
            "ap_single" => $ap_single,
            "vp_single" => $vp_single,
            "unit_count" => $unit_count,
            "unit_type" => $unit_tabless,
            "new_units_count" => $unit_count);
        $this->anzahl_angreifer += $unit_count;
        $this->anzahl_angreifer_vor += $unit_count;

        return 1;
    }

    public function setunits_verteidiger($unit_id, $ap, $vp, $ap_single, $vp_single, $unit_count, $unit_tabless)
    {

        $this->trace_ks("setunits_verteidiger $unit_id, $ap, $vp,$ap_single,$vp_single,$unit_count,$unit_tabless\n");
        $this->verteidiger[] = array(
            "unitid" => $unit_id,
            "angr" => $ap,
            "vert" => $vp,
            "ap_single" => $ap_single,
            "vp_single" => $vp_single,
            "unit_count" => $unit_count,
            "unit_type" => $unit_tabless,
            "new_units_count" => $unit_count);
        $this->verteidiger_org[] = array(
            "unitid" => $unit_id,
            "angr" => $ap,
            "vert" => $vp,
            "ap_single" => $ap_single,
            "vp_single" => $vp_single,
            "unit_count" => $unit_count,
            "unit_type" => $unit_tabless,
            "new_units_count" => $unit_count);
        $this->anzahl_verteidiger += $unit_count;
        $this->anzahl_verteidiger_vor += $unit_count;
        return 1;
    }

    public function decrase_angreifer($angriffspunkte)
    {
        if ($this->anzahl_angreifer_vor <= 0)
        {
            return;
        }

        shuffle($this->angreifer);
        foreach ($this->angreifer as $name => $value)
        {

            $vp = $this->angreifer[$name]['vert'];
            if ($vp > 0)
            {
                $this->angreifer[$name]['vert'] -= $angriffspunkte;
                $this->trace_ks("angreifer id: " . $this->angreifer[$name]['unitid'] . " getroffen: vp alt:$vp neu :" . $this->
                    angreifer[$name]['vert'] . " AP:$angriffspunkte \n");

                if ($this->angreifer[$name]['vert'] <= 0)
                {
                    $this->anzahl_angreifer_end++;
                    return;
                }

                return;
            }

        }
    }

    public function decrase_verteidiger($angriffspunkte)
    {
        if ($this->anzahl_verteidiger <= 0)
        {
            return;
        }
        shuffle($this->verteidiger);
        foreach ($this->verteidiger as $name => $value)
        {

            $vp = $this->verteidiger[$name]['vert'];
            if ($vp > 0)
            {
                $this->verteidiger[$name]['vert'] -= $angriffspunkte;
                $this->trace_ks("verteidiger id: " . $this->verteidiger[$name]['unitid'] . " getroffen: vp alt:$vp neu :" . $this->
                    verteidiger[$name]['vert'] . " AP:$angriffspunkte \n");

                if ($this->verteidiger[$name]['vert'] <= 0)
                {
                    $this->anzahl_verteidiger_end++;

                    return;
                }

                return;
            }

        }
    }


    public function generate_kampf_angreifer()
    {
        $this->trace_ks("angreifer schie�t\n");

        if ($this->anzahl_angreifer_vor <= 0)
        {
            return;
        }
        foreach ($this->angreifer_org as $name => $value)
        {
            $ap = $this->angreifer_org[$name]['angr'];
            $this->decrase_verteidiger($ap);

        }
    }


    public function generate_kampf_verteidiger()
    {
        $this->trace_ks("verteidiger schie�t\n");
        if ($this->anzahl_verteidiger_vor <= 0)
        {
            return;
        }


        $this->trace_ks("anzahl_verteidiger" . $this->anzahl_verteidiger . " \n");
        if ($this->anzahl_verteidiger <= 0)
        {
            return;
        }


        foreach ($this->verteidiger_org as $name => $value)
        {
            $ap = $this->verteidiger_org[$name]['angr'];
            $this->decrase_angreifer($ap);

        }

    }

    public function generate_looses()
    {
        $this->anzahl_angreifer_end = 0;
        $this->anzahl_angreifer = 0;
        $this->anzahl_verteidiger_end = 0;
        $this->anzahl_verteidiger = 0;
        $this->trace_ks("-------------------\n");
        $this->trace_ks("generate_looses angreifer\n");
        if ($this->anzahl_angreifer_vor > 0)
        {
            foreach ($this->angreifer as $name => $value)
            {
                $ap_single = $this->angreifer[$name]['ap_single'];
                $vp_single = $this->angreifer[$name]['vp_single'];
                $units_count = $this->angreifer[$name]['unit_count'];
                $u_id = $this->angreifer[$name]['unitid'];
                $ap = $this->angreifer[$name]['angr'];
                $vp = $this->angreifer[$name]['vert'];
                $new_units = ceil($vp / $vp_single);
                $this->angreifer[$name]['new_units_count'] = $new_units;
                $unit_type = $this->angreifer[$name]['unit_type'];
                $this->anzahl_angreifer += $units_count;
                $this->trace_ks("generate_looses $unit_type angreifer id:$u_id ($ap,$vp) $ap_single,$vp_single..$units_count anzahl_new:$new_units\n");
                if ($new_units > 0)
                {

                    $this->anzahl_angreifer_end += $new_units;
                }
                $this->trace_ks("angreifer_nach kampf all: " . $this->anzahl_angreifer_end . "\n");
            }
        }

        $this->trace_ks("generate_looses verteidiger\n");
        if ($this->anzahl_verteidiger_vor > 0)
        {
            foreach ($this->verteidiger as $name => $value)
            {
                $ap_single = $this->verteidiger[$name]['ap_single'];
                $vp_single = $this->verteidiger[$name]['vp_single'];
                $units_count = $this->verteidiger[$name]['unit_count'];
                $u_id = $this->verteidiger[$name]['unitid'];
                $ap = $this->verteidiger[$name]['angr'];
                $vp = $this->verteidiger[$name]['vert'];
                $new_units = ceil($vp / $vp_single);
                $this->verteidiger[$name]['new_units_count'] = $new_units;
                $unit_type = $this->verteidiger[$name]['unit_type'];
                $this->anzahl_verteidiger += $units_count;
                $this->trace_ks("generate_looses $unit_type verteidiger id:$u_id ($ap,$vp) $ap_single,$vp_single..$units_count anzahl_new:$new_units\n");
                if ($new_units > 0)
                {
                    $this->anzahl_verteidiger_end += $new_units;
                }

            }
        }
    }


    public function calc()
    {
        $this->write_units();
        $this->trace_ks(" starte berechnungen\n");
        $this->generate_kampf_angreifer();
        $this->generate_kampf_verteidiger();
        $this->generate_looses();
        $this->close_file();
    }
    public function close_file()
    {
        if ($this->trace_on == 0)
        {
            return;
        }
        fwrite($this->trace_file, $this->all_trace);
        fclose($this->trace_file);
    }

    public function write_units()
    {
        if ($this->trace_on == 0)
        {
            return;
        }

        $this->trace_file = fopen($this->trace_filename, "w+");
        $curtime = date("d.m.Y H:i:s", time());
        fwrite($this->trace_file, "Datum," . $curtime . "\n");
        fwrite($this->trace_file, "Agreifer Units\n");

        if ($this->anzahl_angreifer_vor > 0)
        {
            foreach ($this->angreifer as $name => $value)
            {
                $t_ang_stark = $this->angreifer[$name]["vert"];
                $t_ang_vert = $this->angreifer[$name]["angr"];
                $t_ang_name = $this->angreifer[$name]["unit"];
                $t_ang_units_count = $this->angreifer[$name]["unit_count"];
                $t_ap_single = $this->angreifer[$name]['ap_single'];
                $t_vp_single = $this->angreifer[$name]['vp_single'];
                $t_all = "$t_ang_name ($t_ang_units_count) AP:$t_ang_vert  VP:$t_ang_stark single($t_ap_single,$t_vp_single)";
                fwrite($this->trace_file, $t_all . "\n");
            }
        }
        else
        {
            fwrite($this->trace_file, "AngreiferUnit = NULL\n");
        }
        fwrite($this->trace_file, "Verteidiger Units\n");
        if ($this->anzahl_verteidiger_vor > 0)
        {
            foreach ($this->verteidiger as $name => $value)
            {
                $t_ang_stark = $this->verteidiger[$name]["vert"];
                $t_ang_vert = $this->verteidiger[$name]["angr"];
                $t_ang_name = $this->verteidiger[$name]["unit"];
                $t_ang_units_count = $this->verteidiger[$name]["unit_count"];
                $t_ap_single = $this->verteidiger[$name]['ap_single'];
                $t_vp_single = $this->verteidiger[$name]['vp_single'];
                $t_all = "$t_ang_name ($t_ang_units_count) AP:$t_ang_vert  VP:$t_ang_stark single($t_ap_single,$t_vp_single)";
                fwrite($this->trace_file, $t_all . "\n");
            }
        }
        else
        {
            fwrite($this->trace_file, "VerteidigerUnit = NULL\n");
        }


    }


    public function trace_ks($msg)
    {
        //echo("DEBUG: $msg <br>");
        $this->all_trace .= "$msg";
    }

}

?>