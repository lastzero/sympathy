<?php

namespace Sympathy\Tests\Db;

use Sympathy\Db\Model;

class UserModel extends Model {
    protected $_factoryNamespace = __NAMESPACE__;
    protected $_daoName = '\Sympathy\Tests\Db\UserDao';
}