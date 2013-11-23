<?php

/**
* ErrorController
*/
class ErrorController
{

	public function error404Action(){
		$this->setJsonHeader();
		Toro::error(new RouterException("Cette action n'est pas autorisé par le système.", 404));
	}

	public function error500Action(){

	}

	protected function setJsonHeader(){
        header('Content-type: application/json; charset=utf-8');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
	}
}