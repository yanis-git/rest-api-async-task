<?php

require __DIR__."/../library/autoloader.php";
require __DIR__."/../library/ActiveRecord.php";
require __DIR__."/../library/Toro.php"; //FIX : l'autoloader n'arrive pas à trouver ToroHook, pour éviter de modifier la lib, on créer une dépendance en dure :/

define("BASEPATH", realpath(__DIR__."/../"));

$connections = include(BASEPATH."/config/database.php");
// initialize ActiveRecord
ActiveRecord\Config::initialize(function($cfg) use ($connections)
{
    $cfg->set_model_directory(__DIR__."/../models");
    $cfg->set_connections($connections);
});

$discovered_handler = "ErrorController";
if(!class_exists($discovered_handler)){
	$discovered_handler = str_replace("Controller", "", ucfirst($discovered_handler));
    require_once $discovered_handler.".php";
}
ToroHook::add("404",  function() {
	$controller = new ErrorController();
	$controller->error404Action();
});

ToroHook::add("500",  function() {
	$controller = new ErrorController();
	$controller->error500Action();
});

// var_dump(RouteConfig::prepare());exit;
Toro::serve(RouteConfig::prepare());