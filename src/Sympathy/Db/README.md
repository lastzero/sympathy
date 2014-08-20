Sympathy DB: Object-oriented CRUD for Doctrine DBAL
===================================================

The Sympathy Model and Database Access Object (DAO) classes encapsulate Doctrine DBAL to provide high-performance, object-oriented CRUD (create, read, update, delete) functionality for relational databases:

![Architecture](https://lastzero.net/wp-content/uploads/2014/08/sympathy_db.png)

*Note: This is not an ORM (object-relational mapping) framework and there are no requirements to the underlying database structure.*

Data Access Objects
-------------------
DAOs directly deal with **database tables** and **raw SQL**, if needed. To implement raw SQL only, you can use `Sympathy\Db\Dao`, while `Sympathy\Db\Entity` offers many powerful methods to easily deal with single database tables.

Example:
    
    <?php
    
    namespace App\Dao;
    
    use Sympathy\Db\Entity;
    
    class UserDao extends Entity
    {
        protected $_factoryNamespace = 'App\\Dao';
        protected $_tableName = 'users';
        protected $_primaryKey = 'user_id';
        protected $_timestampEnabled = true;
    }

Models
------
**Models** (also called "Business Models" or "Business Objects") are logically located between **Controllers** - which render views and validate user input - and **Data Access Objects** (DAOs), that are low-level interfaces to a storage backend or Web service.

Public interfaces of models are high-level and should reflect all use cases within their domain. There are a number of standard use-cases that are pre-implemented in the base class `Sympathy\Db\Model`:
- `factory ($name = '', Dao $dao = null)`: Create a new model instance
- `find ($id)`: Find a record by primary key
- `reload ()`: Reload values from database
- `findAll (array $cond = array(), $wrapResult = true)`: Find multiple records; if `$wrapResult` is false, plain DAOs are returned instead of model instances
- `search (array $cond, array $options = array())`: Perform a search ($options can contain count, offset and/or sort order; the return value array also contains count, offset, sort order plus the total number of results; see DAO documentation)
- `searchAll (array $cond = array(), $order = false)`: Simple version of search(), similar to findAll()
- `searchOne (array $cond = array())`: Search a single record; throws an exception if 0 or more than one record are found
- `searchIds (array $cond, array $options = array())`: Returns an array of matching primary keys for the given search condition
- `getModelName ()`: Returns the model name without prefix and postfix
- `getId ()`: Returns the ID of the currently loaded record (throws exception, if empty)
- `hasId ()`: Returns true, if the model instance has an ID assigned (primary key)
- `getValues ()`: Returns all model properties as associative array
- `getEntityTitle ()`: Returns the common name of this entity
- `isDeletable ()`: Returns true, if the model instance can be deleted
- `batchEdit (array $ids, array $properties)`: Update data for multiple records
- `getTableName ()`: Returns the name of the associated main database table
- `hasTimestampEnabled ()`: Returns true, if timestamps are enabled for the associated DAO
- `delete ()`: Permanently delete the entity record from the database
- `create(array $values)`: Create a new record using the values provided
- `update(array $values)`: Update model instance database record; before assigning multiple values to a model instance, data should be validated using a form class

**How much validation should be implemented within a model?** Wherever invalid data can lead to security issues or major inconsistencies, some core validation rules must be implemented in the model layer. Model exception messages usually don’t require translation (in multilingual applications), since invalid values should be recognized beforehand by a form class. If you expect certain exceptions, you should catch and handle them in your controllers.

Example:

    <?php
    
    namespace App\Model;
    
    use Sympathy\Db\Model;
    
    class User extends Model {
      protected $_daoName = ‘User’;
      protected $_factoryNamespace = 'App\\Model';
      protected $_factoryPostfix = '';
      protected $_daoFactoryNamespace = 'App\\Dao';
      
      public function delete () {
        $dao = $this->getDao();
        $dao->is_deleted = 1;
        $dao->update();
      }
    
      public function undelete () {
        $dao = $this->getDao();
        $dao->is_deleted = 0;
        $dao->update();
      }
    
      public function search (array $cond, array $options = array()) {
        $cond[‘is_deleted’] = 0;
        return parent::search($cond, $options);
      }
    }
