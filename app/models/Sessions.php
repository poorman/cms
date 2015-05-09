<?php
/**
 * Package CMS
 * Syntax Phalcon 2.0
 * Owner: system-work.com
 * Author: Sebastian Rzeszowicz
 * Date: 5/5/2015
 *
 * Segment: Model
 * Object: Session
*/


use Phalcon\Mvc\Model;
/**
 * From here we will control all Session actions
 */
class Sessions extends Model
{
	/**
	* @var integer
	*/
	public $user_id;
	
	/**
	* @var integer
	*/
	public $start;
	
		/**
	* @var integer
	*/
	public $end;
	/**
	 * Fetching collection of available permission levels to current user
	 * usually same permission level and below
	 *
	 * @Param object
	 *
	 * @Return array
	 **/
	public function availableAccessList( $obj )
	{
		$access = array_flip ( $obj->access );
		unset( $access[ 0 ] );
		foreach( $obj->access as $level ) {
			if ( $obj->access[ $obj->Oauth[ 'role' ] ] < $level ) {
					unset( $access[ $level ] );
			}
		}
		return $access;
	}
	
	public function start( $id )
	{
		$this->user_id = $id;
		$this->start = date( 'Y-m-d H:i:s' );
		$this->save();
		return $this;
	}
	
	public function stop( Sessions $session )
	{
		$session->stop = new Phalcon\Db\RawValue( 'now()' );
		$session->update();
	}
}
