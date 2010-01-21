<?php

/**
 * Thing Fixture
 *
 * @package     permissionable
 * @subpackage  permissionable.tests.fixtures
 * @author      Joshua McNeese <jmcneese@gmail.com>
 * @license		Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
 * @copyright	Copyright (c) 2009,2010 Joshua M. McNeese, Curtis J. Beeson
 */
class ThingFixture extends CakeTestFixture {

    /**
     * @var     string
     */
    public $name    = 'Thing';

    /**
     * @var     array
     */
    public $fields  = array(
        'id'        => array(
            'type'  => 'integer',
            'length'=> 11,
            'key'   => 'primary'
        ),
        'name'      => array(
            'type'  => 'string',
            'length'=> 32,
            'null'  => false
        ),
        'desc'      => 'text',
        'created'   => 'datetime',
        'modified'  => 'datetime',
        'is_deleted'=> array(
            'type'  => 'integer',
            'length'=> 1
        ),
    );

    /**
     * @var     array
     */
    public $records = array(
        array(
            'id'        => 1,
            'name'      => 'Gadget',
            'desc'      => 'A Gadget is a type of thing',
            'created'   => '2009-01-01 00:00:01',
            'modified'  => '2009-02-02 23:23:59',
            'is_deleted'=> 0
        ),
        array(
            'id'        => 2,
            'name'      => 'Widget',
            'desc'      => 'A Widget is a type of thing',
            'created'   => '2009-01-02 00:00:02',
            'modified'  => '2009-02-03 23:23:58',
            'is_deleted'=> 0
        ),
        array(
            'id'        => 3,
            'name'      => 'Doodad',
            'desc'      => 'A Doodad is a type of thing',
            'created'   => '2009-01-03 00:00:03',
            'modified'  => '2009-02-04 23:23:57',
            'is_deleted'=> 1
        )
    );

}

?>