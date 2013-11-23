<?php
// return array(
// 	"/" => "homeController"
// );


return  array(
	array(
		"route" => "/",
		"Controller" => "HomeController",
		"params" => array(),
		"description" => "Lorem Ipsum"
	),
	array(
		"route" => "/command/?([0-9]*)/([a-zA-Z0-9+/]+={0,2}$)",
		"Controller" => "CommandController",
		"params" => array("id"),
		"description" => "Lorem Ipsum"
	),
);