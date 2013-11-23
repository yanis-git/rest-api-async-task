<?php
require __DIR__."/../library/ActiveRecord.php";
@require_once "Mail.php";

define("BASEPATH", realpath(__DIR__."/../"));
$connections = include(BASEPATH."/config/database.php");


// initialize ActiveRecord
ActiveRecord\Config::initialize(function($cfg) use ($connections)
{
    $cfg->set_model_directory(__DIR__."/../models");
    $cfg->set_connections($connections);
});

function sprintf_array($format, $arr) { 
    return call_user_func_array('sprintf', array_merge((array)$format, $arr)); 
}

$commandes = Command::find("all", array('conditions' => 'exec_at IS NULL'));

foreach ($commandes as $key => $commande) {	
	$paramsPrintf = array();
	$paramsPrintf[] = escapeshellarg($commande->command);

	$patternPrintf = "%s ";
	foreach ($commande->parameter as $parameter) {
		$patternPrintf .= "%s %s ";
		$paramsPrintf[] = escapeshellarg($parameter->name);
		$paramsPrintf[] = (empty($parameter->value))?$parameter->default_value:$parameter->value;
	}
	// $patternPrintf .= " > /dev/null 2>&1"; // retourne la sortie standard vers un trou noir.
	//echo sprintf_array($patternPrintf,$paramsPrintf)."\n";
	exec(sprintf_array($patternPrintf,$paramsPrintf));
	$commande->set_attributes(
		array(
			'exec_at' => new DateTime("NOW")
		)
	);
	$commande->save();
}