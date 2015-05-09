<?php
/**
 * Package CMS
 * Syntax Phalcon 2.0
 * Owner: system-work.com
 * Author: Sebastian Rzeszowicz
 * Date: 5/5/2015
 *
 * Segment: Model
 * Object: User
*/

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Validator\Email as EmailValidator;
use Phalcon\Mvc\Model\Validator\Uniqueness as UniquenessValidator;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
/**
 * From here we will control all user actions
 */
class User extends Model
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var integer
     */
    public $account_id;
	
	 /**
     * @var integer
     */
    public $role;

	/**
     * @var string
     */
    public $username;

	/**
     * @var string
     */
    public $password;

	/**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $email;

    /**
     * @var boolean
     */
    public $is_active;

	/**
     * @var integer
     */
    public $parent_id;

    /**
     * @var integer
     */
    public $account_group_id;

	public function deleteAccountUsers( $accounts ) {
		$accounts =  ( is_array( $accounts ) ) ? implode( ',', $accounts ) : $accounts;
		$query = "
			DELETE FROM
				`user`
			WHERE
				`account_id` IN (" . $accounts . ")
			";
		return new Resultset( NULL, $this, $this->getReadConnection()->query( $query ) );
	}

	/**
	 * Deleting user by ud
	 *
	 *
	 * @Param int user id
	 *
	 * @Return bool
	 **/
	public function deleteUser( $user_id )
	{
		$user = User::findFirstById( $user_id );
		if ( $user ) {
			return $user->delete();
		}
		else {
			return false;
		}
	}

	/**
	 * Compare password with repeated password
	 *
	 * @Param obj
	 *
	 * @Return bool
	 **/
	
	public function comparePasswords( $requester )
	{
		if ( ( $password = $requester->request->getPost( 'password' ) )!= ( $repeatPassword = $requester->request->getPost( 'repeatPassword' ) ) ) {
			return false;
		}
		else
			return $password;
	}

	/**
	 * Creates new user
	 *
	 * @Param obj
	 *
	 * @Return bool || array
	 **/
	public function insertUser( $obj )
	{
		$user = new User();
		// Assign posted data
		$user->role = $obj->request->getPost( 'userRole' );
		$user->username = $obj->request->getPost( 'username' );
		$user->password = md5($obj->request->getPost( 'password' ) );
		$user->name = $obj->request->getPost( 'name' );
		$user->email = $obj->request->getPost( 'email' );
		$user->created = new Phalcon\Db\RawValue( 'now()' );
		$user->updated = new Phalcon\Db\RawValue( 'now()' );
		$user->is_active = ( $obj->request->getPost( 'is_active' ) ) ? 1 : 0 ;

		if ( $obj->request->getPost( 'userRole' ) == $obj->access[ROLE_GROUP_ADMIN] ){
			// New user is group admin
			$user->account_id = 0;

			// Assign group account id
			if ( $obj->Oauth[ 'account_group_id' ] ) {

			//Account Group inserts new co-group admin with same account group id
				$user->account_group_id = $obj->Oauth[ 'account_group_id' ];
			}
			else {
				// Dev or group admin inserts new account group
				$new_account_group_id = $user->newAccountGroupId();
				$new_account_group_id++;
				$user->account_group_id = $new_account_group_id;
			}

			// New user gets marked with creators id
			$user->parent_id = $obj->Oauth[ 'id' ];
		}
		else {
			if ( $obj->request->getPost( 'userRole' ) > $obj->access[ ROLE_GROUP_ADMIN ] ) {
				$user->account_id = NULL;
				$user->account_group_id = 0;
				$user->parent_id = $obj->request->getPost( 'parent_id' );
			}
			else {
			// New user is non-group admin
			if ( !$user->account_id = $obj->request->getPost( 'account_id' ) ) {
					return [ MSG_MUST_ASSIGN_ACCOUNT ];
			}
			$user->account_group_id = 0;
			$user->parent_id = $obj->request->getPost( 'parent_id' );
			}
		}
		//Save and return result
		if ( $user->save() ) {
			return true;
		}
		else {
			return $user->getMessages();
		}
	}

	/**
	 * Setting up new account group id
	 *
	 * @Param void
	 *
	 * @Return int new account group id
	 **/
	public function newAccountGroupId()
	{
		$user = new User();
		$result = $user->findFirst( [ "order" => "account_group_id DESC", "limit" => 1 ] );
		return $result->account_group_id;
	}

	/**
	 * Resetting password
	 *
	 * @Param obj
	 * @Param int
	 *
	 * @Return bool || aray
	 **/
	public function resetPassword( $obj, $id )
	{
		if ( ( $this->password = $obj->request->getPost( 'newPassword' ) )!= $obj->request->getPost( 'repeatPassword' ) ) {
			// New password and repeated password not matching
			return [ MSG_ERROR_PASSWORDS_NO_MATCH ];
			//return $this->forward( 'user/password' );
		}
		$user = User::findFirst( $id );
		
		if ( md5( $obj->request->getPost( 'oldPassword' ) ) != $user->password ) {
			// Current password not matching
			return [ MSG_ERROR_WRONG_PASSWORD ];
			//return $this->forward( 'user/password' );
		}
		
		//
		$user->password = md5($this->password);
		$user->updated = new Phalcon\Db\RawValue( 'now()' );
		if ( $user->update() == false ) {
			return $user->getMessages();
		}
		return true;
	}

	/**
	 * Fetching collection of users
	 *
	 * @Access admin and above
	 *
	 * @Param void
	 *
	 * @Return array
	 **/
	public function userList( $obj )
	{
		//fetch only associated users
		switch( $obj->Oauth[ 'account_id' ] ) {
			case NULL: // Super Admin and Developer
					return $this->find();
				 break;
			case 0: //Group Admin
					//Fetch all account group accounts
					$account_group = new AccountGroup;
					$group_accounts = $account_group->find( [ "account_group_id = " . $obj->Oauth[ 'account_group_id' ] ] );
					
					if ( count( $group_accounts ) ) {
						$accounts = implode( ',', $group_accounts );
						
						$query = "SELECT * FROM `user` WHERE `account_id` IN (" . $accounts . ") OR `account_group_id` = " . $obj->Oauth[ 'account_group_id' ] . " ORDER BY `account_id` ASC";
						return new Resultset( NULL, $this, $this->getReadConnection()->query( $query ) );
					}
					else {
						$query = "SELECT * FROM `user` WHERE `account_group_id` = " . $obj->Oauth[ 'account_group_id' ];
						return new Resultset( NULL, $this, $this->getReadConnection()->query( $query ) );
					}
			break;
		default:
				//Admins
				return $this->find( [ "account_id = " . $obj->Oauth[ 'account_id' ] ] );
			break;
		}
	}
	
	
	/**
	 * Updating user
	 *
	 * @Param obj
	 * @Param int
	 *
	 * @Return bool
	 **/
	public function updateUser( $obj, $id )
	{
		$user = User::findFirst( $id );
		if ( $obj->request->getPost( 'userRole' ) ) {
			$user->role = $obj->request->getPost( 'userRole' );
		}
		
		if ( $obj->request->getPost( 'userRole' ) == $obj->access[ ROLE_GROUP_ADMIN ] ){
			// Special data for accounts group
			$user->account_id = 0;
			$user->account_group_id = $obj->Oauth[ 'account_group_id' ];
			$user->parent_id = ( $id != $obj->Oauth[ 'id' ] ) ? $obj->Oauth[ 'id' ] : $obj->Oauth[ 'parent_id' ];
		}
		if ( $obj->request->getPost( 'userRole' ) < $obj->access[ ROLE_GROUP_ADMIN ] ){
			if ( !$user->account_id = $obj->request->getPost( 'account_id' ) ) {
				return [ MSG_MUST_ASSIGN_ACCOUNT ];
			}
		}
		$is_active = ( $obj->request->getPost( 'is_active' ) ) ? 1 : 0 ;
		// Owner cant change its active status
		if ( $obj->Oauth[ 'id' ] == $id ) {
			//He must be active since he got allowed here
			$is_active = 1;
		}
		
		
		( $obj->request->getPost( 'is_active' ) ) ? 1 : 0 ;
		// assign data
		$user->name = $obj->request->getPost( 'name' );
		$user->email = $obj->request->getPost( 'email' );
		$user->username = $obj->request->getPost( 'username' );
		$user->is_active = $is_active;
		$user->updated = new Phalcon\Db\RawValue( 'now()' );
		//Update and return result
		if ( $user->update() ) {
			return true;
		}
		else {
			return $user->getMessages();
		}
	}

	/**
	 * Validate new user data
	 *
	 * @Param void
	 *
	 * @Return bool
	 **/
	public function validation()
	{
		$this->validate( new EmailValidator( [ 'field' => 'email' ] ) );
		$this->validate( new UniquenessValidator( [ 'field' => 'email',	'message' => MSG_ERROR_DUPLICATE_EMAIL ] ) );
		$this->validate( new UniquenessValidator( [	'field' => 'username', 'message' => MSG_ERROR_DUPLICATE_USERNAME ] ) );
		
		if ( $this->validationHasFailed() == true ) {
			return false;
		}
	}
}
