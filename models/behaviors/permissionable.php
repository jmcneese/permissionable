<?php

/**
 * PermissionableBehavior
 *
 * An implementation of *NIX-like bitwise permissions for row-level operations.
 *
 * @package     permissionable
 * @subpackage  permissionable.models.behaviors
 * @author      Joshua McNeese <jmcneese@gmail.com>
 */
final class PermissionableBehavior extends ModelBehavior {

	/**
	 * Permission bits, don't touch!
	 */
	const   OWNER_READ		= 2048,		MEMBER_READ		= 32,
			OWNER_WRITE		= 1024,		MEMBER_WRITE	= 16,
			OWNER_DELETE	= 512,		MEMBER_DELETE	= 8,

			GROUP_READ		= 256,		OTHER_READ		= 4,
			GROUP_WRITE		= 128,		OTHER_WRITE		= 2,
			GROUP_DELETE	= 64,		OTHER_DELETE	= 1;

	/**
	 * configured actions
	 *
	 * @var array
	 */
	private $_actions = array(
		'read',
		'write',
		'delete'
	);

	/**
	 * settings defaults
	 *
	 * @var array
	 */
	private $_defaults = array(
		'defaultBits'	=> 4000, // owner (rwd) + group (rw) + member (r)
		'userModel'		=> 'User',
		'groupModel'	=> 'Group'
	);

	/**
	 * disable Permissionable
	 *
	 * @var boolean
	 */
	private $_disabled = false;

	/**
	 * Convenience method for getting the permission bit integer for an action
	 *
	 * @param   mixed    $action
	 * @return  integer
	 */
	private function _getPermissionBit($action = null) {

		$action = strtoupper($action);

		return (empty($action) || !defined("self::$action"))
			? 0
			: constant("self::$action");
		
	}

	/**
	 * helper to build the query for permission checks
	 *
	 * @param  object  $Model
	 * @param  string  $action
	 * @return array
	 */
	private function _getPermissionQuery(&$Model, $action = 'read', $alias = null) {

		extract($this->settings[$Model->alias]);

		if(empty($alias)) {

			$alias = $this->getPermissionAlias($Model);
			
		}
		
		$action	= strtoupper($action);
		$gids	= Permissionable::getGroupIds();
		$uid	= Permissionable::getUserId();
		$query	= array(
			// first check if "other" has the requested action
			"$alias.perms & {$this->_getPermissionBit('OTHER_' . $action)} <> 0",
		);

		if(!empty($gids)) {

			if($Model->name == $groupModel) {

				$query[] = array(
					"$alias.perms & {$this->_getPermissionBit('MEMBER_' . $action)} <> 0",
					"$alias.foreign_id" => $gids
				);

			} elseif(isset($Model->hasAndBelongsToMany[$groupModel])) {

				$assoc = $Model->hasAndBelongsToMany[$groupModel];
				$foreign_ids = ClassRegistry::init($assoc['with'])->find('all', array(
					'fields' => array($assoc['foreignKey']),
					'contain' => array(),
					'recursive' => -1,
					'conditions' => array(
						$assoc['associationForeignKey'] => $gids
					)
				));

				if(!empty($foreign_ids)) {

					$query[] = array(
						"$alias.perms & {$this->_getPermissionBit('MEMBER_' . $action)} <> 0",
						"$alias.foreign_id" => Set::extract("/{$assoc['with']}/{$assoc['foreignKey']}", $foreign_ids)
					);

				}

			}

		}

		if(!empty($gids)) {

			/**
			 * otherwise, if the user is in a group that owns this row, and the
			 * "group" action is allowed
			 */
			$query[] = array(
				"$alias.perms & {$this->_getPermissionBit('GROUP_' . $action)} <> 0",
				"$alias.gid" => $gids
			);

		}

		if(!empty($uid)) {

			/**
			 * otherwise, if the user is the row owner and the "owner" action is allowed
			 */
			$query[] = array(
				"$alias.perms & {$this->_getPermissionBit('OWNER_' . $action)} <> 0",
				"$alias.uid" => $uid
			);

		}

		return $query;
		
	}

	/**
	 * afterSave model callback
	 *
	 * cleanup any related permission rows
	 *
	 * @param  object  $Model
	 * @param  boolean $created
	 * @return boolean
	 */
	public function afterSave(&$Model, $created) {

		if ($this->_disabled) {

			return true;

		}
		
		extract($this->settings[$Model->alias]);

		$user_id	= Permissionable::getUserId();
		$group_id	= Permissionable::getGroupId();
		$alias		= $this->getPermissionAlias($Model);
		$data		= array(
			'model'     => $Model->alias,
			'foreign_id'=> $Model->id,
			'uid'       => $user_id,
			'gid'		=> $group_id,
			'perms'		=> $this->settings[$Model->alias]['defaultBits']
		);

		$requested = Set::extract('/Permission/.', $Model->data);

		if (!empty($requested)) {

			$data = Set::merge($data, $requested[0]);

		}
		
		if($Model->name == $userModel) {

			$assoc = $Model->hasAndBelongsToMany[$groupModel];
			$data = array_merge($data, array(
				'uid' => $Model->id,
				'gid' => $Model->data[$userModel][$assoc['associationForeignKey']]
			));

		}

		if (isset($data['id'])) {

			unset($data['id']);

		}

		if ($created) {

			$this->Permission->create();

		} else {

			// go get existing permission for this row
			$previous = $this->getPermission($Model);

			if (!empty($previous)) {

				$this->Permission->id = $previous['id'];

			}

		}

		return $this->Permission->save($data);
		
	}

	/**
	 * beforeDelete model callback
	 *
	 * direct the callback to determine if user has delete permission on the row
	 *
	 * @param  object $Model
	 * @return boolean
	 */
	public function beforeDelete(&$Model) {

		if ($this->_disabled) {

			return true;

		}

		return $this->hasPermission($Model, 'delete');

	}

	/**
	 * beforeFind model callback
	 *
	 * if we are checking permissions, then the appropriate modifications are
	 * made to the original query to filter out denied rows
	 *
	 * @param  object  $Model
	 * @param  array   $queryData
	 * @return mixed
	 */
	public function beforeFind(&$Model, $queryData) {

		if (
			$this->_disabled ||
			(
				isset($queryData['permissionable']) &&
				$queryData['permissionable'] == false
			) || (
				isset($queryData['conditions']['permissionable']) &&
				$queryData['conditions']['permissionable'] == false
			)
		) {

			@(unset) $queryData['permissionable'];
			@(unset) $queryData['conditions']['permissionable'];

			return $queryData;

		} elseif(Permissionable::isRoot()) {

			/**
			 * if we are skipping checks or if the user is in the "root"
			 * group, just allow the query to continue unmodified
			 */
			return true;

		}

		extract($this->settings[$Model->alias]);

		$alias = $this->getPermissionAlias($Model);

		if (is_array($queryData['fields'])) {

			$queryData['fields'][] = "{$alias}.*";

		} elseif(!empty($queryData['fields'])) {

			$queryData['fields'] = array(
				$queryData['fields'],
				"{$alias}.*"
			);

		} else {

			$queryData['fields'] = array(
				"{$Model->alias}.*",
				"{$alias}.*"
			);

		}
		
		$queryData['joins'][] = array(
			'table'			=> 'permissions',
			'alias'			=> $alias,
			'foreignKey'	=> false,
			'type'			=> 'INNER',
			'conditions'	=> array(
				"{$alias}.model" => "{$Model->name}",
				"{$alias}.foreign_id = {$Model->alias}.{$Model->primaryKey}",
				'or' => $this->_getPermissionQuery($Model)
			)
		);

		return $queryData;
		
	}

	/**
	 * beforeSave model callback
	 *
	 * @param  object $Model
	 * @return boolean
	 */
	public function beforeSave(&$Model) {

		if ($this->_disabled) {

			return true;

		}

		$user_id	= Permissionable::getUserId();
		$group_id	= Permissionable::getGroupId();
		$group_ids	= Permissionable::getGroupIds();

		// if somehow we don't know who the logged-in user is, don't save!
		if (empty($user_id) || empty($group_id) || empty($group_ids)) {

			return false;

		}

		return (!empty($Model->id))
			? $this->hasPermission($Model, 'write')
			: true;
		
	}

	/**
	 * get the permissions for the record
	 *
	 * @param  object  $Model
	 * @param  mixed   $id
	 * @return mixed
	 */
	public function getPermission(&$Model, $id = null) {

		$id = (empty($id))
			? $Model->id
			: $id;

		if (empty($id)) {

			return false;

		}

		$permission = $this->Permission->find('first', array(
			'contain' => array(),
			'recursive' => -1,
			'conditions' => array(
				"Permission.model"		=> $Model->name,
				"Permission.foreign_id"	=> $id
			)
		));

		return !empty($permission) ? $permission['Permission'] : null;
		
	}

	/**
	 * get alias for the Permissionable model
	 *
	 * @param  object  $Model
	 * @return mixed
	 */
	public function getPermissionAlias(&$Model) {

		return "{$Model->alias}Permission";
		
	}

	/**
	 * Determine whether or not a user has a certain permission on a row
	 *
	 * @param  object  $Model
	 * @param  string  $action
	 * @param  mixed   $id
	 * @return boolean
	 */
	public function hasPermission(&$Model, $action = 'read', $id = null) {

		if ($this->_disabled) {

			return true;

		}

		$user_id	= Permissionable::getUserId();
		$group_ids	= Permissionable::getGroupIds();
		$id			= (empty($id)) ? $Model->id : $id;

		// if somehow we don't know who the logged-in user is, don't save!
		if (!in_array($action, $this->_actions) || empty($id) || empty($user_id) || empty($group_ids)) {

			return false;

		} elseif(Permissionable::isRoot()) {

			return true;

		}

		// do a quick count on the row to see if that permission exists
		$perm = $this->Permission->find('count', array(
			'conditions' => array(
				"Permission.model"		=> $Model->name,
				"Permission.foreign_id"	=> $id,
				'or'					=> $this->_getPermissionQuery($Model, $action, 'Permission')
			)
		));

		return !empty($perm);
		
	}

	/**
	 * disable Permissionable for the model
	 *
	 * @param  object   $Model
	 * @param  boolean  $disable
	 * @return null
	 */
	public function disablePermissionable(&$Model, $disable = true) {

		$this->_disabled = $disable;
		
	}

	/**
	 * getter to determine if Permissionable is enabled
	 *
	 * @return boolean
	 */
	public function isPermissionableDisabled() {

		return $this->_disabled;
		
	}

	/**
	 * Behavior configuration
	 *
	 * @param   object  $Model
	 * @param   array   $config
	 * @return  void
	 */
	public function setup(&$Model, $config = array()) {

		$config = (is_array($config) &&!empty($config))
			? Set::merge($this->_defaults, $config)
			: $this->_defaults;

		$this->settings[$Model->alias] = $config;

		$this->Permission = ClassRegistry::init('Permissionable.Permission');
		
	}

}

?>