<?php
/**
 * Package CMS
 * Syntax Phalcon 2.0
 * Owner: system-work.com
 * Author: Sebastian Rzeszowicz
 * Date: 5/5/2015
 *
 * Segment: Controller
 * Object: Accounts
*/

use Phalcon\Forms\Form,
	Phalcon\Forms\Element\Text,
	Phalcon\Forms\Element\Select,
	Phalcon\Forms\Element\Hidden,
	Phalcon\Mvc\Model\Query;

/**
 * From here we control all accounts related call actions
 */
class AccountController extends BaseController
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
		// Accounts global title
		// This title will operate unless,
		// other account title will override it with its own
		$this->tag->setTitle( TITLE_ACCOUNT_LIST );
		
		
	}

	/**
	 * Deleting account, dictated by account id
	 *
	 * @Param int account id
	 * 
	 * @Return void
	 */
	public function deleteAction( $account_id = false )
	{
		if ( $account_id ) {
			// Remove account
			
			// Instantiate Account Model
			$account = new Account;
			
			// Attempt account removal
			if ( $result = $account->deleteAccount( $account_id ) ) {
				// account removed successfuly
				$this->flash->success( MSG_ACCOUNT_REMOVED );
				
				// Remove account group relation
				// Instantiate AccountGroup Model
				$account_group = new AccountGroup;
				
				// Attempt relation removal
				if ( $account_group = $account_group->deleteAccountGroup( $account_id ) ) {
					// Relation removed successfuly
					$this->flash->success( MSG_GROUP_RELATIONSHIP_REMOVED );
				}
				else {
					// Relation removal failed
					foreach ( $account_group->getMessages() as $message ) {
						// Display error messages
						$this->flash->error( ( string ) $message );
					}
				}
			}
			else {
				// Account removal failed
				// Display error message
				$this->flash->error( MSG_ACCOUNT_NOT_REMOVED );
			}
		}
		else {
			// No account submited for removal
			$this->flash->error( MSG_ACCOUNT_DOES_NOT_EXIST );
		}
		// Back to accounts list
		return $this->forward( 'account/index' );
	}

	/**
	 * Account edit form view generator
	 *
	 * @Param int account id
	 * 
	 * @Return void
	 */
	public function editAction( $account_id = false )
	{
		// Instantiate Account Model
		$account = new Account;
		
		// initiate form input rules
		$form = new AccountForms;
		
		// Override title
		$this->tag->setTitle( TITLE_PRE . ' | ' . TITLE_EDIT_ACCOUNT );
		
		// Set view assets
		$this->assets->collection( 'userFormCss' )
			->addCss( 'css/userForms.css' );
		
		// Send data to view
		$this->view->setVars( [
			"account" => $account->findFirst( $account_id ),
			"access" => $this->access,
			"form" => $form,
			"id" => $this->Oauth[ 'id' ],
			"role" => $this->Oauth[ 'role' ],
			"account_group_id" => $this->Oauth[ 'account_group_id' ],
			"account_id" => $account_id
		] );
	}

	/**
	 * Account index view generator
	 * Shows list of account available for the user
	 *
	 * @Param void
	 * 
	 * @Return void
	 */
	public function indexAction( $limit = false, $offset = false )
	{
		// Instantiate Account Model
		$account = new Account;

		// fetch account for this user
		$limit = ( !$limit ) ? $this->limit : $limit;
		$account_list = $account->accountList( $this, $limit, $offset );
		$pagination = [
							"total_count" => count( $account->accountList( $this ) ),
							"button_count" => 5,
							"offset" => $offset,
							"limit" => $limit,
							"startEnd" => 0
							];
		// Set view assets
		$this->assets->collection( 'tablesCss' )
			->addCss( 'css/tables.css' );
		$this->assets->collection( 'confirmDeleteJs' )
			->addJs( 'js/modal/confirmDelete.js' );
		// send data to view
		$this->view->setVars( [
			"pagination" => $pagination,
			"access" => $this->access,
			"id" => $this->Oauth[ 'id' ],
			"role" => $this->Oauth[ 'role' ],
			"account_group_id" => $this->Oauth[ 'account_group_id' ],
			"account" => $account_list
		] );
	}
	
	/**
	 * New Account form view generator
	 *
	 * @Param int group account id
	 * @Param int parent id
	 * @Param string redirect as url/controller/action/parameters
	 *
	 * @Return void
	 */
	public function newAction( $account_group_id = 0, $parent_id = false, $redirect = false )
	{
		// Initiate form input rules
		$form = new AccountForms;
		
		// Set view assets
		$this->assets->collection( 'userFormCss' )
			->addCss( 'css/userForms.css' );
		
		// Override title
		$this->tag->setTitle( TITLE_PRE . ' | ' . TITLE_NEW_ACCOUNT );

		// Set parent_id
		$parent_id = ( $parent_id ) ? $parent_id : $this->Oauth[ 'id' ];

		// Set form hidden fields
		$form->add( new Hidden( 'account_group_id', [ 'value' => $account_group_id ] ) );
		$form->add( new Hidden( 'parent_id',[ 'value' => $parent_id ] ) );
		
		if ( $redirect ) {
			// Add redirect value if required 
			// this will happen when user comes from other control than account, so after this prosess we return user to previous point
			$form->add( new Hidden( 'redirect', [ 'value' => $redirect ] ) );
		}
		
		// Send data to view
		$this->view->setVars( [
			"form" => $form,
			"redirect" => $redirect
		] );
	}

	/**
	 * New Account insert or update, dictated by account id
	 *
	 * @Param int account id
	 *
	 * @Return void
	 */
	public function saveAction( $account_id = false )
	{
		// Instantiate Account Model
		$account = new Account;

		if ( $this->request->isPost() ) {
			
			// redirect as url/controller/action/parameters, to return user after save to previous view
			$redirect = $this->request->getPost( 'redirect' );
			
			if ( !$account_id ) {
				// New Account
				// Insert account and get id
				$account_id = $account_save = $account->saveAccount( $this ); 
			
				if ( is_array( $account_save ) ) {
					// Save failed
					// display error messages
					foreach ( $account_save as $message ) {
						$this->flash->error( ( string ) $message );
					}

					// Go back to form
					return $this->forward( 'account/new/' . $this->request->getPost( 'account_group_id' ) . '/' . $this->request->getPost( 'parent_id' ) . '/' . $redirect );
				}
				else {
					// Account saved successfuly
					// display success message
					$this->flash->success( MSG_NEW_ACCOUNT_CREATED );

					// Create relation
					// Instantiate AccountGroup Model
					$account_group = new AccountGroup;

					// save account account group relation
					$account_group_save = $account_group->saveAccountGroup( $this, $account_id );

					if ( is_array( $account_group_save ) ) {
						// Save failed
						// display error messages
						foreach ( $account_group_save as $message ) {
							$this->flash->error( ( string ) $message );
						}

						// Entire process flunked by latest attempt, so remove just added account
						if ( $account_deleted = $account->deleteAccount( $account_id ) ) {
							// Acount removal successful
							// display success message
							$this->flash->success( MSG_ACCOUNT_REMOVED );
						}
						else {
							// Account removal failed
							// display error message
							$this->flash->error( MSG_ACCOUNT_NOT_REMOVED );
						}

						// Go back to form
						return $this->forward( 'account/new/' . $this->request->getPost( 'account_group_id' ) . '/' . $this->request->getPost( 'parent_id' ) . '/' . $redirect );
						
					}
					else {
						// display success message
						$this->flash->success( MSG_NEW_GROUP_RELATIONSHIP_CREATED );
					}
				}
			}
			else {
				// Existing account update
				if ( $account_save = $account->updateAccount( $this, $account_id ) ) {

					//display success message
					$this->flash->success( MSG_ACCOUNT_UPDATED_SUCCESSFULY );
				} else {

					foreach ( $account_save->getMessages() as $message ) {
						// display error messages
						$this->flash->error( ( string ) $message );
					}
				}
			}
		}
		else {
			// No data posted
			$this->flash->notice( MSG_NO_DATA );
		}

		// By this point all if any error messages are rendered
		if ( $redirect ) {
			// this action was not trigerred from account so lets return where we came from
			return $this->forward( $redirect . '/index' );
		}
		else {
			return $this->forward( 'account/index' );
		}
	}
}