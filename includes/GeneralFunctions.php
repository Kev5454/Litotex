<?php

function startsWith($string, $searchString)
{
    // search backwards starting from haystack length characters from the end
    return $searchString === "" || strrpos($string, $searchString, -strlen($string)) !== false;
}

function endsWith($string, $searchString)
{
    // search forward starting from end minus needle length characters
    return $searchString === "" || (($temp = strlen($string) - strlen($searchString)) >= 0 && strpos($string, $searchString,
        $temp) !== false);
}

function startsWithArray($string, array $searchString)
{
    $return = array();
    foreach ($searchString as $search)
    {
        $return[$search] = ($search === "" || strrpos($string, $search, -strlen($string)) !== false);
    }
    return $return;
}

function endsWithArray($string, array $searchString)
{
    $return = array();
    foreach ($searchString as $search)
    {
        $return[$search] = ($search === "" || (($temp = strlen($string) - strlen($search)) >= 0 && strpos($string, $search, $temp)
            !== false));
    }
    return $return;
}

/**
 * Copy a file, or recursively copy a folder and its contents
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.0.1
 * @link        http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
 * @param       string   $source    Source path
 * @param       string   $dest      Destination path
 * @return      bool     Returns TRUE on success, FALSE on failure
 */
function copyr($source, $dest)
{
    // Simple copy for a file
    if (is_file($source))
    {
        return copy($source, $dest);
    }

    // Make destination directory
    if (!is_dir($dest))
    {
        mkdir($dest);
    }

    // Loop through the folder
    $dir = dir($source);
    while (false !== $entry = $dir->read())
    {
        // Skip pointers
        if ($entry == '.' || $entry == '..')
        {
            continue;
        }

        // Deep copy directories
        copyr("$source/$entry", "$dest/$entry");
    }

    // Clean up
    $dir->close();
    return true;
}

function _rmdir($src, $recursiv = true)
{
    if (endsWith($src, '/'))
    {
        $src = substr($src, 0, -1);
    }
    foreach (scandir($src) as $item)
    {
        if ($item == '.' || $item == '..')
        {
            continue;
        }
        $full = $src . '/' . $item;

        if (is_file($full) || is_link($full))
        {
            chmod($full, 0777);
            unlink($full);
        }
        elseif (is_dir($full))
        {
            if (!_rmdir($full))
            {
                chmod($full, 0777);
                if (!_rmdir($full))
                {
                    return false;
                }
            }
        }
    }
    return rmdir($src);
}

function _scandir($src, $_arrayFiles = array())
{
    if (endsWith($src, '/'))
    {
        $src = substr($src, 0, -1);
    }

    foreach (scandir($src) as $item)
    {
        if ($item == '.' || $item == '..')
        {
            continue;
        }
        $full = $src . '/' . $item;

        if (is_file($full) || is_link($full))
        {
            $_arrayFiles[] = $full;
        }
        elseif (is_dir($full))
        {
            $_arrayFiles = _scandir($full, $_arrayFiles);
        }
    }
    return $_arrayFiles;
}

function getBaseUrl()
{
    // output: /myproject/index.php
    $currentPath = $_SERVER['PHP_SELF'];

    // output: Array ( [dirname] => /myproject [basename] => index.php [extension] => php [filename] => index )
    $pathInfo = pathinfo($currentPath);

    // output: localhost
    $hostName = $_SERVER['HTTP_HOST'];

    // output: http://
    $protocol = (strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, 5)) == 'https://' ? 'https://' : 'http://');

    // return: http://localhost/myproject/
    if (substr($pathInfo['dirname'], -1) != '/')
    {
        $pathInfo['dirname'] = $pathInfo['dirname'] . '/';
    }
    return $protocol . $hostName . $pathInfo['dirname'];
}