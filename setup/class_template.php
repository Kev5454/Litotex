<?PHP

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