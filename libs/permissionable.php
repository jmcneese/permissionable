<?php

/**
 * Permissionable
 *
 * Provides static class app-wide for Permissionable info getting/setting
 *
 * @package     permissionable
 * @subpackage  permissionable.libs
 * @author      Joshua McNeese <jmcneese@gmail.com>
 * @license		Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 * @copyright	Copyright (c) 2009,2010 Joshua M. McNeese, Curtis J. Beeson
 */
final class Permissionable {

	/**
	 * @var mixed
	 */
	public static $user_id		= null;

	/**
	 * @var mixed
	 */
	public static $group_id		= null;

	/**
	 * @var mixed
	 */
	public static $group_ids	= null;

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

}

?>
