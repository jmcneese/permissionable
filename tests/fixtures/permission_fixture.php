<?php

/**
 * Permission Fixture
 *
 * @package     permissionable
 * @subpackage  permissionable.tests.fixtures
 * @author      Joshua McNeese <jmcneese@gmail.com>
 */
class PermissionFixture extends CakeTestFixture {

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

	/**
	 * @var string
	 */
	public $name = 'Permission';

}

?>