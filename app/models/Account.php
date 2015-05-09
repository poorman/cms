<?php
/**
 * Package CMS
 * Syntax Phalcon 2.0
 * Owner: system-work.com
 * Author: Sebastian Rzeszowicz
 * Date: 5/5/2015
 *
 * Segment: Model
 * Object: Account
*/

/**
 * From here we will control all Accounts actions
 */
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Validator\Uniqueness as UniquenessValidator;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

class Account extends Model
{
	/**
	 * @var integer
	 * Account identification number
	 */
	public $id;

	/**
	 * @var string
	 * Account name
	 */
	public $name;

	/**
	 * @var boolean
	 * Flag for whether Account is active on not
	 */
	public $is_active;

	/**
	 * @var integer
	 * User id of whoever created this account
	 */
	public $parent_id;

	/**
	 * @var datetime
	 * Created date
	 */
	public $created;

	/**
	 * @var datetime
	 * Updated date
	 */
	public $updated;
	
	/**
	 * @var datetime
	 * Deleted date
	 */
	public $deleted;



	/**
	 * fetch child ids recuring
	 *
	 * @Param int account id
	 *
	 * @Return bool || array
	 **/
	public function associatedChildenIds( $account_id, $include = false )
	{
		$ids = [];
		if ( !$include ) {
			$ids[] = $account_id;
		}
		if ( $account_id ) {
			$children = $this->find( [ "parent_id = " . $account_id ] );
			foreach ( $children as $child ) {
				$ids[] = $child->id;
				$grandchild_ids = $this->associatedChildenIds( $child->id, true );
				foreach( $grandchild_ids as $gId ) {
					$ids[] = $gId;
				}
			}
		}
		return $ids;
	}

	/**
	 * Deleting Account
	 *
	 * @Param int account id
	 *
	 * @Return bool || array
	 **/
	public function deleteAccount( $account_id )
	{
		$account = $this->findFirst( $account_id );
		if ( $account ) {
			$user = User;
			//get all associated child account ids
			$accounts = $this->associatedChildenIds( $account_id, false );
			
			// Attempt removing users and admins for this group of accounts
			if ( ! $user->deleteAccountUsers( $accounts ) ) {
				return [ MSG_ERROR_DELETING_ASSOCIATED_USERS ];
			}
			// Remove Child accountss
			if ( !$this->deleteAccounts( $in_accounts ) ) {
				return [ MSG_ERROR_DELETING_ASSOCIATED_ACCOUNTS ] ;
			}
			// Removing requested account
			return  $account->delete();
		}
		return false;
	}
	
	function deleteAccounts( $accounts ) {
		$accounts = ( is_array( $accounts ) ) ? implode( ',', $accounts ) : $accounts;
		$query = "
			DELETE FROM
				`account`
			WHERE
				`id` IN (" . $accounts . ")
			";
		return new Resultset( NULL, $this, $this->getReadConnection()->query( $query ) );
	}
	
	/**
	 * Deleting all Account Group Accounts
	 *
	 * @Param int account id
	 *
	 * @Return bool || array
	 **/
	public function deleteAccountGroup( $obj, $account_group_id, $accounts = false )
	{
		if ( !$accounts ) {
			$accounts = $this->accountGroupIds( $account_group_id , true);
		}
		return $this->deleteAccounts( $accounts );
	}
	
	/**
	 * Fetching collection of accounts
	 *
	 * @Param object
	 *
	 * @Return array
	 **/
	public function accountList( $obj, $limit = false, $offset = false)
	{
		// pagination sql addon
		if ( $offset ) {
			$limit = " LIMIT " . $limit . "," . $offset;
		}
		else {
			$limit ='';
		}

		//fetch only records associated with current user
		switch( $obj->Oauth[ 'role' ] ) {
			case ROLE_DEVELOPER : 
			case ROLE_SUPER_ADMIN :
				// Devs and Super Admins permitted to all accounts
					//return $this->find();
					$query = "SELECT * FROM `account` ORDER BY `id` ASC " . $limit;
					return new Resultset( NULL, $this, $this->getReadConnection()->query( $query ) );
				break;
			case ROLE_GROUP_ADMIN :
					// Group Admins permited only to owned accounts
					$accounts = $this->accountGroupIds( $obj->Oauth[ 'account_group_id' ] );
					$accounts = ( is_array( $accounts ) ) ? implode( ',', $accounts ) : $accounts;
					$query = "SELECT * FROM `account` WHERE `id` IN (" . $accounts . ") ORDER BY `id` ASC" . $limit;
					return new Resultset( NULL, $this, $this->getReadConnection()->query( $query ) );
				break;
			case ROLE_ADMIN :
				// Admins permitted to single account
					$query = "SELECT * FROM `account` WHERE id = " . $obj->Oauth[ 'account_id' ];
					return new Resultset( NULL, $this, $this->getReadConnection()->query( $query ) );
				break;
			default: 
					return false;
				break;
		}
	}

	public function accountGroupIds( $account_group_id = false , $children = false ) {
		$account_group_id = ( $account_group_id ) ? $account_group_id : $obj->Oauth[ 'account_group_id' ];
		// Account Groups permited only to owned accounts
		$accounts = AccountGroup::find( [ "account_group_id = " . $group_id ] );
		
		if ( count( $accounts ) ) {
			$ids = [];
			foreach( $accounts as $account ) {
				$ids[] = $account->account_id;
				if ( $children ) {
					$family = $this->associatedChildenIds( $account->account_id, false );
					foreach ( $family as $child ) {
						if ( !in_array($child, $ids) ) {
							$ids[] = $child;
						}
					}
				}
			}
			return $ids;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Inserting new account
	 *
	 * @Param void
	 *
	 * @Return array || int
	 **/
	public function saveAccount( $obj )
	{	
		$this->name = $obj->request->getPost( 'name' );
		$this->account_group_id = $obj->request->getPost( 'account_group_id' );
		$this->is_active = ($obj->request->getPost( 'active' ) ) ? 1 : 0;
		$this->parent_id = ( $obj->request->getPost( 'parent_id' ) ) ? $obj->request->getPost( 'parent_id' ) : 0;
		$this->created = new Phalcon\Db\RawValue( 'now()' );
		$this->updated = new Phalcon\Db\RawValue( 'now()' );
		if ( $this->save() ) {
			return $this->id;
		}
		else {
			return $this->getMessages();
		}
	}

	/**
	 * Updating Account
	 *
	 * @Param int account id
	 *
	 * @Return bool || array
	 **/
	public function updateAccount( $obj, $id )
	{
		$account = $this->findFirst( $id );
		$account->parent_id = ( $obj->request->getPost( 'parent_id' ) ) ? $obj->request->getPost( 'parent_id' ) : 0;
		$account->name = $obj->request->getPost( 'name' );
		$account->is_active = ( $obj->request->getPost( 'active' ) ) ? 1 : 0 ;
		$account->updated = new Phalcon\Db\RawValue( 'now()' );
		return $account->update();
	}
	/**
	 * Default form validators for account inputs
	 *
	 * @Param void
	 *
	 * @Return bool
	 */
	public function validation()
	{
		$this->validate( new UniquenessValidator( [ 'field' => 'name', 'message' => MSG_ACCOUNT_WITH_THIS_NAME_EXISTS ] ) );
		
		if ( $this->validationHasFailed() == true ) {
			return false;
		}
	}
}
