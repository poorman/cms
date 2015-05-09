<?php
/**
 * Package CMS
 * Syntax Phalcon 2.0
 * Owner: system-work.com
 * Author: Sebastian Rzeszowicz
 * Date: 5/5/2015
 *
 * Segment: Controller
 * Object: User
*/

use Phalcon\Forms\Form,
	Phalcon\Forms\Element\Text,
	Phalcon\Forms\Element\Select,
	Phalcon\Forms\Element\Hidden,
	Phalcon\Mvc\Model\Query;

/**
 * From here we will control all user related actions
 */
class UserController extends BaseController
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
		parent::initialize();
		// User global title
		// This title will operate unless,
		//other user title will override it with its own
		$this->tag->setTitle( TITLE_USER_LIST );
	}
	
	/**
	 * Deleting User dictated by user_id
	 *
	 * @Param int user id
	 *
	 * @Return void
	 */
	public function deleteAction( $user_id=false )
	{
		// get user
		$user = User::findFirstById( $user_id );
		
		if ( $user->account_group_id && count( User::find( [ "account_group_id = " . $user->account_group_id ] ) ) == 1 ) {
			// this is last account group for this group
			// remove accounts, admin and users
			$account = new Account;
			$user = new User;
			$account_group = new AccountGroup;
			
			$account_ids = $account->accountGroupIds( $user->account_group_id );
			// remove associated users
			if ( count( $account_ids ) ) {
				if ( !$user->deleteAccountUsers( $account_ids ) ) {
					$this->flash->error( MSG_ERROR_DELETING_ASSOCIATED_USERS );
					// go back to users list
					return $this->forward( 'user/index' );
				}
				else {
					$this->flash->success( MSG_REMOVED_ASSOCIATED_USERS );
				}
								
				// Remove associated accounts
				if ( !$account->deleteAccountGroup( $this, $user->account_group_id, $account_ids ) ) {
					$this->flash->error( MSG_ERROR_DELETING_ASSOCIATED_ACCOUNTS );
					// go back to users list
					return $this->forward( 'user/index' );
				}
				else {
					$this->flash->success( MSG_REMOVED_ASSOCIATED_ACCOUNTS );
				}
				
			}
			// deactivate account group
			if ( !$account_group->deactivateAccountGroup( $user->account_group_id ) && count( $account_ids ) ) {
				$this->flash->error( MSG_ERROR_DEACTIVATING_ACCOUNT_GROUP );
				// go back to users list
				return $this->forward( 'user/index' );
			}
			else {
				 if ( $account_ids  ) { 
					$this->flash->success( MSG_ACCOUNT_GROUP_REMOVED );
				 }
			}
		}
		if ( $user->delete() ) {
			// user deleted
			$this->flash->success( MSG_USER_REMOVED );
		}
		else {
			// Error deleting user
			if ( $user === false ) {
				// Show generic error message if no formal messages
				$this->flash->error( MSG_ERROR_NON_EXISTENT_USER );
			}
			else {
				// Show formal messages
				foreach ( $user->getMessages() as $message ) {
					$this->flash->error( ( string ) $message );
				}
			}
		}

		// go back to users list
		return $this->forward( 'user/index' );
	}

	/**
	 * User editform view generator
	 * 
	 * @Param int user id
	 *
	 * @Return void
	 */
	public function editAction( $user_id = false )
	{
		// Set view assets
		$this->assets->collection( 'userFormCss' )
			->addCss( 'css/userForms.css' );
		$this->assets->collection( 'confirmDeleteJs' )
			->addJs( 'js/modal/confirmDelete.js' );
			
		// fetch available access levels
		// Instantiate Session Model
		$session = new Sessions;
		$access = $session->availableAccessList( $this );
		if ( $this->Oauth[ 'role' ] == ROLE_GROUP_ADMIN ) {
			// Reset label for group admins to co-group admins
			$access[ $this->access[ ROLE_GROUP_ADMIN ] ] = LABEL_COOP . '-' . ROLE_GROUP_ADMIN;
		}
		
		// Instantiate accounts Model
		$account = new Account;
		
		// fetch available records
		$accountSelect = $account->accountList( $this );
		
		// Initiate form input rules
		$form = new UserForms;

		if ( $accountSelect ) {
			// Set account dropdown
			$form->add( new Select( "account_id", $accountSelect, [ 'using' => [ 'id', 'name' ],'class' => 'form-control' ] ) );

		}
		else {
			// User (ie.Admin) does not have cross account permissions, set hidden value for current user account_id
			$form->add( new Hidden( "account_id", [ 'value'=>$this->Oauth[ 'account_id' ] ] ) );
		}
		
		$form->add( new Select( "userRole", $access, [ "class" => "form-control" ] ) );	

		// Send data to view
		$this->view->setVars( [
			"user" => User::findFirstById( $user_id ),
			"access" => $this->access,
			"form" => $form,
			"id" => $this->Oauth[ 'id' ],
			"role" => $this->Oauth[ 'role' ],
			"account_group_id" => $this->Oauth[ 'account_group_id' ],
			"account_id" => $this->Oauth[ 'account_id' ]
		] );
	}

	/**
	 * User index view generator
	 * 
	 * @Param int user id
	 *
	 * @Return void
	 */
	public function indexAction( $id = false )
	{
		// Instantiate User Model
		$user = new User;

		// Set view assets for all possible view options
		$this->assets->collection( 'confirmDeleteJs' )
			->addJs( 'js/modal/confirmDelete.js' );
			
		if ( $this->access[ $this->Oauth[ 'role' ] ] >= $this->access[ ROLE_ADMIN ] && !$id ) {
			//Administrative permissions
			// Set view assets fot this view option only
			$this->assets->collection( 'tablesCss' )
			->addCss( 'css/tables.css' );

			$account_group = new AccountGroup;
			$accounts = new Account;
			// Send data to view
			$this->view->setVars( [
			"users" => $users->userList( $this ),
			"access" => $this->access,
			"id" => $this->Oauth[ 'id' ],
			"role" => $this->Oauth[ 'role' ],
			"account_id" => $this->Oauth[ 'account_id' ],
			"account_groups" => $account_group,
			"accounts" => $accounts
			] );
		}
		else {
			// User only permissions, will show user edit view form
			$action = ( $id ) ? '/user/save/' . $id : '/user/save/' . $this->Oauth[ 'id' ];

			// Initiate form input rules
			$form = new UserForms;

			// Set view assets
			$this->assets->collection( 'userFormCss' )
			->addCss( 'css/userForms.css' );

			// Send data to view
			$this->view->setVars( [
			"user" => $users->findFirst($this->Oauth[ 'id' ]),
			"access" => $this->access,
			"id" => $this->Oauth[ 'id' ],
			"role" => $this->Oauth[ 'role' ],
			"account_id" => $this->Oauth[ 'account_id' ],
			"form" => $form,
			'action' => $action
			] );
		}
	}

	/**
	 * New User form generator
	 * 
	 * @Param int parent id
	 *
	 * @Return void
	 */
	public function newAction( $parent_id = false )
	{
		// Set view assets
		$this->assets->collection( 'userFormCss' )
			->addCss( 'css/userForms.css' );

		// who is creating this user
		if ( ! $parent_id ) {
			$parent_id = $this->Oauth[ 'id' ];
		}

		// fetch available access levels
		$session = new Sessions;
		$access = $session->availableAccessList( $this );
		if ( $this->Oauth[ 'role' ] == ROLE_GROUP_ADMIN ) {
			// Reset label for group admins to co-group admins
			$access[ $this->access[ ROLE_GROUP_ADMIN ] ] = LABEL_COOP . '-' . ROLE_GROUP_ADMIN;
		}

		// Instantiate account Model
		$account = new Account;

		// fetch available records
		$accountSelect = $account->accountList( $this );

		// Initiate form input rules
		$form = new UserForms;

		if ( $accountSelect ) {
			// Set account dropdown
			$form->add( new Select( "account_id", $accountSelect, [ 'using' => [ 'id', 'name' ],'class' => 'form-control' ] ) );

		}
		else {
			// User (ie.Admin) does not have cross account permissions, set hidden value for current user account_id
			$form->add( new Hidden( "account_id", [ 'value'=>$this->Oauth[ 'account_id' ] ] ) );
		}

		// Set new user role dropdown
		$form->add( new Select( "userRole", $access, [ "class" => "form-control" ] ) );

		// Send data to view
		$this->view->setVars( [
			"parent_id" => $parent_id,
			"account_id" => $this->Oauth[ 'account_id' ],
			"role" => $this->Oauth[ 'role' ],
			"form" => $form
		] );
	}

	/**
	 * Password reset form
	 * 
	 * @Param int user id
	 *
	 * @Return void
	 */
	public function passwordAction( $id = false )
	{
		// Set view assets
		$this->assets->collection( 'userFormCss' )
			->addCss( 'css/userForms.css' );

		if ( $id ) {
			// only users with permissions above account group can assume foreign user id and reset its password
			$id = ( $this->access[ $this->Oauth[ 'role' ] ] < $this->access[ ROLE_SUPER_ADMIN ] ) ? $id : $this->Oauth[ 'id' ];
		}
		else {
			// all other users get their own user id and reset only owned password
			$id = $this->Oauth[ 'id' ];
		}

		// Initiate form input rules
		$form = new UserForms;

		// Send data to view
		$this->view->setVars( [
			"access" => $this->access,
			"form" => $form,
			"id" => $id,
			"role" => $this->Oauth[ 'role' ]
		] );
	}
	
	/**
	 * New User insert or update, dictated by id
	 *
	 * @Param int user id
	 *
	 * @Return void
	 */
	public function saveAction( $id = false )
	{
		// Instantiate User Model
		$user = new User;

		if ( $this->request->isPost() ) {
			if ( !$id ) {
				// Inserting New user

				//Check if passwords match
				if ( ! $user->comparePasswords( $this ) ) {
					$this->flash->error( MSG_ERROR_PASSWORDS_NO_MATCH );
					return $this->forward( 'user/new' );
				}

				// Save user
				if ( is_array( $save = $user->insertUser( $this ) ) ){
					// errors saving new user
					foreach ( $save as $message ) {
						$this->flash->error( ( string ) $message );
					}
					// Back to new user form
					return $this->forward( 'user/new' );
				}
				else {
					// Save successful
					$this->flash->success( MSG_USER_CREATED );
				}
			}
			else {
				// Updating existing user
				if ( is_array( $update = $user->updateUser( $this, $id ) ) ) {
					// errors updating new user
					foreach ( $update as $message ) {
						$this->flash->error( ( string ) $message );
					}
					if ( $this->Oauth[ 'role' ] == $this->access[ ROLE_GROUP_ADMIN ] ) {
						// account groups go back to users list view
						return $this->forward( 'user/edit/' );
					}
					// users go back to edit view
					return $this->forward( 'user/edit/' . $id );
				}
				else {
					// User updated
					$this->flash->success( MSG_USER_UPDATED );
				}
			}
		}
		else {
			// No data posted
			$this->flash->notice( MSG_NO_DATA );
		}
		// By this point all if any error messages are rendered
		return $this->forward( 'user/index' );
	}

	/**
	 * New Password validate and save
	 * 
	 * @Param int user id
	 *
	 * @Return void
	 */
	public function savePasswordAction( $id )
	{
		if ($this->request->isPost() ) {
			// Instantiate User Model
			$user = new User();

			// Attempt reset
			if ( is_array( $reset = $user->resetPassword( $this, $id ) ) ) {
				// Erroreous attempt, display error messages
				foreach ( $reset as $message ) {
					$this->flash->error( ( string ) $message );
				}

				// Go back to reset form
				return $this->forward( 'user/password/' . $id );
			}
			else {
				// Reset successful
				$this->flash->success( MSG_PASSWORD_RESET );

				// Back to user(s)
				return $this->forward( 'user/index/' . $id );
			}
		}
		else {
			// No data posted
			$this->flash->notice( MSG_NO_DATA );
		}
		// By this point all if any error messages are rendered
		return $this->forward( 'user/index/' . $id );
	}
	
	/**
	 * User data view generator
	 * 
	 * @Param int user id
	 *
	 * @Return void
	 */
	public function showAction( $id )
	{
		// Set view assets
		$this->assets->collection( 'userFormCss' )
			->addCss( 'css/userForms.css' );

		// Initiate form input rules
		$form = new UserForms;

		// Send data to view
		$this->view->setVars( [
			"user" => User::findFirstById( $id ),
			"access" => $this->access,
			"id" => $this->Oauth[ 'id' ],
			"role" => $this->Oauth[ 'role' ],
			"form" => $form
		] );
	}
}