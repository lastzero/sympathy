<?php

namespace Sympathy\Db;

use Doctrine\DBAL\Connection as Db;
use Sympathy\Form\Form;

/**
 * Business Models are logically located between the controllers, which render
 * the views and validate user input, and the DAOs, that are the low-level
 * interface to the storage backend. The public interface of models is high-level and
 * should reflect the all use cases for the business domain. There is a number of standard
 * use-cases that are pre-implemented in this base class for convenience reasons.
 *
 * @author Michael Mayer <michael@liquidbytes.net>
 * @package Sympathy
 * @license MIT
 */
abstract class Model {
    private $_db; // Reference to the database connection
    protected $_daoName = ''; // Main data access object (DAO) class name (without prefix)
    protected $_dao; // Reference to DAO instance

    protected $_factoryNamespace = '';
    protected $_factoryPostfix = 'Model';

    protected $_daoFactoryNamespace = '';
    protected $_daoFactoryPostfix = 'Dao';

    /**
     * @param $db Db The current database connection instance
     * @param $dao Dao An instance of a DOA to initialize this instance (otherwise, you must call find/search)
     */
    public function __construct (Db $db, Dao $dao = null) {
        $this->_db = $db;

        if(!empty($dao)) {
            $this->_dao = $dao;
        }
    }

    /**
     * Creates a new data access object (DAO) instance
     *
     * @param string $name Class name without prefix namespace and postfix
     * @throws Exception
     * @return Entity
     */
    protected function daoFactory ($name = '') {
        $daoName = empty($name) ? $this->_daoName : $name;

        if(empty($daoName)) {
            throw new Exception ('Default DAO name was not set');
        }

        if(empty($this->_db)) {
            throw new Exception ('Database instance was not set');
        }

        $className = $this->_daoFactoryNamespace . '\\' . $daoName  . $this->_daoFactoryPostfix;

        $dao = new $className ($this->_db);

        return $dao;
    }

    /**
     * Returns main DAO instance; automatically creates an instance, if $this->_dao is empty
     *
     * @return Entity
     */
    protected function getDao () {
        if(empty($this->_dao)) {
            $this->_dao = $this->daoFactory();
        }

        return $this->_dao;
    }

    /**
     * Resets the internal DAO reference
     */
    protected function resetDao () {
        $this->_dao = $this->daoFactory();
    }

    /**
     * Creates a new model instance
     *
     * @param string $modelName
     * @param Dao $dao DB DAO instance
     * @return Model
     */
    public function factory ($modelName, Dao $dao = null) {
        $className = $this->_factoryNamespace . '\\' . $modelName . $this->_factoryPostfix;

        $model = new $className ($this->_db, $dao);

        return $model;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function find ($id) {
        $this->getDao()->find($id);

        return $this;
    }

    /**
     * @param array $cond
     * @param bool $wrapResult
     * @throws FindException
     * @return array
     */
    public function findAll (array $cond = array(), $wrapResult = true) {
        $result = $this->getDao()->findAll($cond, $wrapResult);

        if(!is_array($result)) {
            throw new FindException('DAO findAll() return value is not an array');
        }

        if($wrapResult) {
            foreach ($result as &$row) {
                $row = $this->factory($this->getModelName(), $row);
            }
        }

        return $result;
    }

    /**
     * Performs a search ($options can contain count, offset and/or sort order; the return value array also
     * contains count, offset, sort order plus the total number of results; see DAO documentation)
     *
     * @param array $cond The search conditions as array
     * @param array $options The optional search options as array
     * @throws FindException
     * @return array
     */
    public function search (array $cond, array $options = array()) {
        $params = $options + array('cond' => $cond);

        $result = $this->getDao()->search($params);

        if(!is_array($result)) {
            throw new FindException('DAO search() return value is not an array');
        }

        if(!isset($options['ids_only']) || $options['ids_only'] == false) {
            foreach ($result['rows'] as &$row) {
                // Wrap search results in model instances
                $row = $this->factory($this->getModelName(), $row);
            }
        }

        return $result;
    }

    /**
     * Simple version of search(), similar to findAll()
     *
     * @param array $cond The search conditions as array
     * @param mixed $order The sort order (use an array for multiple columns)
     * @return array
     */
    public function searchAll (array $cond = array(), $order = false) {
        $options = array(
            'order' => $order,
            'count' => 0,
            'offset' => 0
        );

        $result = $this->search($cond, $options);

        return $result['rows'];
    }

    /**
     * Returns an array of matching primary keys for the given search condition
     *
     * @param array $cond
     * @param array $options
     * @throws FindException
     * @return array
     */
    public function searchIds (array $cond, array $options = array()) {
        $params = $options + array('cond' => $cond);

        $params['ids_only'] = true;

        $result = $this->getDao()->search($params);

        if(!is_array($result)) {
            throw new FindException('DAO search() return value is not an array');
        }

        return $result;
    }

    /**
     * Returns the model name without prefix and postfix
     *
     * @return string
     */
    public function getModelName () {
        $modelName = substr(get_class($this), strlen($this->_factoryNamespace) + 1, strlen($this->_factoryPostfix) * -1);

        return $modelName;
    }

    /**
     * Return the ID of the currently loaded entity (throws exception, if empty)
     *
     * @return mixed Primary key
     */
    public function getId () {
        return $this->getDao()->getId();
    }

    /**
     * Checks, if the model instance has an ID assigned (primary key on the database level)
     *
     * @return bool
     */
    public function hasId () {
        return $this->getDao()->hasId();
    }

    /**
     * Return all model instance values
     * Note: Result is empty for new instances: you must assign values or call find/search first!
     *
     * @return array Model property values
     */
    public function getValues () {
        return $this->getDao()->getValues();
    }

    /**
     * Return the common name of this entity (for lists or box titles)
     *
     * Should be overwritten by inherited classes
     *
     * @return string
     */
    public function getEntityTitle () {
        return $this->_daoName . ' ' . $this->getId();
    }

    /**
     * Returns true, if this model instance can be deleted
     * (not related to user's specific rights, which can be different)
     *
     * @return bool
     */
    public function isDeletable () {
        return true;
    }

    /**
     * Update the data of multiple DAO instances
     *
     * @param array $ids The IDs (primary keys) of the entities to be changed
     * @param array $properties The properties to be changed
     * @throws UpdateException
     * @return object this
     */
    public function batchEdit (array $ids, array $properties) {
        $this->getDao()->beginTransaction();

        try {
            foreach($ids as $id) {
                $dao = $this->daoFactory()->find($id);

                foreach($properties as $key => $value) {
                    $dao->$key = $value;
                }

                $dao->update();
            }

            $this->getDao()->commit();
        } catch (Exception $e) {
            $this->getDao()->rollBack();

            throw new UpdateException ('Batch edit was not successful: ' . $e->getMessage());
        }


        return $this;
    }

    /**
     * Returns the name of the associated main database table
     * Note: Needed for search filters or security checks
     *
     * @return string
     */
    public function getTableName () {
        return $this->getDao()->getTableName();
    }

    /**
     * Magic getter
     *
     */
    public function __get ($name) {
        return $this->getDao()->$name;
    }

    /**
     * Magic setter
     *
     * Throws exception, because Models should implement use cases and not just
     * change data based on field names. Each specific use case needs a separate
     * function.
     */
    public function __set ($name, $value) {
        throw new ModelException (
            'A use case specific method must be implemented to change any ' .
            'model data. Magic setters are therefore not available. ' .
            'Model: ' . $this->getModelName() . ', Property: ' . $name
        );
    }

    /**
     * Returns true, if timestamps are enabled in the associated main DAO
     *
     * @return bool
     */
    public function hasTimestampEnabled () {
        return $this->getDao()->hasTimestampEnabled();
    }

    /**
     * Deletes the stored data without any checks
     */
    protected function _delete () {
        $dao = $this->getDao();

        // Start the database transaction
        $dao->beginTransaction();

        try {
            $dao->delete();

            $dao->commit();
        } catch (Exception $e) {
            // Roll back in case of ANY error and throw exception
            $dao->rollBack();

            throw $e;
        }
    }

    /**
     * Permanently deletes the entity instance
     *
     * @throws DeleteException
     */
    public function delete () {
        if(!$this->hasId() || !$this->isDeletable()) {
            throw new DeleteException('Entity can not be deleted');
        }

        $this->_delete();
        $this->resetDao();
        return $this;
    }

    /**
     * Permanently stores the entity
     *
     * @param Form $form
     * @throws InvalidFormException
     * @throws CreateException
     * @throws Exception
     * @return Model
     */
    public function create (Form $form) {
        if($form->hasErrors()) {
            throw new InvalidFormException('Form passed to create() has errors');
        }

        if(!method_exists($this, '_createFromForm')) {
            throw new CreateException('You need to implement _createFromForm($form) first');
        }

        $dao = $this->getDao();

        // Start the database transaction
        $dao->beginTransaction();

        try {
            /* Note: You need to create a specific function for this operation!
             * Example:
             * 
             * protected function _createFromForm (Form $form) {
             *     $this->getDao()->setValues($form->getValues())->insert();
             * }
             */
            $this->_createFromForm($form);

            $dao->commit();
        } catch (Exception $e) {
            // Roll back in case of ANY error and throw exception
            $dao->rollBack();

            throw $e;
        }

        return $this;
    }

    /**
     * Updates stored entity data
     *
     * @param Form $form
     * @throws InvalidFormException
     * @throws UpdateException
     * @throws Exception
     * @return Model
     */
    public function update (Form $form) {
        if($form->hasErrors()) {
            throw new InvalidFormException('Form passed to update() has errors');
        }

        if(!method_exists($this, '_updateFromForm')) {
            throw new UpdateException('You need to implement _updateFromForm($form) first');
        }

        $dao = $this->getDao();

        // Start the database transaction
        $dao->beginTransaction();

        try {
            /* Note: You need to create a specific function for this operation!
             * Example:
             * 
             * protected function _updateFromForm (Form $form) {
             *     $this->getDao()->setValues($form->getValues())->update();
             * }
             */
            $this->_updateFromForm($form);

            $dao->commit();
        } catch (Exception $e) {
            // Roll back in case of ANY error and throw exception
            $dao->rollBack();

            throw $e;
        }

        return $this;
    }
}