<?php

class Toro
{
    public static function serve($routes)
    {
        ToroHook::fire('before_request', compact('routes'));

        $request_method = strtolower($_SERVER['REQUEST_METHOD']);
        $path_info = '/';
        if (!empty($_SERVER['PATH_INFO'])) {
            $path_info = str_replace("public/", "",$_SERVER['PATH_INFO']);
        }
        else if (!empty($_SERVER['ORIG_PATH_INFO']) && $_SERVER['ORIG_PATH_INFO'] !== '/index.php') {
            $path_info = $_SERVER['ORIG_PATH_INFO'];
        }
        else {
            if (!empty($_SERVER['REQUEST_URI'])) {
                $path_info = (strpos($_SERVER['REQUEST_URI'], '?') > 0) ? strstr($_SERVER['REQUEST_URI'], '?', true) : $_SERVER['REQUEST_URI'];
            }
        }

//	echo $path_info;exit;

        $discovered_handler = null;
        $regex_matches = array();

        if (isset($routes[$path_info])) {
            $discovered_handler = $routes[$path_info];
        }
        else if ($routes) {
            $tokens = array(
                ':string' => '([a-zA-Z]+)',
                ':number' => '([0-9]+)',
                ':alpha'  => '([a-zA-Z0-9-_]+)'
            );
            foreach ($routes as $pattern => $handler_name) {
                $pattern = strtr($pattern, $tokens);
                if (preg_match('#^/?' . $pattern . '/?$#', $path_info, $matches)) {
                    $discovered_handler = $handler_name;
                    $regex_matches = $matches;
                    break;
                }
            }
        }
        $result = null;
        $handler_instance = null;
        if ($discovered_handler) {
            if (is_string($discovered_handler)) {
                if(!class_exists($discovered_handler)){
                    $ClassFile = str_replace("Controller", "", ucfirst($discovered_handler));
                    require_once $ClassFile.".php";
                }
                $handler_instance = new $discovered_handler();
            }
            elseif (is_callable($discovered_handler)) {
                $handler_instance = $discovered_handler();
            }
        }

        if ($handler_instance) {
            unset($regex_matches[0]);

            if (self::is_xhr_request() && method_exists($handler_instance, $request_method . '_xhr')) {
                header('Content-type: application/json; charset=utf-8');
                header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
                header('Cache-Control: no-store, no-cache, must-revalidate');
                header('Cache-Control: post-check=0, pre-check=0', false);
                header('Pragma: no-cache');
                $request_method .= '_xhr';
            }
            else{
                header('Content-type: application/json; charset=utf-8');
                header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
                header('Cache-Control: no-store, no-cache, must-revalidate');
                header('Cache-Control: post-check=0, pre-check=0', false);
                header('Pragma: no-cache');

            }

            if (method_exists($handler_instance, $request_method)) {
                ToroHook::fire('before_handler', compact('routes', 'discovered_handler', 'request_method', 'regex_matches'));
                try{
                    $result = call_user_func_array(array($handler_instance, $request_method), $regex_matches);
                    echo self::prepareJson($result);
                
                }catch(RouterException $e){
                    self::error($e);
                }

                ToroHook::fire('after_handler', compact('routes', 'discovered_handler', 'request_method', 'regex_matches', 'result'));
            }
            else {
                ToroHook::fire('404', compact('routes', 'discovered_handler', 'request_method', 'regex_matches'));
            }
        }
        else {
            ToroHook::fire('404', compact('routes', 'discovered_handler', 'request_method', 'regex_matches'));
        }

        ToroHook::fire('after_request', compact('routes', 'discovered_handler', 'request_method', 'regex_matches', 'result'));
    }

    private static function is_xhr_request()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    public static function error(RouterException $e){
        $formated_header = self::getHeaderStatutCode($e->getCode());
        header('Content-type: application/json; charset=utf-8');
        echo self::prepareJson(array(
            "codeErrorHttp" => $e->getCode(),
            "header"        => $formated_header,
            "message"       => $e->getMessage(),
            "origin"        => sprintf("Fichier : %s ligne : %s",$e->getFile(),$e->getLine())
        ));
        // exit;
    }

    public static function getHeaderStatutCode($statusCode) {
        $status_codes = array (
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            426 => 'Upgrade Required',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            509 => 'Bandwidth Limit Exceeded',
            510 => 'Not Extended'
        );

        if(!array_key_exists($statusCode, $status_codes)){
            $statusCode = 500;
        }
        
        $status_string = $statusCode . ' ' . $status_codes[$statusCode];
        header($_SERVER['SERVER_PROTOCOL'] . ' ' . $status_string, true, $statusCode);
        return $status_string;
    }

    public static function prepareJson($struct) {
        return preg_replace_callback("/\\\\u([a-f0-9]{4})/", function($m){ 
            return iconv('UCS-4LE','UTF-8',pack('V', hexdec('U'.$m[1])));
        },
        json_encode($struct));
    }
}

class ToroHook
{
    private static $instance;

    private $hooks = array();

    private function __construct() {}
    private function __clone() {}

    public static function add($hook_name, $fn)
    {
        $instance = self::get_instance();
        $instance->hooks[$hook_name][] = $fn;
    }

    public static function fire($hook_name, $params = null)
    {
        $instance = self::get_instance();
        if (isset($instance->hooks[$hook_name])) {
            foreach ($instance->hooks[$hook_name] as $fn) {
                call_user_func_array($fn, array(&$params));
            }
        }
    }

    public static function get_instance()
    {
        if (empty(self::$instance)) {
            self::$instance = new ToroHook();
        }
        return self::$instance;
    }
}


class RouterException extends Exception{

}


class RouteConfig{
    const CONFIGPATH = "/config/route.php";

    public static function prepare($configs = false){
        if(!$configs)
            $configs = include(BASEPATH.self::CONFIGPATH);

        $routes = array();

        foreach ($configs as $config) {
            $routes[$config["route"]] = $config["Controller"];
        }

        return $routes;
    }
}
