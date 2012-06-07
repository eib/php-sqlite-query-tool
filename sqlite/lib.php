<?php

function error_not_found() {
	header('HTTP/1.0 404 Not Found');
	echo <<<EOD
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>404 Not Found</title>
</head><body>
<h1>Not Found</h1>
<p>The requested resource was not found on this server.</p>
</body></html>
EOD;
	exit();
}
if (array_get($_SERVER, 'SCRIPT_FILENAME') == str_replace('\\', '/', __FILE__)) {
	error_not_found();
}

function no_cache_headers() {
	if (!headers_sent()) {
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1 
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	}
}

function array_get($array, $key, $default = NULL) {
	return (is_array($array) && array_key_exists($key, $array)) ? $array[$key] : $default;
}

function get_sqlite_connection($db_name, $parent_dir = '.') {
	$path = $parent_dir . '/' . basename($db_name);
	if (!string_contains($db_name, '.sdb')) {
		$path .= '.sdb';
	}
	$pdo = new PDO('sqlite:' . $path);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	return $pdo;
}

function pre_print_r() {
	echo "<pre>";
	foreach (func_get_args() as $arg) {
		print_r($arg);
	}
	echo "</pre>";
}

function string_contains($haystack, $needle, $case_sensitive = TRUE) {
	$position = $case_sensitive ? strpos($haystack, $needle) : stripos($haystack, $needle);
	return $position !== FALSE;
}
