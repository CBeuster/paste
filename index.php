<?php
ini_set('default_charset', 'utf-8');

spl_autoload_register(function($class) {
	include('./classes/'.$class.'.php');
});

Config::sql_connect();

/**
 * welcome
 */

Bob::get('/', function() {
	View::add('header');
	View::add('editor');
	View::add('footer');
});

/**
 * ui
 */

Bob::get('/help', function() {
	header('location: '.Config::path('base').'/svwiujlxh5kf');
	exit();
});

Bob::get('/:paste', function($paste) {
	View::add('header');

	if($paste = Paste::get($paste))
		View::add('paste', ['paste' => array_merge($paste, ['text' => Paste::get_text($paste['file'])])]);
	else View::add('error', ['code' => 404]);

	View::add('footer');
});

Bob::get('/fork/:paste', function($paste) {
	View::add('header');

	if($paste = Paste::get($paste))
		View::add('fork', ['paste' => array_merge($paste, ['text' => Paste::get_text($paste['file'])])]);
	else View::add('error', ['code' => 404]);

	View::add('footer');
});

Bob::get('/delete/:paste/:paste', function($token, $key) {
	View::add('header');

	if(Paste::delete($token, $key))
		View::add('delete');
	else View::add('error', ['code' => 403]);

	View::add('footer');
});

Bob::get('/recent', function() {
	View::add('header');

	$pastes = Paste::get_num(20);

	#foreach($pastes as $i => $paste)
	#	$pastes[$i] = array_merge($paste, ['text' => Paste::get_text($paste['file'])]);

	View::add('recent', ['pastes' => $pastes]);

	View::add('footer');
});

Bob::post('/add', function() {
	if(isset($_POST['brobdingnagian']) and $_POST['brobdingnagian'] == '')
		if(isset($_POST['text']) and !empty($_POST['text']))
			if($token = Paste::save($_POST['text'], (isset($_POST['parent'])) ? $_POST['parent'] : '', (isset($_POST['hidden'])) ? $_POST['hidden'] : ''))
				header('location: '.Config::path('base').'/'.$token);
			else header('location: '.Config::path('base').'/');
		else header('location: '.Config::path('base').'/');
	else header('location: '.Config::path('base').'/');

	exit();
});

/**
 * api
 */

Bob::get('/raw/:paste', function($paste) {
	if($paste = Paste::get($paste))
		View::add('raw', ['paste' => array_merge($paste, ['text' => Paste::get_text($paste['file'])])]);
	else header('Status: 404 Not Found');
});

Bob::get('/info/:paste', function($paste) {
	header('Content-type: application/json');

	if($paste = get_paste($paste))
		echo json_encode($paste);
	else header('Status: 404 Not Found');
});

Bob::get('/recent/:is_numeric', function($num) {
	header('Content-type: application/json');

	if($num <= 100) {
		$pasts = [];

		foreach(Paste::get_num($num) as $paste)
			$pasts[] = get_paste($paste['token']);

		echo json_encode($pasts);
	} else header('Status: 404 Not Found');
});

/**
 * here you go
 */

Bob::go(Config::path('base'));

/**
 * default 404
 */

Bob::notfound(function() {
	View::add('header');
	View::add('error', ['code' => 404]);
	View::add('footer');
});

/**
 * receive paste and parents
 */

function get_paste($token) {
	if($paste = Paste::get($token)) {
		$data = [
			'date'   => $paste['date'],
			'token'  => $paste['token'],
			'hidden' => ($paste['hidden'] == 'true'),
			'url'    => Config::path('url').Config::path('base').'/'.$paste['token'],
			'raw'    => Config::path('url').Config::path('base').'/raw/'.$paste['token'],
			'json'   => Config::path('url').Config::path('base').'/info/'.$paste['token']
		];

		if($paste['parent'] != '' and $parent = get_paste($paste['parent']))
			$data['parent'] = $parent;

		return $data;
	} else return false;
}

/**
 * custom selectorzZ
 */

function paste($paste) {
	return preg_match('/^[a-z0-9].{11}$/i', $paste) or is_numeric($paste);
}
