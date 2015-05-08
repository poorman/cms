<?php
/* * Package CMS
 * Syntax Phalcon 2.0
 * Owner: system-work.com
 * Author: Sebastian Rzeszowicz
 * Date: 5/5/2015
 *
 * Segment: Controller
 * Object: Index
*/

/**
 * Here we generate start settings
 */
class IndexController extends BaseController
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
		// Index global title
		// This title will operate unless,
		//other index title will override it with its own
		$this->tag->setTitle( TITLE_WELCOME );
		parent::initialize();
	}

	/**
	 * Display index page
	 *
	 * @Param void
	 *
	 * @Return void
	 **/
	public function indexAction()
	{

	}
}