<?php

namespace Sympathy\Tests\Db;

use Sympathy\Db\Entity;
use Sympathy\Db\Format;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
 * @package Sympathy
 * @license MIT
 */
class UserSequenceDao extends UserDao
{
    protected $_idSequenceName = "test_seq";
}
