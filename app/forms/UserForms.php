<?php
/**
 * Package CMS
 * Syntax Phalcon 2.0
 * Owner: system-work.com
 * Author: Sebastian Rzeszowicz
 * Date: 5/5/2015
 *
 * Segment: Form
 * Object: User
*/

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text as inputText;
use Phalcon\Forms\Element\Email as inputEmail;
use Phalcon\Forms\Element\Password as inputPassword;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Email;

class UserForms extends Form
{
	protected $password_min_length = 4;
	protected $password_max_length = 36;
	protected $username_min_length = 4;
	protected $username_max_length = 36;
	/**
	 * Initialize the users form
	 */
	public function initialize( $entity = null, $options = [] )
	{

		if ( !isset( $options[ 'edit' ] ) ) {
			$element = new inputText( "id" );
			$this->add( $element->setLabel( "Id" ) );
		} else {
			$this->add( new Hidden( "id" ) );
		}
		$this->add(new Hidden( "parent_id" ) );

		$name = new inputText( "name", [ "required" => true, "class" => "form-control", "placeholder" => "Name" ] );
		$name->setLabel( "Name" );
		$name->setFilters( [ 'striptags', 'string' ] );
		$name->addValidators( [ new PresenceOf( [ 'message' => MSG_NAME_REQUIRED ] ) ] );
		$this->add( $name );


		$username = new inputText( "username", [ "required" => true, "class" => "form-control", "placeholder" => "Username", "minlength" => $this->username_min_length, "maxlength" => $this->username_max_length ] );
		$username->setLabel( "Username" );
		$username->setFilters( [ 'striptags', 'string' ] );
		$username->addValidators( [ new PresenceOf( [ 'message' => MSG_USERNAME_REQUIRED ] ) ] );
		$this->add( $username );

		$email = new inputEmail( "email", [ "required" => true, "class" => "form-control", "placeholder" => "Email address", "value" => "" ] );
		$email->setLabel( "Email address" );
		$email->setFilters( [ 'striptags', 'string' ] );
		$email->addValidators( [ new PresenceOf( [ 'message' => MSG_EMAIL_REQUIRED ] ) ] );
		$this->add( $email );
		
		$password = new inputPassword( "password", [ "required" => true, "class" => "form-control", "placeholder" => "Password", "minlength" => $this->password_min_length, "maxlength" => $this->password_max_length, "value" => "" ] );
		$password->setLabel( "Password" );
		$password->setFilters( [ 'striptags', 'string' ] );
		$password->addValidators( [ new PresenceOf( [ 'message' => MSG_PASSWORD_REQUIRED ] ) ] );
		$this->add( $password );
		
		$repeatPassword = new inputPassword( "repeatPassword", [ "required" => true, "class" => "form-control", "placeholder" => "Repeat Password", "minlength" => $this->password_min_length, "maxlength" => $this->password_max_length ] );
		$repeatPassword->setLabel( "Repeat Password" );
		$repeatPassword->setFilters( [ 'striptags', 'string' ] );
		$repeatPassword->addValidators( [
			new PresenceOf( [ 'message' => MSG_REPEAT_PASSWORD_REQUIRED ] ) ] );
		$this->add( $repeatPassword );
		
		$oldPassword = new inputPassword( "oldPassword", [ "required" => true, "class" => "form-control", "placeholder" => "Old Password" ] );
		$oldPassword->setLabel( "Old Password" );
		$oldPassword->setFilters( [ 'striptags', 'string' ] );
		$oldPassword->addValidators( [
			new PresenceOf( [ 'message' => MSG_OLD_PASSWORD_REQUIRED ] ) ] );
		$this->add( $oldPassword );
		
		$newPassword = new inputPassword( "newPassword", [ "required" => true, "class" => "form-control", "placeholder" => "New Password", "minlength" => $this->password_min_length, "maxlength" => $this->password_max_length ] );
		$newPassword->setLabel( "New Password" );
		$newPassword->setFilters( [ 'striptags', 'string' ] );
		$newPassword->addValidators( [
			new PresenceOf( [ 'message' => MSG_NEW_PASSWORD_REQUIRED ] ) ] );
		$this->add( $newPassword );
	}

}