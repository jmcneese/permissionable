<?php

/**
 * Permission Model Test Case
 *
 * @package     permissionable
 * @subpackage  permissionable.tests.cases.models
 * @see         Permission
 * @author      Joshua McNeese <jmcneese@gmail.com>
 * @license		Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 * @copyright	Copyright (c) 2009,2010 Joshua M. McNeese, Curtis J. Beeson
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