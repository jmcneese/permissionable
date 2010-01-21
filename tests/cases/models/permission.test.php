<?php

/**
 * Permission Model Test Case
 *
 * @package     permissionable
 * @subpackage  permissionable.tests.cases.models
 * @see         Permission
 * @author      Joshua McNeese <jmcneese@gmail.com>
 */
class PermissionTestCase extends CakeTestCase {

	/**
	 * @var     array
	 */
	public $fixtures = array(
		'plugin.permissionable.permission'
	);

	/**
	 * @return  void
	 */
	public function start() {

		parent::start();

		$this->Permission =& ClassRegistry::init('Permissionable.Permission');

	}

	/**
	 * Test Instance Creation
	 *
	 * @return  void
	 */
	public function testInstanceSetup() {

		$this->assertIsA($this->Permission, 'Model');

	}

}

?>