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
class db
{

    private $sql_host = "";
    private $sql_user = "";
    private $sql_pass = "";
    private $sql_base = "";
    private $sql_port = "";
    private $link_id = null;
    private $sql_count = 0;

    public function __construct($host, $user, $pass, $base, $port = 3306)
    {
        $this->sql_host = $host;
        $this->sql_user = $user;
        $this->sql_pass = $pass;
        $this->sql_base = $base;
        $this->sql_port = $port;
    }

    public function connect()
    {
        $this->link_id = new mysqli($this->sql_host, $this->sql_user, $this->sql_pass, $this->sql_base, $this->sql_port);
        if ($this->link_id->connect_error)
        {
            $error = $this->link_id->connect_error;
            $this->link_id = null;
            return $error;
        }
        return true;
    }

    public function close()
    {
        if (is_null($this->link_id))
        {
            return false;
        }
        $this->link_id->close();
    }

    public function query($query_string)
    {
        if (is_null($this->link_id))
        {
            return false;
        }

        $selecting_query = $this->link_id->query($query_string);
        if (!$selecting_query)
        {
            $this->error("Datenbank abfrage konnte nicht durchgeführt werden:<br /> " . $query_string . '<br />Error:' . $this->link_id->
                error);
            return false;
        }
        $this->sql_count++;
        return $selecting_query;
    }

    public function multi_query($query_string)
    {
        if (is_null($this->link_id))
        {
            return false;
        }
        $results = array();
        if ($this->link_id->multi_query($query_string))
        {
            do
            {
                if ($query_result = $this->link_id->store_result())
                {
                    $result = array();
                    while ($row = $this->fetch_array($query_result))
                    {
                        $result[] = $row;
                    }
                    $results[] = $result;
                    $query_result->free();
                    $this->sql_count++;
                }
                else
                {
                    $results[] = false;
                }
            } while ($this->link_id->more_results() && $this->link_id->next_result());
        }
        else
        {
            $this->error("Datenbank abfrage konnte nicht durchgeführt werden: " . $query_string . '<br />Error:' . $this->link_id->
                error);

            return false;
        }
        if (count($results) > 0)
        {
            $_all_false = true;
            foreach ($results as $data)
            {
                if ($data != false)
                {
                    $_all_false = false;
                    break;
                }
            }

            if ($_all_false === true)
            {
                return false;
            }
        }
        return $results;
    }

    public function fetch_array($result_string)
    {
        if (is_null($this->link_id) || $result_string == false)
        {
            return false;
        }
        return $result_string->fetch_array();
    }

    public function num_rows($result_string)
    {
        if (is_null($this->link_id))
        {
            return false;
        }
        return $result_string->num_rows;
    }

    public function unbuffered_query($query_string)
    {
        if (is_null($this->link_id))
        {
            return false;
        }
        return $this->link_id->real_query($query_string);
    }

    public function insert_id()
    {
        if (is_null($this->link_id))
        {
            return false;
        }
        return $this->link_id->insert_id;
    }

    public function escape_string($string)
    {
        if (is_null($this->link_id))
        {
            return false;
        }
        return $this->link_id->escape_string($string);
    }

    public function number_of_querys()
    {
        return $this->sql_count;
    }


    public function error($error)
    {
        echo ("<title>Error by Base - Litotex</title>");
        echo ("Error: <b>" . $error . "</b><br>\n");
        echo ("SQL-Error: " . $this->link_id->error . "<br>\n");
        echo ("Derzeit gibt es Datenbank Probleme, bitte haben Sie etwas gedult.");
    }

}
