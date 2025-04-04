<?php
spl_autoload_register(function ($classname) {
	if(empty($classname)) {
		throw new Exception('Class name is empty');
	}
	if (preg_match('/^Psr\\\/', $classname)) {
		$classname = 'PhpOffice/Psr/Psr';
	}
	else {
		$classname = str_replace('\\','/',$classname);
	}
	if(file_exists(__DIR__.'/class/'.$classname.'.php')) {
		require_once __DIR__.'/class/'.$classname.'.php';
		return;
	}

	throw new Exception('Can not load class : ['.$classname.']');
});

require_once 'lib/common.php';
require_once 'lib/sql_injection.php';
require_once str_replace('/_static','/',__DIR__).'_resources/config.php';
require_once str_replace('/_static','/',__DIR__).'_resources/config.table.php';
require_once str_replace('/_static','/',__DIR__).'_resources/func.php';
if(file_exists($_SERVER['DOCUMENT_ROOT'].'/../config.php')) {
	require_once $_SERVER['DOCUMENT_ROOT'].'/../config.php';
}
if(file_exists($_SERVER['DOCUMENT_ROOT'].'/../config.table.php')) {
	require_once $_SERVER['DOCUMENT_ROOT'].'/../config.table.php';
}
if(file_exists($_SERVER['DOCUMENT_ROOT'].'/../func.php')) {
	require_once $_SERVER['DOCUMENT_ROOT'].'/../func.php';
}
require_once 'language/'.LANGUAGE.'.php';
require_once 'controller/pre.php';
