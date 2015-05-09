<?php

use Phalcon\Acl;
use Phalcon\Acl\Role;
use Phalcon\Acl\Resource;
use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Acl\Adapter\Memory as AclList;

/**
 * SecurityPlugin
 *
 * This is the security plugin which controls that users only have access to the modules they're assigned to
 */
class SecurityPlugin extends Plugin
{

	/**
	 * This action is executed before execute any action in the application
	 *
	 * @param Event $event
	 * @param Dispatcher $dispatcher
	 */
	public function beforeDispatch(Event $event, Dispatcher $dispatcher)
	{

		$Oauth = $this->session->get( 'Oauth' );
		if (!$Oauth){
			$role = ROLE_GUEST;
			$this->session->set( 'Oauth',[ 'role'=>$role ] );
		} else {
			$role = $Oauth["role"];
		}
		$controller = $dispatcher->getControllerName();
		$action = $dispatcher->getActionName();

		$acl = $this->getAcl();


		$allowed = $acl->isAllowed($role, $controller, $action);
		if ($allowed != Acl::ALLOW) {
			$dispatcher->forward( [	'controller' => 'errors', 'action'     => 'show401' ] );
			return false;
		}
	}

	/**
	 * Returns an existing or new access control list
	 *
	 * @returns AclList
	 */
	public function getAcl()
	{
		if (!isset($this->persistent->acl) ) {
			$acl = new AclList();

			$acl->setDefaultAction(Acl::DENY);

			//Register roles
			$roles = [
				strtolower(ROLE_DEVELOPER) => new Role(ROLE_DEVELOPER),
				strtolower(ROLE_SUPER_ADMIN) => new Role(ROLE_SUPER_ADMIN),
				strtolower(ROLE_GROUP_ADMIN) => new Role(ROLE_GROUP_ADMIN),
				strtolower(ROLE_ADMIN) => new Role(ROLE_ADMIN),
				strtolower(ROLE_MEMBER)  => new Role(ROLE_MEMBER),
				strtolower(ROLE_GUEST) => new Role(ROLE_GUEST)
			];
			foreach ($roles as $role) {
				$acl->addRole($role);
			}

			//Developers area resources
			$developerResources = [
				'user'				=> [ 'index', 'new', 'edit', 'save', 'show', 'delete','password','savePassword' ],
				'account'		=> [ 'index', 'new', 'edit', 'save','delete' ],
				'accountGroup'			=> [ 'save' ],
				'error'			=> [ 'show401', 'show404', 'show500' ]
			];
			foreach ($developerResources as $resource => $actions) {
				$acl->addResource(new Resource($resource), $actions);
			}

			//Managers area resources
			$superAdminResources = [
				'user'				=> [ 'index', 'new', 'edit', 'save', 'show', 'delete','password','savePassword' ],
				'account'		=> [ 'index', 'new', 'edit', 'save','delete' ],
				'accountGroup'			=> [ 'save' ],
				'errors'			=> [ 'show401', 'show404', 'show500' ]
			];
			foreach ($superAdminResources as $resource => $actions) {
				$acl->addResource(new Resource($resource), $actions);
			}
			//Managers area resources
			$groupAdminResources = [
				'user'				=> [ 'index', 'search', 'new', 'edit', 'save', 'show', 'delete','password','savePassword' ],
				'account'			=> [ 'index', 'new', 'edit', 'save','delete' ],
				'accountGroup'		=> [ 'save' ],
				'errors'			=> [ 'show401', 'show404', 'show500' ]				
			];
			foreach ($groupAdminResources as $resource => $actions) {
				$acl->addResource(new Resource($resource), $actions);
			}
			//Admin area resources
			$adminResources = [
				'user'				=> [ 'index', 'search', 'new', 'edit', 'save', 'show', 'delete','password','savePassword' ],
				'account'		=> [ 'edit', 'save' ],
				'errors'			=> [ 'show401', 'show404', 'show500' ]
			];
			foreach ($adminResources as $resource => $actions) {
				$acl->addResource(new Resource($resource), $actions);
			}
			
			//Private area resources
			$memberResources = [
				'user'				=> [ 'index', 'save', 'password', 'savePassword' ], //for users only no edit for user because that option exist in users/index 
				'account'		=> [ 'show' ],
				'errors'			=> [ 'show401', 'show404', 'show500' ]
			];
			foreach ($memberResources as $resource => $actions) {
				$acl->addResource(new Resource($resource), $actions);
			}

			//Public area resources
			$publicResources =[
				'index'				=> [ 'index' ],
				'errors'			=> [ 'show404', 'show500' ],
				'session'			=> [ 'index', 'start', 'end' ]
			];
			
			
			foreach ($publicResources as $resource => $actions) {
				$acl->addResource(new Resource($resource), $actions);
			}

			//Grant access to public areas for all access levels
			foreach ($roles as $role) {
				foreach ($publicResources as $resource => $actions) {
					foreach ($actions as $action){
						$acl->allow($role->getName(), $resource, $action);
					}
				}
			}

			//Grant acess to private area to role Users
			foreach ($memberResources as $resource => $actions) {
				foreach ($actions as $action){
					$acl->allow(ROLE_MEMBER, $resource, $action);
				}
			}
			//Grant acess to admin area to role admins
			foreach ($adminResources as $resource => $actions) {
				foreach ($actions as $action){
					$acl->allow(ROLE_ADMIN, $resource, $action);
				}
			}
			//Grant acess to group admin area to role group admins
			foreach ($groupAdminResources as $resource => $actions) {
				foreach ($actions as $action){
					$acl->allow(ROLE_GROUP_ADMIN, $resource, $action);
				}
			}
			//Grant acess to super admin area to super admins
			foreach ($superAdminResources as $resource => $actions) {
				foreach ($actions as $action){
					$acl->allow(ROLE_SUPER_ADMIN, $resource, $action);
				}
			}
			//Grant acess to developer area to role Developers
			foreach ($developerResources as $resource => $actions) {
				foreach ($actions as $action){
					$acl->allow(ROLE_DEVELOPER, $resource, $action);
				}
			}

			//The acl is stored in session, APC would be useful here too
			$this->persistent->acl = $acl;
		}
		return $this->persistent->acl;
	}
}
