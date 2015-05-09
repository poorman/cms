<?php
/**
 * Package CMS
 * Syntax Phalcon 2.0
 * Owner: system-work.com
 * Author: Sebastian Rzeszowicz
 * Date: 5/5/2015
 *
 * Segment: Form
 * Object: Files
*/

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text as inputText;
use Phalcon\Forms\Element\Email as inputEmail;
use Phalcon\Forms\Element\Password as inputPassword;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Email;

class FileForms extends Form
{

	/**
	 * Initialize the files form
	 */
	public function initialize( $entity = null, $options = [] )
	{

	}
}