<?php

class HomeController{
	public function get(){
		//Liste toutes les méthodes disponibles pour l'api.
		return include(BASEPATH."/config/route.php");
	}

	public function post(){
		throw new RouterException("Action non autorisé", 405);
	}

	public function put(){
		throw new RouterException("Action non autorisé", 405);
	}

	public function delete(){
		throw new RouterException("Action non autorisé", 405);
	}

}