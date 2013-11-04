<?php

namespace Sympathy\Tests\Db;

use TestTools\TestCase\UnitTestCase;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
 * @package Sympathy
 * @license MIT
 */
class EntityTest extends UnitTestCase
{
    /**
     * @var UserDao
     */
    protected $dao;

    public function setUp()
    {
        $db = $this->get('dbal.connection');
        $this->dao = new UserDao ($db);
    }

    /**
     * @expectedException \Sympathy\Db\NotFoundException
     */
    public function testFindNotFoundException()
    {
        $this->dao->find(45345);
    }

    public function testFind()
    {
        $user = $this->dao->factory('User');

        $user->find(1);

        $this->assertEquals('Foo', $user->username);
        $this->assertEquals('foo@bar.com', $user->email);
        $this->assertEquals(true, $user->active);
    }
}