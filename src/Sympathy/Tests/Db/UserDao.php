<?php

namespace Sympathy\Tests\Db;

use Sympathy\Db\Entity;
use Sympathy\Db\Format;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
 * @package Sympathy
 * @license MIT
 */
class UserDao extends Entity
{
    protected $_factoryNamespace = __NAMESPACE__;
    protected $_tableName = 'users';
    protected $_primaryKey = 'id';
    protected $_timestampEnabled = true;
    protected $_formatMap = array(
        'id' => Format::INT,
        'username' => Format::STRING,
        'email' => Format::STRING,
        'active' => Format::BOOL,
        'updated' => Format::DATETIME,
        'created' => Format::DATETIME
    );
}
