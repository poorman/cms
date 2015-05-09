<?php
/**
 * Package CMS
 * Syntax Phalcon 2.0
 * Owner: system-work.com
 * Author: Sebastian Rzeszowicz
 * Date: 5/5/2015
 *
 * Segment: Form
 * Object: Account
*/

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text as inputText;
use Phalcon\Forms\Element\Password as inputPassword;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Validation\Validator\PresenceOf;

class AccountForms extends Form
{

	/**
	 * Initialize the users form
	 */
	public function initialize( $entity = null, $options = [] )
	{

		if ( !isset( $options[ 'edit' ] ) ) {
			$element = new inputText( "id" );
			$this->add($element->setLabel( "Id" ) );
		} else {
			$this->add(new Hidden( "id" ) );
		}
		
		$this->add( new Hidden( "secret" ) );
		
		$name = new inputText( "name", [ "required" => true, "class" => "form-control", "placeholder" => "Name" ] );
		$name->setLabel( "Name" );
		$name->setFilters( [ 'striptags', 'string' ] );
		$name->addValidators( [	new PresenceOf( [ 'message' => MSG_NAME_REQUIRED ] ) ] );
		$this->add($name);
	}
}