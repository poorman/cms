<?php
/**
 * Package CMS
 * Syntax Phalcon 2.0
 * Owner: system-work.com
 * Author: Sebastian Rzeszowicz
 * Date: 5/5/2015
 *
 * Segment: Model
 * Object: Logos
*/

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

class Uploader extends Model
{
	/**
	 * @var integer
	 * Account identification number
	 */
	public $id;

	/**
	 * @var integer
	 * Account id
	 */
	public $account_id;

	/**
	 * @var string
	 * logo filename
	 */
	public $filename;

	/**
	 * @var string
	 * logo filename
	 */
	public $small_filename;

	/**
	 * @var string
	 * logo filename
	 */
	public $medium_filename;
	/**
	 * @var string
	 * logo filename
	 */
	public $large_filename;

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
	 * @var flag
	 */
	public $is_active;
	
	/**
	 * Upload Files
	 * 
	 * @param obj
	 *
	 *@return array || bool
	*/
	public function upload( $obj, $account_id )
	{
		/*
		$baseLocation = LOGO_FILES_PATH;
		$folder = $baseLocation . $account_id . '/';
		if (!file_exists($folder) && !is_dir($folder) ) {
			mkdir($folder);
		} 
		chmod($folder, 0777);
		$result = [];
		$count = 0;
		foreach ( $obj->request->getUploadedFiles() as $file) {//echo $folder . $file->getName().'<br>';
			if ( ! $file->moveTo( $folder . $file->getName() ) ) {
				$result[$count] = MSG_SYSTEM_ERROR;
			}
			else {
					$result[$count] = true;
			}
			$count++;
		}
		//chmod($folder, 0555);
		return $result;
		*/
	}
	 
}