<?php

/**
 * PermissionBit Fixture
 *
 * @package     permissionable
 * @subpackage  permissionable.tests.fixtures
 * @author      Joshua McNeese <jmcneese@gmail.com>
 * @license		Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 * @copyright	Copyright (c) 2009,2010 Joshua M. McNeese, Curtis J. Beeson
 */
class PermissionBitFixture extends CakeTestFixture {

	/**
	 * @var string
	 */
	public $name = 'PermissionBit';

	/**
	 * @var array
	 */
	public $fields = array(
		'id' => array(
			'type'		=> 'integer',
			'null'		=> false,
			'default'	=> NULL,
			'key'		=> 'primary'
		),
		'model' => array(
			'type'		=> 'string',
			'null'		=> false,
			'default'	=> NULL,
			'length'	=> 32,
			'key'		=> 'index'
		),
		'foreign_id' => array(
			'type'		=> 'integer',
			'null'		=> false,
			'default'	=> NULL
		),
		'uid' => array(
			'type'		=> 'integer',
			'null'		=> false,
			'default'	=> NULL,
			'key'		=> 'index'
		),
		'gid' => array(
			'type'		=> 'integer',
			'null'		=> false,
			'default'	=> NULL,
			'key'		=> 'index'
		),
		'perms' => array(
			'type'		=> 'integer',
			'null'		=> false,
			'default'	=> '000',
			'length'	=> 3
		),
		'indexes' => array(
			'PRIMARY' => array(
				'column' => 'id',
				'unique' => 1
			),
			'polymorphic_idx' => array(
				'column' => array(
					'model',
					'foreign_id'
				),
				'unique' => 0
			),
			'uid_idx' => array(
				'column' => 'uid',
				'unique' => 0
			),
			'gid_idx' => array(
				'column' => 'gid',
				'unique' => 0
			)
		)
	);

}

?>