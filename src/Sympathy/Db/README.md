Models and Data Access Object wrappers for Doctrine DBAL
========================================================

**Models** (also called "Business Models" or "Business Objects") are logically located between **Controllers** - which render views and validate user input - and **Data Access Objects** (DAOs), that are low-level interfaces to a storage backend or Web service.

Public interfaces of models are high-level and should reflect all use cases within their domain. There is a number of standard use-cases (CRUD - create, read, update, delete) that are pre-implemented in the base class `Sympathy\Db\Model`:
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
- `update(array $values)`: Update existing entity; before assigning multiple values to a model instance, data should be validated using a form class
- `create(array $values)`: Create a new record using the values provided

**How much validation should be implemented within a model?** Wherever invalid data can lead to security issues or major inconsistencies, some core validation rules must be implemented in the model layer. Model exception messages usually don’t require translation (in multilingual applications), since invalid values should be recognized beforehand by a form class. If you expect certain exceptions, you should catch and handle them in your controllers.
