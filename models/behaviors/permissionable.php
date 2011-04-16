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
class PermissionableBehavior extends ModelBehavior {

    /**
     * Permission bits, don't touch!
     */
    const   OWNER_READ      = 256,  GROUP_READ   = 32,  OTHER_READ      = 4,
            OWNER_WRITE     = 128,  GROUP_WRITE  = 16,  OTHER_WRITE     = 2,
            OWNER_DELETE    = 64,   GROUP_DELETE = 8,   OTHER_DELETE    = 1;

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
        'defaultBits'   => 416 // owner_read + owner_write + group_read
    );

    /**
     * disable Permissionable
     *
     * @var boolean
     */
    private $_disabled = false;

    /**
     * bind the permission model to the model in question
     *
     * @param  object	$Model
     * @return boolean
     */
    private function _bind(&$Model, $conditions = array()) {

        $this->_unbind($Model);

        $alias = $this->getPermissionAlias($Model);

        return $Model->bindModel(array(
            'hasOne' => array(
                $alias => array(
                    'className'		=> 'Permissionable.PermissionBit',
                    'foreignKey'	=> 'foreign_id',
                    'dependent'		=> true,
                    'type'			=> 'INNER',
                    'conditions'	=> array_merge($conditions, array(
                        "{$alias}.model" => $Model->name
                    ))
                )
            )
        ), false);

    }

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
    private function _getPermissionQuery(&$Model, $action = 'read') {

        $alias	= $this->getPermissionAlias($Model);
        $action	= strtoupper($action);
        $gids	= Permissionable::getGroupIds();

        return array(
            // first check if "other" has the requested action
            "$alias.perms&{$this->_getPermissionBit('OTHER_' . $action)} <> 0",
            // otherwise, if the user has a group tht the row has, && the
            // "group" action is allowed
            array(
                "$alias.perms&{$this->_getPermissionBit('GROUP_' . $action)} <> 0",
                "$alias.gid" => (count($gids) == 1) ? $gids[0] : $gids
            ),
            // otherwise, if the user is the row owner, && the "owner" action
            // is allowed
            array(
                "$alias.perms&{$this->_getPermissionBit('OWNER_' . $action)} <> 0",
                "$alias.uid" => Permissionable::getUserId()
            )
        );

    }

    /**
     * helper to determine if the user
     * is the root user or member of the root group
     *
     * This method is deprecated, and will be removed in a future release
     *
     * @return boolean
     */
    private function _isRoot() {

        trigger_error(__('Model->_isRoot() is deprecated. Please use the static method Permissionable::isRoot() instead.', true), E_USER_WARNING);
        return Permissionable::isRoot();

    }

    /**
     * unbind the permission model from the model in question
     *
     * @param  object	$Model
     * @return boolean
     */
    private function _unbind(&$Model) {

        return $Model->unbindModel(array(
            'hasOne' => array(
                $this->getPermissionAlias($Model)
            )
        ), false);

    }

    /**
     * settings
     *
     * @var     array
     */
    public $settings = array();

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

        $user_id	= Permissionable::getUserId();
        $group_id	= Permissionable::getGroupId();
        $alias = $this->getPermissionAlias($Model);
        $data = array(
            'model'     => $Model->alias,
            'foreign_id'=> $Model->id,
            'uid'       => $user_id,
            'gid'		=> $group_id,
            'perms'		=> $this->settings[$Model->alias]['defaultBits']
        );

        $requested = Set::extract('/Permissionable/.', $Model->data);

        if (!empty($requested)) {

            $data = Set::merge($data, $requested[0]);

        }

        if (isset($data['id'])) {

            unset($data['id']);

        }

		$this->_bind($Model);
		
        if ($created) {

            $Model->{$alias}->create();

        } else {

            // go get existing permission for this row
            $previous = $this->getPermission($Model);

            if (!empty($previous)) {

                $Model->{$alias}->id = $previous[$alias]['id'];

            }

        }

        return $Model->{$alias}->save($data);

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

            $this->_unbind($Model);

            return $queryData;

        } elseif(Permissionable::isRoot()) {

            $this->_unbind($Model);

            /**
             * if we are skipping checks or if the user is in the "root"
             * group, just allow the query to continue unmodified
             */
            return true;

        }

        $alias = $this->getPermissionAlias($Model);

        if (empty($queryData['fields'])) {

            $queryData['fields'] = array("{$Model->alias}.*");

        }

        $queryData['fields'] = Set::merge(
                $queryData['fields'],
                array(
                    "{$alias}.*"
                )
        );

        $this->_bind($Model, array(
            "{$alias}.model" => "{$Model->alias}",
            "{$alias}.foreign_id = {$Model->alias}.{$Model->primaryKey}",
            'or' => $this->_getPermissionQuery($Model)
        ));

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

        $alias = $this->getPermissionAlias($Model);

        $this->_bind($Model);

        return $Model->{$alias}->find('first', array(
            'conditions' => array(
                "{$alias}.model"		=> $Model->alias,
                "{$alias}.foreign_id"	=> $id
            )
        ));

    }

    /**
     * get alias for the Permissionable model
     *
     * @param  object  $Model
     * @return mixed
     */
    public function getPermissionAlias(&$Model) {

        return "{$Model->alias}PermissionBit";

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
        $id         = (empty($id)) ? $Model->id : $id;

        $this->_unbind($Model);

        // if somehow we don't know who the logged-in user is, don't save!
        if (!in_array($action, $this->_actions) || empty($id) || empty($user_id) || empty($group_ids)) {

            return false;

        } elseif(Permissionable::isRoot()) {

            return true;

        }

        $this->_bind($Model);

        // do a quick count on the row to see if that permission exists
        $alias	= $this->getPermissionAlias($Model);
        $perm	= $Model->{$alias}->find('count', array(
            'conditions' => array(
                "{$alias}.model"		=> $Model->alias,
                "{$alias}.foreign_id"	=> $id,
                'or'					=> $this->_getPermissionQuery($Model, $action)
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

        $config = (is_array($config) && !empty($config))
            ? Set::merge($this->_defaults, $config)
            : $this->_defaults;

        $this->settings[$Model->alias] = $config;

    }

}

?>