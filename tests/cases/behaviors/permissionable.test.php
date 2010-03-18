<?php

App::import('Lib', 'Permissionable.Permissionable');

/**
 * Generic Thing Model
 *
 * @package     permissionable
 * @subpackage  permissionable.tests.cases.behaviors
 * @uses        CakeTestModel
 * @author      Joshua McNeese <jmcneese@gmail.com>
 */
class Thing extends CakeTestModel {

    public $actsAs = array(
        'Permissionable.Permissionable'
    );

}

/**
 * Permissionable Test Case
 *
 * @package     permissionable
 * @subpackage  permissionable.tests.cases.behaviors
 * @see         PermissionableBehavior
 * @author      Joshua McNeese <jmcneese@gmail.com>
 */
final class PermissionableTestCase extends CakeTestCase {

    /**
     * @var     array
     */
    public $fixtures = array(
        'plugin.permissionable.thing',
        'plugin.permissionable.permission'
    );

    /**
     * @return  void
     */
    public function start() {

        parent::start();

        $this->Thing = ClassRegistry::init('Permissionable.Thing');

    }

    /**
     * Test Instance Creation
     *
     * @return  void
     */
    public function testInstanceSetup() {

        $this->assertIsA($this->Thing, 'Model');
        $this->assertTrue($this->Thing->Behaviors->attached('Permissionable'));

    }

    /**
     * Test Find
     *
     * @return  void
     */
    public function testFindNoPermissions() {

        Permissionable::setUserId(2);
        Permissionable::setGroupId(2);
        Permissionable::setGroupIds(array(2, 3, 4));

        $result1 = $this->Thing->find('all');
        $this->assertFalse($result1);

        $result2 = $this->Thing->find('count');
        $this->assertEqual($result2, 0);

        $result3 = $this->Thing->find('all', array(
            'permissionable' => false
        ));
        $this->assertTrue($result3);
        $this->assertTrue(Set::matches('/Thing[name=Gadget]', $result3));

    }

    /**
     * Test Save
     *
     * @return  void
     */
    public function testSave() {

        Permissionable::setUserId(2);
        Permissionable::setGroupId(2);
        Permissionable::setGroupIds(array(2, 3, 4));

        $data1 = array(
            array(
                'name'	=> 'Foo',
                'desc'	=> 'Foo is a Thing'
            ),
            array(
                'name'	=> 'Bar',
                'desc'	=> 'Bar is a Thing'
            )
        );
        $result1 = $this->Thing->saveAll($data1);
        $this->assertTrue($result1);

        $result2 = $this->Thing->find('all');

        $this->assertEqual(count($result2), count($data1));
        $this->assertTrue(Set::matches('/Thing[name=Foo]', $result2));
        $this->assertTrue(Set::matches('/Thing[name=Bar]', $result2));

        $this->Thing->create();
        $result3 = $this->Thing->save(array(
            'Thing' => array(
                'name'	=> 'Baz',
                'desc'	=> 'Baz is a Thing'
            ),
            'Permissionable' => array(
                'perms' => 480
            )
        ));
        $this->assertTrue($result3);

        $result4 = $this->Thing->read();
        $this->assertTrue(Set::matches('/ThingPermission[perms=480]', $result4));

        $result5 = $this->Thing->save(array(
            'Thing' => array(
                'name'	=> 'Baz',
                'desc'	=> 'Baz is a Thing!'
            ),
            'Permissionable' => array(
                'perms' => 416
            )
        ));
        $this->assertTrue($result5);

        $result6 = $this->Thing->read();
        $this->assertTrue(Set::matches('/ThingPermission[perms=416]', $result6));

        Permissionable::setUserId(null);

        $result7 = $this->Thing->save(array(
            'Thing' => array(
                'name'	=> 'Baz',
                'desc'	=> 'Baz is a Thing!'
            ),
            'Permissionable' => array(
                'perms' => 416
            )
        ));
        $this->assertFalse($result7);

        Permissionable::setUserId(2);
        Permissionable::setGroupId(null);
        Permissionable::setGroupIds(array());

        $result8 = $this->Thing->save(array(
            'Thing' => array(
                'name'	=> 'Baz',
                'desc'	=> 'Baz is a Thing!'
            ),
            'Permissionable' => array(
                'perms' => 416
            )
        ));
        $this->assertFalse($result8);

        $this->Thing->create();
        $result8 = $this->Thing->save($data1[0]);
        $this->assertFalse($result8);

        Permissionable::setUserId(2);
        Permissionable::setGroupId(2);
        Permissionable::setGroupIds(array(2, 3, 4));

        $result9 = $this->Thing->save(array(
            'Thing' => array(
                'name'  => 'Gadget',
                'desc'	=> 'A Gadget is a type of Thing!'
            ),
            'Permissionable' => array(
                'id'	=> 2,
                'perms' => 480
            )
        ));
        $this->assertTrue($result9);

    }

    /**
     * Test Delete
     *
     * @return  void
     */
    public function testDelete() {

        Permissionable::setUserId(2);
        Permissionable::setGroupId(2);
        Permissionable::setGroupIds(array(2, 3, 4));

        $data1 = array(
            'Thing' => array(
                'name'	=> 'Foo',
                'desc'	=> 'Foo is a Thing'
            ),
            'Permissionable' => array(
                'perms' => 480
            )
        );

        $this->Thing->create();
        $this->Thing->save($data1);

        $result1 = $this->Thing->delete();
        $this->assertTrue($result1);

        $this->Thing->create();
        $this->Thing->save($data1);

        Permissionable::setUserId(3);

        $result2 = $this->Thing->delete();
        $this->assertFalse($result2);

        Permissionable::setGroupId(5);
        Permissionable::setGroupIds(array(6));

        $result3 = $this->Thing->delete();
        $this->assertFalse($result3);

    }

    /**
     * Test Save
     *
     * @return  void
     */
    public function testFindMixed() {

        Permissionable::setUserId(2);
        Permissionable::setGroupId(2);
        Permissionable::setGroupIds(array(2, 3, 4));

        $data1 = array(
            array(
                'name'	=> 'Foo',
                'desc'	=> 'Foo is a Thing'
            ),
            array(
                'name'	=> 'Bar',
                'desc'	=> 'Bar is a Thing'
            )
        );
        $result1 = $this->Thing->saveAll($data1);
        $this->assertTrue($result1);

        Permissionable::setUserId(1);
        Permissionable::setGroupId(1);
        Permissionable::setGroupIds(array(1));

        $result2 = $this->Thing->find('all');
        $this->assertEqual(count($result2), 5);
        $this->assertTrue(Set::matches('/Thing[name=Foo]', $result2));
        $this->assertTrue(Set::matches('/Thing[name=Bar]', $result2));

        Permissionable::setUserId(null);
        Permissionable::setGroupId(null);
        Permissionable::setGroupIds(array());

        $result3 = $this->Thing->find('all');
        $this->assertFalse($result3);

    }

    /**
     * Test hasPermission()
     *
     * @return  void
     */
    public function testHasPermission() {

        Permissionable::setUserId(2);
        Permissionable::setGroupId(2);
        Permissionable::setGroupIds(array(2, 3, 4));

        $this->Thing->create();
        $this->Thing->save(array(
            'Thing' => array(
                'name'	=> 'Baz',
                'desc'	=> 'Baz is a Thing'
            ),
            'Permissionable' => array(
                'perms' => 480
            )
        ));

        $result1 = $this->Thing->hasPermission('read');
        $this->assertTrue($result1);

        Permissionable::setUserId(null);
        Permissionable::setGroupId(null);
        Permissionable::setGroupIds(array());

        $result2 = $this->Thing->hasPermission('read');
        $this->assertFalse($result2);

        Permissionable::setUserId(1);
        Permissionable::setGroupId(1);
        Permissionable::setGroupIds(array(1));

        $result3 = $this->Thing->hasPermission('read');
        $this->assertTrue($result3);

    }

    /**
     * Test getPermission()
     *
     * @return  void
     */
    public function testGetPermission() {

        Permissionable::setUserId(2);
        Permissionable::setGroupId(2);
        Permissionable::setGroupIds(array(2, 3, 4));

        $this->Thing->create();
        $this->Thing->save(array(
            'Thing' => array(
                'name'	=> 'Baz',
                'desc'	=> 'Baz is a Thing'
            ),
            'Permissionable' => array(
                'perms' => 480
            )
        ));

        $result1 = $this->Thing->getPermission();
        $this->assertTrue($result1);
        $this->assertTrue(Set::matches('/ThingPermission[perms=480]', $result1));

        $this->Thing->id = null;
        $result2 = $this->Thing->getPermission();
        $this->assertFalse($result2);

    }

    /**
     * Test disablePermissionable()
     *
     * @return  void
     */
    public function testDisablePermissionable() {

        $this->Thing->disablePermissionable();
        $this->assertTrue($this->Thing->isPermissionableDisabled());

        $this->Thing->disablePermissionable(false);
        $this->assertFalse($this->Thing->isPermissionableDisabled());

    }

    /**
     * Test setting disablePermissionable before reading
     *
     * @return  void
     */
    public function testReadWithPermissionableDisabled() {

        Permissionable::setUserId(2);
        Permissionable::setGroupId(2);
        Permissionable::setGroupIds(array(2, 3, 4));

        $this->Thing->create();
        $this->Thing->save(array(
            'Thing' => array(
                'name'	=> 'Baz',
                'desc'	=> 'Baz is a Thing'
            ),
            'Permissionable' => array(
                'perms' => 480
            )
        ));

        Permissionable::setUserId(null);
        Permissionable::setGroupId(null);
        Permissionable::setGroupIds(array());

        $result1 = $this->Thing->hasPermission('read');
        $this->assertFalse($result1);

				$this->Thing->disablePermissionable();
        $result2 = $this->Thing->hasPermission('read');
        $this->assertTrue($result2);

    }

    /**
     * Test setting disablePermissionable before saving
     *
     * @return  void
     */
		public function testSaveWithPermissionableDisabled() {

        Permissionable::setUserId(2);
        Permissionable::setGroupId(2);
        Permissionable::setGroupIds(array(2, 3, 4));

        $this->Thing->create();
        $result1 = $this->Thing->save(array(
            'Thing' => array(
                'name'	=> 'Baz',
                'desc'	=> 'Baz is a Thing'
            ),
            'Permissionable' => array(
                'perms' => 480
            )
        ));
        $this->assertTrue($result1);

				$this->Thing->disablePermissionable();
        $result2 = $this->Thing->read();
        $this->assertFalse(isset($result2['ThingPermission']));

	}

    /**
     * Test setting disablePermissionable before deleting
     *
     * @return  void
     */
		public function testDeleteWithPermissionableDisabled() {

        Permissionable::setUserId(2);
        Permissionable::setGroupId(2);
        Permissionable::setGroupIds(array(2, 3, 4));

        $this->Thing->create();
        $this->Thing->save(array(
            'Thing' => array(
                'name'	=> 'Baz',
                'desc'	=> 'Baz is a Thing'
            ),
            'Permissionable' => array(
                'perms' => 480
            )
        ));

        Permissionable::setUserId(5);
        Permissionable::setGroupId(5);
        Permissionable::setGroupIds(array(5, 6));

				$this->Thing->disablePermissionable();
        $result1 = $this->Thing->delete();
        $this->assertTrue($result1);

	}

}

?>