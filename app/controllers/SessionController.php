<?php
/**
 * Package CMS
 * Syntax Phalcon 2.0
 * Owner: system-work.com
 * Author: Sebastian Rzeszowicz
 * Date: 5/5/2015
 *
 * Segment: Controller
 * Object: Session
*/

/**
 * From here we will control all session actions
 */
class SessionController extends BaseController
{
	/**
	 *Initialize objects global settings
	 *
	 *@Param void
	 *
	 *@Return void
	*/
	public function initialize()
	{
		parent::initialize();
		// Session global title
		// This title will operate unless,
		//other title will override it with its own
		$this->tag->setTitle( TITLE_SIGN_UP_IN );
	}
	
	/**
	 * Logout - Finishes the active session redirecting to the index
	 *
	 * @Param void
	 *
	 * @Return void
	 */
	public function endAction()
	{
		// Set view assets
		$this->assets->collection( 'preloadersCss' )
			->addCss( 'css/preloaders.css' );
			
		$session = new Session;
		$session->stop( $this->Oauth[ 'session' ] );
		// Remove authorization token
		$this->session->remove( 'Oauth' );
		
		// Display Logout message
		$this->flash->success( MSG_YOU_LOGGEDOUT . '!' );
	}
	
	/*
	 * Displays login form
	 *
	 * @Param void
	 *
	 * @Return void
	 */
	public function indexAction()
	{
		if ( !$this->request->isPost() ) {
			// Set view assets
			$this->assets->collection( 'userFormCss' )
			->addCss( 'css/userForms.css' );

			// Initiate form input rules
			$form = new UserForms;

			// Send data to view
			$this->view->setVars( [ "form" => $form ] );
		}
	}

	/**
	 * This action authenticate and logs an user into the application
	 *
	 * @Param void
	 *
	 * @Return void
	 */
	public function startAction()
	{
		if ($this->request->isPost() ) {
			// Collect posted data
			$email = $this->request->getPost( 'email' );
			$password = $this->request->getPost( 'password' );
			
			// Find the user in the database
			// Attempt to locate Combination of email + password
			$user = User::findFirst( [
				"email = :email: AND password = :password: AND is_active = 1",
				"bind" => [ 'email' => $email, 'password' => md5( $password ) ]
			] );
			if ($user == false) {
				// Combination of email + user name failed
				// Attempt to locate Combination of username + password
				$user = User::findFirst( [
					"username = :username: AND password = :password: AND is_active = 1",
					"bind" => [ 'username' => $email, 'password' => md5( $password ) ]
				] );
			}
			if ( $user != false ) {
				// User located

				//Store current session
				$this->_registerSession( $user );

				// Display welcome message
				$this->flash->success( MSG_WELCOME . ' ' . $user->name );
				// Go to landing as logged in
				return $this->forward( 'index/index' );
			}

			// Login input has not matched anything
			// Set view assets
			$this->assets->collection( 'userFormCss' )
				->addCss( 'css/userForms.css' );

			// Display error message
			$this->flash->error( MSG_INVALID_LOGIN );

			// Initiate form input rules
			$form = new UserForms;

			// Send data to view
			$this->view->setVars( [ "form" => $form ] );
		}
		return $this->forward( 'session/index' );
	}

	/**
	 * Register an authenticated user into session data
	 *
	 * @Param Users user data
	 *
	 * @Return void
	 */
	private function _registerSession( User $user )
	{
		$session = new Sessions;
		
		// Prepare permissions list
		$access = array_flip ( $this->access );
		
		// Register Session token
		$this->session->set( 'Oauth', [
			'id'				=> $user->id,
			'name'				=> $user->name,
			'username'			=> $user->username,
			'email'				=> $user->email,
			'role'				=> $access[$user->role],
			'account_id'		=> $user->account_id,
			'account_group_id'	=> $user->account_group_id,
			'parent_id'			=> $user->parent_id,
			'session'			=> $session->start( $user->id )
		] );
	}
}