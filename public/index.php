<?php
/*
 * Package CMS
 * Owner: 
 * Author: Sebastian Rzeszowicz
 * Date: 2015
 *
 * Segment: Bootstrap
 * Object: Index.php
*/
error_reporting(E_ALL);
use Phalcon\Mvc\Application;
use Phalcon\Config\Adapter\Ini as ConfigIni;

try {
	$home_url = ( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
	define( 'HOME_PATH', $home_url );
	define( 'CONFIG_PATH', '../../_private/config/' );
	define( 'APP_PATH', realpath('..') . '/' );
	/**
	 * Read the configuration
	 */
	$config = new ConfigIni(CONFIG_PATH . 'config.ini');
	
	/**
	 * Read constant variables
	 */
	$constant = new ConfigIni(CONFIG_PATH . 'constants.ini');
	
	/**
	 * Auto-loader configuration
	 */
	require APP_PATH . 'app/config/loader.php';

	/**
	 * Load application services
	 */
	require APP_PATH . 'app/config/services.php';



	//Handle the request
	$application = new Application($di);

	echo $application->handle()->getContent();

} catch(\Phalcon\Exception $e) {
	echo "PhalconException: ", $e->getMessage();
}