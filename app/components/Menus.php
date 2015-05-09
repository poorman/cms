<?php
/**
 * Package CMS
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
	private $_headerMenu = [
		'navbar-left' => [
					'index'	=> [
						'caption'	=> LABEL_HOME,
						'action'	=> 'index'
					],
					'account' => [
						'caption'	=> LABEL_ACCOUNTS,
						'action'	=> 'index'
					],
					'user' => [
						'caption'	=> LABEL_USERS,
						'action'	=> 'index'
					]
		],
		'navbar-right' => [
					'session' => [
						'caption'	=> LABEL_LOGIN,
						'action'	=> 'index'
					]
			]
	];

	// User Tabs setup
	private $user_tabs = [
			LABEL_PROFILE => [
				'controller'	=> 'user',
				'action'		=> 'index',
				'param'			=> [ 'id' ],
				'any'			=> false
			],
			LABEL_UPDATE_PASSWORD => [
				'controller'	=> 'user',
				'action'		=> 'password',
				'any'			=> false
			]
	];

	// Account Tabs menu setup
	private $account_tabs = [
		LABEL_ACCOUNT => [
			'controller' 	=> 'account',
			'action' 		=> 'edit',
			'param' 		=> [ 'id' ],
			'any' 			=> false
		]
	];
	private $Oauth;
	/**
	 * Builds header menu with left and right items
	 *
	 * @return string
	 */
	public function getMenu()
	{
		$this->Oauth = $this->session->get( 'Oauth' );
		if ($this->Oauth) {
			switch( $this->Oauth[ 'role' ] ) {
				case ROLE_GUEST:
						unset( $this->_headerMenu[ 'navbar-left' ][ 'account' ] );
						unset( $this->_headerMenu[ 'navbar-left' ][ 'index' ] );
						unset( $this->_headerMenu[ 'navbar-left' ][ 'user' ] );
					break;
				case ROLE_MEMBER:
						unset($this->_headerMenu[ 'navbar-left' ][ 'account' ]);
						
						$this->_headerMenu[ 'navbar-left' ][ 'user' ][ 'caption' ] = LABEL_ACCOUNT;
						$this->_headerMenu[ 'navbar-left' ][ 'user' ][ 'action' ] = 'index';
						$this->_headerMenu[ 'navbar-left' ][ 'user' ][ 'caption' ] = LABEL_MY_SETTINGS;
						$this->_headerMenu[ 'navbar-left' ][ 'user' ][ 'action' ] = 'index';
					break;
				case ROLE_ADMIN:
						$this->_headerMenu[ 'navbar-left' ][ 'account' ][ 'caption' ] = LABEL_ACCOUNT;
						$this->_headerMenu[ 'navbar-left' ][ 'account' ][ 'action' ] = 'edit';
						$this->_headerMenu[ 'navbar-left' ][ 'account' ][ 'param' ] = [ 'account_id' ];
					break;
				case ROLE_GROUP_ADMIN:
					break;
				case ROLE_SUPER_ADMIN:
					break;
				case ROLE_DEVELOPER:
					break;
				default:
					break;
					
			}
			if ( $this->Oauth[ 'role' ] != ROLE_GUEST ) {
				$this->_headerMenu[ 'navbar-right' ][ 'session' ] = [
				'caption' => LABEL_LOGOUT,
				'action' => 'end'
				];
			}
		} else {
			$this->_headerMenu[ 'navbar-left' ] = [];
			$this->_headerMenu[ 'navbar-right' ] = [];
		}

		$controllerName = $this->view->getControllerName();
		foreach ( $this->_headerMenu as $position => $menu ) {
			echo '<div class="nav-collapse">';
			echo '<ul class="nav navbar-nav ', $position, '">';
			foreach ( $menu as $controller => $option ) {
				if ( $controllerName == $controller ) {
					echo '<li class="active">';
				} else {
					echo '<li>';
				}
				$option[ 'target' ] = !empty( $option[ 'target' ] ) ? $option[ 'target' ] : "_self" ;
				$params = '';
				if ( isset( $option[ 'param' ] ) ) {
					foreach ( $option[ 'param' ] as $param ) {
					$params .='/'.$this->Oauth[ $param ];
				}
			}
				echo $this->tag->linkTo( [ $controller . '/' . $option[ 'action' ] . $params, $option[ 'caption' ],'target' => $option[ 'target' ] ] );
				echo '</li>';
			}
			if ( !empty( $this->Oauth[ 'role' ] ) && $this->Oauth[ 'role' ] != ROLE_GUEST && $position == 'navbar-right' ) { 
				echo '<li class="menu-user-pane">';
				
				$show_role = ( empty( $this->Oauth[ 'parent_id' ] ) ) ? LABEL_HEAD . ' ' . $this->Oauth[ 'role' ] : $this->Oauth[ 'role' ];
				
				echo $this->tag->linkTo( [ 'user/index/' . $this->Oauth[ 'id' ], '<img src="/images/user.png"  style="float: left;"  ><span style="float: right; padding-top:5px; padding-right:5px; ">' . $this->Oauth[ 'name' ] . ' <br><small>' . $show_role . '</small>' ] ) . '</span>';
				echo '</li>';
			}
			echo '</ul>';
			echo '</div>';
		}

	}

	/**
	 * Returns menu tabs
	 */
	public function getTabs( $tabs = false, $id = false )
	{
		$this->Oauth = $this->session->get( 'Oauth' );
		if ( $this->Oauth ) {
			switch( $this->Oauth[ 'role' ] ) {
				case ROLE_MEMBER:
						//unset($this->_tabs[ 'Companies' ]);
					break;
				case ROLE_ADMIN:
						//unset($this->_tabs[ 'Companies' ]);
					break;
				case ROLE_GROUP_ADMIN:
					break;
				case ROLE_SUPER_ADMIN:
					break;
				case ROLE_DEVELOPER:
					break;
				default : return false; break;
			}
		}
		$tabs = ( $tabs ) ? $tabs . '_tabs' : '_tabs';
		$tabs = $this->$tabs;
		$controllerName = $this->view->getControllerName();
		$actionName = $this->view->getActionName();
		echo '<ul class="nav nav-tabs">';
		foreach ($tabs as $caption => $option) {
			if ($option[ 'controller' ] == $controllerName && ($option[ 'action' ] == $actionName || $option[ 'any' ]) ) {
				echo '<li class="active">';
			} else {
				echo '<li>';
			}
			$params = '';
			if ( isset( $id ) ) {
				$params .= '/'. $id;
			}
			else {
				if ( isset( $option[ 'param' ] ) ) {
					foreach ( $option[ 'param' ] as $param ) {
						$params .='/' . $this->Oauth[$param];
					}
				}
			}
			echo $this->tag->linkTo( $option[ 'controller' ] . '/' . $option[ 'action' ] . $params, $caption ), '<li>';
		}
		echo '</ul>';
	}
}
