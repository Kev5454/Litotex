<?PHP

@session_start();

define("LITO_ROOT_PATH", dirname(dirname(dirname(__file__))) . '/');
define("LITO_SETUP_TEMP", LITO_ROOT_PATH . 'setup_tmp/');

$cur_pos = (int)$_REQUEST['id'];

if ($cur_pos == 0)
{

    $cacheDirs = array(
        'cache',
        'alli_flag',
        'backup',
        'battle_kr',
        'image_user',
        'images_sig',
        'images_tmp',
        'templates_c',
        'templates_c/standard',
        'acp/cache',
        'acp/templates_c',
        'acp/templates_c/standard',
        'acp/tmp',
        );

    foreach ($cacheDirs as $dirName)
    {
        $dirName = LITO_ROOT_PATH . $dirName . '/';
        if (!is_dir($dirName))
        {
            mkdir($dirName, 0777, true);
        }
        chmod($dirName, 0777);
    }

    $dirs = file(LITO_ROOT_PATH . 'dirlist.txt');
    foreach ($dirs as $dirName)
    {
        $dirname = str_replace("\n", "", LITO_ROOT_PATH . $dirName);
        if (!is_dir($dirname))
        {
            mkdir($dirname, 0775, true);
        }
    }
}

$inhalt = file(LITO_ROOT_PATH . 'filelist.txt');
$oldFileName = LITO_SETUP_TEMP . str_replace("\n", '', $inhalt[$cur_pos]);
$newFileName = LITO_ROOT_PATH . str_replace("\n", '', $inhalt[$cur_pos]);

echo ("installiere: " . str_replace(LITO_ROOT_PATH, '', $newFileName));

if (!copy($oldFileName, $newFileName))
{
    echo ("Error: " . str_replace(LITO_ROOT_PATH, '', $newFileName));
}
