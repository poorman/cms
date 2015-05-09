<?php
/* * Package CMS
 * Syntax Phalcon 2.0
 * Owner: system-work.com
 * Author: Sebastian Rzeszowicz
 * Date: 5/5/2015
 *
 * Segment: Controller
 * Object: Errors
*/

/**
 * From here we will control all error views
 */	
class ErrorsController extends BaseController
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
		// Errors global title
		// This title will operate unless,
		//other error title will override it with its own
		$this->tag->setTitle( MSG_ERROR );
		
		parent::initialize();
	}

	/**
	 * Show error 404
	 *
	 * @Param void
	 *
	 * @Return void
	*/
	public function show404Action()
	{
	}

	/**
	 * Show error 401
	 *
	 * @Param void
	 *
	 * @Return void
	*/
	public function show401Action()
	{
	}

	/**
	 * Show error 500
	 *
	 * @Param void
	 *
	 * @Return void
	*/
	public function show500Action()
	{
	}
}
