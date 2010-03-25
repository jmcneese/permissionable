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
 * @license		Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 * @copyright	Copyright (c) 2009,2010 Joshua M. McNeese, Curtis J. Beeson
 */
class PermissionableComponent extends Object {

	/**
	 * @author  Joshua McNeese <jmcneese@gmail.com>
	 * @param   object	$controller
	 * @return	void
	 */
    public function initialize(&$controller) {

		App::import('Lib', 'Permissionable.Permissionable');

		/**
		 * if the root user or root group are other than '1',
		 * set them here here, with:
		 */
		Permissionable::setRootUserId('eaaacb16-3572-11df-9cc2-f34a9a25e922');
		Permissionable::setRootGroupId('b26bc03e-3635-11df-b6fe-793dc31c42f7');

		$user = $controller->Auth->user();

		if(!empty($user)) {

			Permissionable::setUserId($user['User']['id']);
			Permissionable::setGroupId($user['User']['group_id']);

			$group_ids = $controller->Session->read('Permissionable.group_ids');

			if(!$group_ids) {

				$group_ids = ClassRegistry::init('Membership')->find('list', array(
					'fields' => array('Membership.group_id'),
					'contain' => array(),
					'conditions' => array(
						'Membership.user_id' => $user['User']['id']
					)
				));

				sort($group_ids);

				$controller->Session->write('Permissionable.group_ids', $group_ids);
			}

			Permissionable::setGroupIds(array_unique(array_merge(
				(array)$group_ids,
				array($user['User']['group_id'])
			)));
			
		}

    }

}

?>