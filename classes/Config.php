<?php
class Config {
	public static $paths = [
		'url'    => 'http://foo.bar',
		'base'   => '',
		'index'  => '/index.php',
		'views'  => 'views/',
		'assets' => '/assets/',
		'pastes' => '/var/www/paste/pastes/'
	];

	public static $mysql = [
		'host'   => '',
		'user'   => '',
		'pass'   => '',
		'db'     => ''
	];

	# leave this empty to use mysql
	# be sure to put this in a save location
	public static $sqlite = 'pasts.db';

	public static $table = 'paste';

	public static $db;

	public static function path($path) {
		return static::$paths[$path];
	}

	public static function sql_connect() {
		if(!empty(static::$sqlite))
			static::$db = new PDO('sqlite:'.static::$sqlite);
		else static::$db = new PDO('mysql:host='.static::$mysql['host'].';dbname='.static::$mysql['db'], static::$mysql['user'], static::$mysql['pass']);

		$results = static::$db->query('SHOW TABLES LIKE `'.static::$table.'`');

		if(!$results or $results->rowCount() > 0)
			static::initialize();
	}

	private static function initialize() {
		if(!empty(static::$sqlite))
			static::$db->query('
				CREATE TABLE `'.static::$table.'` (
					`id` integer NOT NULL PRIMARY KEY AUTOINCREMENT,
					`hidden` text(6) NOT NULL,
					`date` text(64) NOT NULL,
					`token` text(12) NOT NULL,
					`key` text(12) NOT NULL,
					`parent` text(12) NOT NULL,
					`file` text(12) NOT NULL
				);');
		else
			static::$db->query('
				CREATE TABLE `'.static::$table.'` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`date` varchar(64) NOT NULL,
					`token` varchar(12) NOT NULL,
					`key` varchar(12) NOT NULL,
					`parent` varchar(12) NOT NULL,
					`hidden` varchar(6) NOT NULL,
					`file` varchar(12) NOT NULL,
					PRIMARY KEY (`id`)
				);');
	}
}
