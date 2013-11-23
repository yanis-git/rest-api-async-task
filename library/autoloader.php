<?php
class autoloader {

    public static $loader = null;
    public $flag = false;

    public static function init()
    {
        if (self::$loader == NULL)
            self::$loader = new self();

        return self::$loader;
    }
    public function defineIncludePath(){
        // echo "<pre>";
        //     var_dump(get_include_path()) ;
        // echo "</pre>";
        if(!$this->flag){
            set_include_path(implode(PATH_SEPARATOR, array(
                realpath('../library'),realpath('../controllers'),get_include_path(),
            )));
            $this->flag = true;
        }
    }

    public function __construct()
    {
        // spl_autoload_register(array($this,'model'));
        // spl_autoload_register(array($this,'helper'));

        spl_autoload_register(array($this,'controller'));
        spl_autoload_register(array($this,'library'));
    }

    public function library($class)
    {
        $this->defineIncludePath();
        spl_autoload_extensions('.php');
        spl_autoload($class);
    }

    public function controller($class)
    {
        $class = str_replace("Controller", "", ucfirst($class));
        $this->defineIncludePath();
        spl_autoload_extensions('.php');
        spl_autoload($class);
    }

    public function model($class)
    {
        $class = preg_replace('/_model$/ui','',$class);
        set_include_path(get_include_path().PATH_SEPARATOR.'/models/');
        spl_autoload_extensions('.model.php');
        spl_autoload($class);
    }

    public function helper($class)
    {
        $class = preg_replace('/_helper$/ui','',$class);

        set_include_path(get_include_path().PATH_SEPARATOR.'/helper/');
        spl_autoload_extensions('.helper.php');
        spl_autoload($class);
    }

}

//call
autoloader::init();
?>