<?php

class CommandController{

	static public $command_ref = array(
				"command_1" => array(
					"id" => 1,
					"description" => "Lorem Ipsum",
					"parameter" => array("id" => 'integer', 'parameters' => "string encodé en base64 sous la forme : --params;-para izipizi"),
				),
				"command_4" => array(
					"id" => 4,
					"description" => "Convertisseur d'image pour les différents formats/assets attendu par la webapp.",
					"parameter" => array("document_id" => 'integer', 'parameters' => "string encodé en base64 sous la forme : <ID>"),
				),
				"command_5" => array(
					"id" => 5,
					"description" => "Lorem Ipsum",
					"parameter" => array("document_id" => 'integer',"original_width / 2" ,"original_width / 8" , 'parameters' => "string encodé en base64 sous la forme : width;width"),
				),
			);

	// Allowed Command

	static public $command = array(
		1 => array("command" => "ls"), // LWFsbCAvQXBwbGljYXRpb25zL01BTVAvaHRkb2NzLw==
		2 => array("command" => "du"),
		3 => array("command" => "ls"),
		4 => array('command' => "/root/generator/webapp/converttoimg/convert.sh"),
		5 => array('command' => "/root/generator/webapp/initmobile.sh")
	);

	// Début de l'implémentation.

	public function get($id = null,$mail, $parameters){
		$params = explode(";",base64_decode($parameters));
		$arrayParams = array();
		$i = 0;

		foreach ($params as $param) {
			$param = trim($param);
			$tab = explode(" ", $param);
			$arrayParams[$i]["name"] = $tab[0];
			if(!empty($tab[1])){
				$arrayParams[$i]["value"] = $tab[1];
				$arrayParams[$i]["default_value"] = null;
			}
			else{
				$arrayParams[$i]["value"] = null;
				$arrayParams[$i]["default_value"] = null;
			}

			$i++;
		}

		if(empty($id)){
			return self::$command_ref;
		}
		else if(!isset(self::$command_ref["command_".$id])){
			throw new RouterException("Paramètre ID éronné.", 400);
		}
		else{
			return self::$command_ref["command_".$id];
		}
	}

	public function post($id = null, $parameters){
		if(empty($id))
			throw new RouterException("Paramètre ID manquant.", 400);
		
		if(isset($_POST["parameters"]) and base64_decode($_POST["parameters"],true)){
			throw new RouterException("Paramètre 'parameter' n'est pas en base64.", 400);
		}

		$t = explode("/",$parameters);
		$mail = $t[0];
		$parameters = $t[1];

		//TODO : ajouter à la pile.
		$parameters = $this->decodeParameters($parameters);
		$mail = base64_decode($mail);

		$commande = new Command();

		$commande->set_attributes(array(
			"command" 	 => self::$command[$id]["command"],
			"email" => $mail,
			"created_at" => new DateTime('NOW'),
			"exec_at" => NULL//new DateTime('NOW')
		));

		$commande->save();
		foreach ($parameters as $parameter) {
			$commande->create_parameter($parameter);
		}
		$commande->create_parameter(array("name" => $mail, "value" => "", "default_value" => ""));

		return $parameters;
	}

	public function decodeParameters($parameters){

		$params = explode(";",base64_decode($parameters));
		$arrayParams = array();
		$i = 0;

		foreach ($params as $param) {
			$param = trim($param);
			$tab = explode(" ", $param);
			$arrayParams[$i]["name"] = $tab[0];
			if(!empty($tab[1])){
				$arrayParams[$i]["value"] = $tab[1];
				$arrayParams[$i]["default_value"] = "";
			}
			else{
				$arrayParams[$i]["value"] = "";
				$arrayParams[$i]["default_value"] = "";
			}

			$i++;
		}

		return $arrayParams;
	}
}