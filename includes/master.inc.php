<?php

	// Application flag
	define('CLYDEPHP', true);
	
	// Determine our absolute document root
	define('DOC_ROOT', dirname(realpath(__FILE__)).'/../');
	
	// Determine core spf-ng path
	define('CLYDEPHP_ROOT', realpath(dirname(__FILE__)));
	
	define('CLYDEPHP_VENDOR', DOC_ROOT.'vendor/');
	
	define('APP_ROOT', dirname(realpath($_SERVER['SCRIPT_FILENAME'])).'/');
	
	// Global include files
	require CLYDEPHP_ROOT . '/beans.inc.php';
	require CLYDEPHP_ROOT . '/core.inc.php';
	require CLYDEPHP_ROOT . '/functions.inc.php';
	require CLYDEPHP_ROOT . '/compat.inc.php'; //some emulated function for php 5.1
	require CLYDEPHP_ROOT . '/class.string.php'; //some emulated function for php 5.1
	require CLYDEPHP_ROOT . '/class.classloader.php';  // __autoload() is contained in this file
	
	//register autoload function
	spl_autoload_register('ClassLoader::autoload');
	
	//add framework dir to classpath
	addClasspath(CLYDEPHP_ROOT);
	
	// Fix magic quotes
	if(get_magic_quotes_gpc())
	{
		$_POST    = String::fixSlashes($_POST);
		$_GET     = String::fixSlashes($_GET);
		$_REQUEST = String::fixSlashes($_REQUEST);
		$_COOKIE  = String::fixSlashes($_COOKIE);
	}
	
	//enable html exception handler
	if (!isset($_ENV['SHELL']))
		set_exception_handler('clydePhpExceptionHandler');
	
	//if exists a file with custom boostrap
	//include it
	if (is_file(APP_ROOT.'/bootstrap.inc.php')) {
		require APP_ROOT.'/bootstrap.inc.php';
	}

