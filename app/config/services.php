<?php
/*
 * Package CMS
 * Syntax Phalcon 2.0
 * Owner: system-work.com 
 * Author: Sebastian Rzeszowicz
 * Date: 5/5/2015
 *
 * Segment: Global
 * Object: Services
*/

use Phalcon\DI\FactoryDefault;
use Phalcon\Http\Response;
use Phalcon\Http\Request;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Db\Adapter\Pdo\Mysql as DbConn;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Mvc\Model\Metadata\Memory as MetaData;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Flash\Session as FlashSession;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\Model\MetaData\Apc;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Router;

//Dependency Injector
$di = new Phalcon\DI\FactoryDefault();

/**
 * We register the events manager
 */
$di->set( 'dispatcher', function() use ( $di ) {

	$eventsManager = new EventsManager;

	/**
	 * Check if the user is allowed to access certain action using the SecurityPlugin
	 */
	$eventsManager->attach( 'dispatch:beforeDispatch', new SecurityPlugin );

	/**
	 * Handle exceptions and not-found exceptions using NotFoundPlugin
	 */
	$eventsManager->attach( 'dispatch:beforeException', new NotFoundPlugin );

	$dispatcher = new Dispatcher;
	$dispatcher->setEventsManager( $eventsManager );
	return $dispatcher;
});

/**
 *Database connection
 */
$di->set( 'db', function() use ($config) {
    return new DbConn( [
        "host"		=> $config->database->host,
        "username"	=> $config->database->username,
        "password"	=> $config->database->password,
        "dbname"	=> $config->database->name
    ] );
});

/**
 * Register the flash service with custom CSS classes
 */
$di->set( 'flash', function() {
	return new FlashSession( [
		'error'		=> 'alert alert-danger',
		'success'	=> 'alert alert-success',
		'notice'	=> 'alert alert-info',
	] );
});

/**
 * Start the session the first time some component request the session service
 */
$di->set( 'session', function() {
$session = new SessionAdapter();
$session->start();
return $session;
});

//Setup a base URI so that all generated URIs include the "tutorial" folder
$di->set( 'url', function() {
	$url = new UrlProvider();
	$url->setBaseUri( '/' );
	return $url;
});

/**
 * Register a user component
 */
$di->set( 'menus', function() {
	return new Menus();
});

$di->set( 'modelsManager', function() {
	return new \Phalcon\Mvc\Model\Manager();
});

/**
 * Setup the view component
 */
$di->set ( 'view', function() use ( $config ){
	$view = new View();
	$view->setViewsDir( APP_PATH . $config->application->viewsDir );
	return $view;
});