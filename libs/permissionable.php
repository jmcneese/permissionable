<?php

/**
 * Permissionable
 *
 * Provides static class app-wide for Permissionable info getting/setting
 *
 * @package     permissionable
 * @subpackage  permissionable.libs
 * @author      Joshua McNeese <jmcneese@gmail.com>
 */
class Permissionable {

	/**
	 * @var mixed
	 */
	public static $user_id = 0;

	/**
	 * @var mixed
	 */
	public static $group_id	= 0;

	/**
	 * @var mixed
	 */
	public static $group_ids = 0;

	/**
	 * @var mixed
	 */
	public static $root_user_id = 1;

	/**
	 * @var mixed
	 */
	public static $root_group_id = 1;

	/**
	 * @return void
	 */
	private function  __construct() {}

	/**
	 * @return mixed
	 */
	public static function getUserId() {

		return Permissionable::$user_id;

	}

	/**
	 * @return mixed
	 */
	public static function getGroupId() {

		return Permissionable::$group_id;

	}

	/**
	 * @return mixed
	 */
	public static function getGroupIds() {

		return Permissionable::$group_ids;

	}

	/**
	 * @param	mixed $user_id
	 * @return	mixed
	 */
	public static function setUserId($user_id = null) {

		Permissionable::$user_id = $user_id;

	}

	/**
	 * @param	mixed $group_id
	 * @return	mixed
	 */
	public static function setGroupId($group_id = null) {

		Permissionable::$group_id = $group_id;

	}

	/**
	 * @param	mixed $group_ids
	 * @return	mixed
	 */
	public static function setGroupIds($group_ids = null) {

		Permissionable::$group_ids = $group_ids;

	}

	/**
	 * @return	mixed
	 */
	public static function getRootUserId() {

		return Permissionable::$root_user_id;

	}

	/**
	 * @return	mixed
	 */
	public static function getRootGroupId() {

		return Permissionable::$root_group_id;

	}

	/**
	 * @param	mixed $user_id
	 * @return	mixed
	 */
	public static function setRootUserId($user_id) {

		Permissionable::$root_user_id = $user_id;

	}

	/**
	 * @param	mixed $group_id
	 * @return	mixed
	 */
	public static function setRootGroupId($group_id) {

		Permissionable::$root_group_id = $group_id;

	}

  /**
   * helper to determine if the user
   * is the root user or member of the root group
   *
   * @return boolean
   */
  public static function isRoot() {

      return (
          Permissionable::$user_id == Permissionable::$root_user_id ||
          in_array(Permissionable::$root_group_id, Permissionable::$group_ids)
      );

  }

}

?>
