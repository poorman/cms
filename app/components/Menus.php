<?php
/* * Package CMS
 * Syntax Phalcon 2.0
 * Owner: system-work.com
 * Author: Sebastian Rzeszowicz
 * Date: 5/5/2015
 *
 * Segment: Component
 * Object: Menus
*/

use Phalcon\Mvc\User\Component;

/**
 * Elements
 *
 * Helps to build UI elements for the application
 */
class Menus extends Component
{
	// Header Menu setup
	private $_headerMenu = array(
	);
	
	/**
	 * Builds header menu with left and right items
	 *
	 * @Param void
	 *
	 * @Return string
	 */
	public function getMenu()
	{
		$controllerName = $this->view->getControllerName();
		foreach ($this->_headerMenu as $position => $menu) {
			echo '<div class="nav-collapse">';
			echo '<ul class="nav navbar-nav ', $position, '">';
			foreach ($menu as $controller => $option) {
				if ($controllerName == $controller) {
					echo '<li class="active">';
				} else {
					echo '<li>';
				}
				$option['target'] = !empty( $option['target'] ) ? $option['target'] : "_self" ;
				$params = '';
				if(isset($option['param'])) {
					foreach ( $option['param'] as $param ) {
					$params .='/'.$auth[$param];
				}
			}
				echo $this->tag->linkTo(array($controller . '/' . $option['action'] . $params, $option['caption'],'target' => $option['target']));
				echo '</li>';
			}
			if ( !empty( $auth['role'] ) && $auth['role'] != ROLE_GUEST && $position == 'navbar-right') { 
				echo '<li class="menu-user-pane">';
				
				$show_role = ( empty( $auth['parent_id'] ) ) ? LABEL_HEAD . ' ' . rtrim( $auth[ 'role' ], 's' ) : rtrim( $auth[ 'role' ], 's' );
				
				echo $this->tag->linkTo(array('users/index/' . $auth['id'], '<img src="/images/user.png"  style="float: left;"  ><span style="float: right; padding-top:5px; padding-right:5px; ">' . $auth[ 'name' ] . ' <br><small>' . $show_role . '</small>')) . '</span>';
				echo '</li>';
			}
			echo '</ul>';
			echo '</div>';
		}

	}

	/**
	 * Builds tabbed menu
	 *
	 * @Param string
	 * @Param int
	 *
	 * @Return string
	 */
	public function getTabs($tabs = false, $id = false)
	{
		$tabs = ( $tabs ) ? $tabs . '_tabs' : '_tabs';
		$tabs = $this->$tabs;
		$controllerName = $this->view->getControllerName();
		$actionName = $this->view->getActionName();
		echo '<ul class="nav nav-tabs">';
		foreach ($tabs as $caption => $option) {
			if ($option['controller'] == $controllerName && ($option['action'] == $actionName || $option['any'])) {
				echo '<li class="active">';
			} else {
				echo '<li>';
			}
			$params = '';
			if( isset( $id ) ) {
				$params .= '/'. $id;
			}
			else {
				if(isset($option['param'])) {
					foreach ( $option['param'] as $param ) {
						$params .='/'.$auth[$param];
					}
				}
			}
			echo $this->tag->linkTo($option['controller'] . '/' . $option['action'].$params, $caption), '<li>';
		}
		echo '</ul>';
	}
}
