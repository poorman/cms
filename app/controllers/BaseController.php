<?php
/*
 * Package CMS
 * Syntax Phalcon 2.0
 * Owner: system-work.com
 * Author: Sebastian Rzeszowicz
 * Date: 5/5/2015
 *
 * Segment: Controller
 * Object: Base
*/

use \Phalcon\Assets\Filters\Cssmin;
use \Phalcon\Assets\Filters\Jsmin;

/**
 * Here we control all global aspects of application
 */	
class BaseController extends \Phalcon\Mvc\Controller
{
	/**
	 * Initialize objects global settings
	 *
	 * @Param void
	 *
	 * @Return void
	*/
	public function initialize()
	{
		//Set Global Title
		$this->tag->prependTitle( TITLE_PRE . ' | ' );
		//Set base template
		$this->view->setTemplateAfter( 'base' );

		//Minify Assets
		$this->assets
					->collection( 'style' )
					->addCss( 'assets/css/style.css' )
					->setTargetPath( 'cache/css/production.css' )
					->setTargetUri( 'cache/css/production.css' )
					->join( true )
					->addFilter( new Cssmin() );

		$this->assets
					->collection( 'script' )
					->addJs( 'third-party/js/jquery.min.js', false, false )
					->addJs( 'third-party/bootstrap.js/js/bootstrap.min.js', false, false )
					->addJs( 'assets/js/script.js' )
					->setTargetPath( 'cache/js/production.js' )
					->setTargetUri( 'cache/js/production.js' )
					->join( true )
					->addFilter( new Jsmin() );
	}

	/**
	 * Pre-Dispatcher
	 *
	 * @Param string uri
	 * 
	 * @Return void
	 */
	protected function forward($uri)
	{
		$uriParts = explode( '/', $uri );
		$params = array_slice( $uriParts, 2 );
		return $this->dispatcher->forward(
			array(
				'controller' => $uriParts[ 0 ],
				'action' => $uriParts[ 1 ],
				'params' => $params
			)
		);
	}
}