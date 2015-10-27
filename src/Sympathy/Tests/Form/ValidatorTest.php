<?php

namespace Sympathy\Tests\Form;

use TestTools\TestCase\UnitTestCase;
use Sympathy\Form\Validator;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
 * @package Sympathy
 * @license MIT
 */
class ValidatorTest extends UnitTestCase {
    /**
     * @var Validator
     */
    protected $validator;

    public function setUp () {
        $this->validator = $this->get('validator');
    }

    /**
     * @expectedException \Sympathy\Form\Exception
     */
    public function testGetFormException () {
        $this->validator->getForm();
    }
}