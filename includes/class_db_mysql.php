<?PHP

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

    var $sql_host = "";
    var $sql_user = "";
    var $sql_pass = "";
    var $sql_base = "";
    var $link_id = 0;
    var $sql_count = 0;

    public function __construct($host, $user, $pass, $base)
    {
        $this->sql_host = $host;
        $this->sql_user = $user;
        $this->sql_pass = $pass;
        $this->sql_base = $base;
        $this->connect();
    }

    public function connect()
    {
        try
        {
            $this->link_id = new PDO('mysql:host=' . $this->sql_host . ';dbname=' . $this->sql_base, $this->sql_user, $this->
                sql_pass, array(
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ));
        }
        catch (PDOException $e)
        {
            $this->link_id = null;
            $this->error("Es konnte keine Verbindung zum SQL Server hergestellt werden.<br>Error:" . $e->getMessage());
        }
    }

    public function select($sqlCode)
    {
        if ($this->link_id == null)
        {
            $this->warning("select >> Database is not Connected!");
            return null;
        }
        try
        {
            $result = $this->link_id->query($sqlCode);
            $this->sql_count++;
        }
        catch (PDOException $e)
        {
            $this->error("update >> PDOException >> " . $sqlCode . ' >> ' . $e->getMessage());
        }
        return $this->fetchArray($result);
    }

    public function selectAll($sqlCode)
    {
        if ($this->link_id == null)
        {
            $this->warning("selectAll >> Database is not Connected!");
            return null;
        }
        try
        {
            $result = $this->link_id->query($sqlCode);
            $this->sql_count++;
        }
        catch (PDOException $e)
        {
            $this->error("update >> PDOException >> " . $sqlCode . ' >> ' . $e->getMessage());
        }
        return $this->fetchArrayAll($result);
    }

    public function update($sqlCode)
    {
        if ($this->link_id == null)
        {
            $this->warning("update >> Database is not Connected!");
            return null;
        }
        try
        {
            $count = $this->link_id->exec($sqlCode);
            $this->sql_count++;
        }
        catch (PDOException $e)
        {
            $this->error("update >> PDOException >> " . $sqlCode . ' >> ' . $e->getMessage());
        }
        return $count;
    }

    public function insert($sqlCode)
    {
        if ($this->link_id == null)
        {
            $this->warning("insert >> Database is not Connected!");
            return null;
        }
        try
        {
            $count = $this->link_id->exec($sqlCode);
            $this->sql_count++;
        }
        catch (PDOException $e)
        {
            $this->error("insert >> PDOException >> " . $sqlCode . ' >> ' . $e->getMessage());
        }
        return $count;
    }

    public function replace($sqlCode)
    {
        if ($this->link_id == null)
        {
            $this->warning("replace >> Database is not Connected!");
            return null;
        }
        try
        {
            $count = $this->link_id->exec($sqlCode);
            $this->sql_count++;
        }
        catch (PDOException $e)
        {
            $this->error("replace >> PDOException >> " . $sqlCode . ' >> ' . $e->getMessage());
        }
        return $count;
    }

    public function delete($sqlCode)
    {
        if ($this->link_id == null)
        {
            $this->warning("delete >> Database is not Connected!");
            return null;
        }
        try
        {
            $count = $this->link_id->exec($sqlCode);
            $this->sql_count++;
        }
        catch (PDOException $e)
        {
            $this->error("delete >> PDOException >> " . $sqlCode . ' >> ' . $e->getMessage());
        }
        return $count;
    }

    public function multi_query($sqlCode)
    {
        if ($this->link_id == null)
        {
            $this->warning("multi_query >> Database is not Connected!");
            return null;
        }
        try
        {
            $count = $this->link_id->exec($sqlCode);
            $this->sql_count++;
        }
        catch (PDOException $e)
        {
            $this->error("multi_query >> PDOException >> " . $sqlCode . ' >> ' . $e->getMessage());
        }
        return $count;
    }

    public function query($sqlCode)
    {
        if ($this->link_id == null)
        {
            $this->warning("query >> Database is not Connected!");
            return null;
        }
        if (!startsWith($sqlCode, 'SELECT'))
        {
            $return = startsWithArray($sqlCode, array(
                'UPDATE',
                'INSERT',
                'REPLACE',
                'DELETE'));
            if ($return['UPDATE'] === true)
            {
                return $this->update($sqlCode);
            }
            elseif ($return['INSERT'] === true)
            {
                return $this->insert($sqlCode);
            }
            elseif ($return['REPLACE'] === true)
            {
                return $this->replace($sqlCode);
            }
            elseif ($return['DELETE'] === true)
            {
                return $this->delete($sqlCode);
            }
        }

        try
        {
            $result = $this->link_id->query($sqlCode);
            $this->sql_count++;
        }
        catch (PDOException $e)
        {
            $this->error("query >> PDOException >> " . $sqlCode . ' >> ' . $e->getMessage());
        }
        return $result;
    }

    public function fetchQuery($sqlCode)
    {
        $result = $this->query($sqlCode);
        if ($result === false)
        {
            $this->warning("fetchQuery >> Result is null!");
            return null;
        }
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchArray($result)
    {
        if ($result === false)
        {
            $this->warning("fetchArray >> Result is null!");
            return null;
        }
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchArrayAll($result)
    {
        if ($result === false)
        {
            $this->warning("fetchArrayAll >> Result is null!");
            return null;
        }
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchNum($result)
    {
        if ($result === false)
        {
            $this->warning("fetchNum >> Result is null!");
            return null;
        }
        return $result->fetch(PDO::FETCH_NUM);
    }

    public function fetchNumAll($result)
    {
        if ($result === false)
        {
            $this->warning("fetchNumAll >> Result is null!");
            return null;
        }
        return $result->fetchAll(PDO::FETCH_NUM);
    }

    public function quote($string)
    {
        if ($this->link_id == null)
        {
            $this->warning("insert_id >> Database is not Connected!");
            return null;
        }
        return $this->link_id->quote($string);
    }

    //Alias old Litotex

    public function fetch_array($result)
    {
        if ($result === false)
        {
            $this->warning("fetch_array >> Result is null!");
            return null;
        }
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    public function num_rows($result)
    {
        if ($result === false)
        {
            $this->warning("num_rows >> Result is null!");
            return null;
        }
        return $result->fetch(PDO::FETCH_NUM);
    }

    public function unbuffered_query($sqlCode)
    {
        if ($this->link_id == null)
        {
            $this->warning("insert_id >> Database is not Connected!");
            return null;
        }
        try
        {
            $count = $this->link_id->exec($sqlCode);
            $this->sql_count++;
        }
        catch (PDOException $e)
        {
            $this->error("unbuffered_query >> PDOException >> " . $sqlCode . ' >> ' . $e->getMessage());
        }
        return $count;
    }

    public function insert_id()
    {
        if ($this->link_id == null)
        {
            $this->warning("insert_id >> Database is not Connected!");
            return null;
        }
        return $this->link_id->lastInsertId();
    }

    public function escape_string($string)
    {
        return $this->quote($string);
    }

    public function number_of_querys()
    {
        return $this->sql_count;
    }


    public function error($error)
    {
        echo ("<title>Error by Base - Litotex</title>");
        echo ("Error: <b>$error</b><br>\n");
        echo ("Derzeit gibt es Datenbank Probleme, bitte haben Sie etwas gedult.");
        exit();
    }


    public function warning($error)
    {
        echo ("<title>Warning by Base - Litotex</title>");
        echo ("Warning: <b>$error</b><br>\n");
        echo ("Derzeit gibt es Datenbank Probleme, bitte haben Sie etwas gedult.");
    }

}
