<?php

/**
 * PermissionableComponent
 * 
 * Sets user info for PermissionableBehavior
 *
 * @package     permissionable
 * @subpackage  permissionable.controllers.components
 * @see         PermissionableBehavior
 * @uses		Component
 * @author      Joshua McNeese <jmcneese@gmail.com>
 */
final class PermissionableComponent extends Component {

	/**
	 * @author  Joshua McNeese <jmcneese@gmail.com>
	 * @param   object	$controller
	 * @return	void
	 */
    public function initialize(&$controller) {

		App::import('Lib', 'Permissionable.Permissionable');

		/**
		 * set user info here, with:
		 * 
		 * Permissionable::setUserId(2);
		 * Permissionable::setGroupId(2);
		 * Permissionable::setGroupIds(array(3,4));
		 */

    }

}

?>