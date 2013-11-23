<?php

class Command extends ActiveRecord\Model
{
	// explicit table name since our table is not "books"
	static $table_name = 'command';

	// explicit pk since our pk is not "id"
	static $primary_key = 'id';

	// explicit connection name since we always want production with this model
	static $connection = 'production';

	// explicit database name will generate sql like so => db.table_name
	static $db = 'webapp_api';

	static $has_many = array(
		array('parameter')
   	);
}