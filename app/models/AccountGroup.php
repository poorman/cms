<?php
/**
 * Package CMS
 * Syntax Phalcon 2.0
 * Owner: system-work.com
 * Author: Sebastian Rzeszowicz
 * Date: 5/5/2015
 *
 * Segment: Model
 * Object: Account Group
*/

/**
 * From here we will control all Accounts actions
 */
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Validator\Email as EmailValidator;
use Phalcon\Mvc\Model\Validator\Uniqueness as UniquenessValidator;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

class AccouptGroup extends Model
{
	/**
	* @var integer
	*/
	public $id;
	
	/**
	* @var integer
	*/
	public $account_group_id;
	
	/**
	* @var integer
	*/
	public $account_id;
	
	/**
	* @var integer
	*/
	public $is_active = 1;

	public function deactivateAccountGroup( $account_group_id ) {
		$query = "
			UPDATE
				accountGroup
			SET
				is_active=0
			WHERE account_group_id=" . $account_group_id;
		return new Resultset( NULL, $this ,$this->getReadConnection()->query( $query ) );
	}
	/**
	 * deleting account group
	 *
	 * @Param int account id
	 *
	 * @Return bool
	 **/
	public function deleteAccountGroup( $account_group_id ) {
		$query = "DELETE FROM accountGroup WHERE account_id = '" . $account_group_id . "'";
		return new Resultset( NULL, $this ,$this->getReadConnection()->query( $query ) );
	}

	/**
	 * Fetching collection of accounts
	 *
	 * @Param object
	 * @Param int account id where 0 is for devs and group admins
	 *
	 * @Return array
	 **/
	public function XsaveReseller( $obj, $account_id = 0 )
	{
		$account_group_id = $obj->request->getPost( 'account_group_id' );
		$created = new Phalcon\Db\RawValue( 'now()' );
		$query = "INSERT INTO `resellers` (`account_group_id`,`account_id`,`created`) VALUES (" . $account_group_id . "," . $account_id . "," . $created . ")";
		return new Resultset( NULL, $this ,$this->getReadConnection()->query( $query ) );
	}
}