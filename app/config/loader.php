<?php
/*
 * Package CMS
 * Owner: 
 * Author: Sebastian Rzeszowicz
 * Date: 2015
 *
 * Segment: Global
 * Object: Loader
*/

$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->registerDirs(
	array(
		APP_PATH . $config->application->controllersDir,
		APP_PATH . $config->application->pluginsDir,
		APP_PATH . $config->application->componentsDir,
		APP_PATH . $config->application->modelsDir,
		APP_PATH . $config->application->formsDir
	)
)->register();
/**
 * Registering global constants 
 */

foreach( $constant->label as $key => $label ) { //labels
	define( "LABEL_" . strtoupper( $key ), $label );
}

foreach( $constant->title as $key => $label ) { //labels
	define( "TITLE_" . strtoupper( $key ), $label );
}