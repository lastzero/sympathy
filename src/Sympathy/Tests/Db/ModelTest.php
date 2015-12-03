<?php

namespace Sympathy\Tests\Db;

use TestTools\TestCase\UnitTestCase;

/**
 * @author Michael Mayer <michael@lastzero.net>
 * @package Sympathy
 * @license MIT
 */
class ModelTest extends UnitTestCase
{
    /**
     * @var UserModel
     */
    protected $model;

    public function setUp()
    {
        $db = $this->get('dbal.connection');
        $this->model = new UserModel ($db);
    }

    /**
     * @expectedException \Sympathy\Db\NotFoundException
     */
    public function testFindNotFoundException()
    {
        $this->model->find(45345);
    }

    public function testFind()
    {
        $user = $this->model->factory('User');

        $user->find(1);

        $this->assertEquals('Foo', $user->username);
        $this->assertEquals('foo@bar.com', $user->email);
        $this->assertEquals(true, $user->active);
    }
}