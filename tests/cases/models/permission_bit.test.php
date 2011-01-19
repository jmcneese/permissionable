<?php

/**
 * PermissionBit Model Test Case
 *
 * @package     permissionable
 * @subpackage  permissionable.tests.cases.models
 * @see         Permission
 * @author      Joshua McNeese <jmcneese@gmail.com>
 * @license		Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 * @copyright	Copyright (c) 2009,2010 Joshua M. McNeese, Curtis J. Beeson
 */
class PermissionBitTestCase extends CakeTestCase {

	/**
	 * @var     array
	 */
	public $fixtures = array(
		'plugin.permissionable.permission_bit'
	);

	/**
	 * @return  void
	 */
	public function start() {

		parent::start();

		$this->PermissionBit = ClassRegistry::init('Permissionable.PermissionBit');

	}

	/**
	 * Test Instance Creation
	 *
	 * @return  void
	 */
	public function testInstanceSetup() {

		$this->assertIsA($this->PermissionBit, 'Model');

	}

}

?>