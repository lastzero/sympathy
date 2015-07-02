<?php

namespace Sympathy\Tests\Db;

use TestTools\TestCase\UnitTestCase;
use Sympathy\Db\Format;
use DateTime;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
 * @package Sympathy
 * @license MIT
 */
class FormatTest extends UnitTestCase {
    public function setUp () {
        date_default_timezone_set('UTC');
    }

    public function testFromSqlDatetime () {
        $output = Format::fromSql(Format::DATETIME, '2010-10-11 17:08:21');
        $this->assertInstanceOf('\DateTime', $output);
        $this->assertEquals('11.10.2010 17:08', $output->format('d.m.Y H:i'));
    }

    public function testFromSqlDate () {
        $output = Format::fromSql(Format::DATE, '2010-10-11');
        $this->assertInstanceOf('\DateTime', $output);
        $this->assertEquals('11.10.2010', $output->format('d.m.Y'));
        $this->assertEquals('00:00:00', $output->format('H:i:s'));
    }

    public function testFromSqlUnixTimestamp () {
        $output = Format::fromSql(Format::TIMESTAMP, '1354632469');
        $this->assertInstanceOf('\DateTime', $output);
        $this->assertEquals('04.12.2012', $output->format('d.m.Y'));

        $output = Format::fromSql(Format::TIMESTAMP, 1354632469);
        $this->assertInstanceOf('\DateTime', $output);
        $this->assertEquals('04.12.2012', $output->format('d.m.Y'));

        $output = Format::fromSql(Format::TIMESTAMP, '1354632469');
        $this->assertInstanceOf('\DateTime', $output);
        $this->assertEquals('04.12.2012 14:47', $output->format('d.m.Y H:i'));

        $output = Format::fromSql(Format::TIMESTAMP, 1354632469);
        $this->assertInstanceOf('\DateTime', $output);
        $this->assertEquals('04.12.2012 14:47', $output->format('d.m.Y H:i'));
    }

    public function testToSqlDateException () {
        $this->setExpectedException('Sympathy\Db\FormatException');
        Format::toSql(Format::DATE, new Format());
    }

    public function testToSqlDatetimeException () {
        $this->setExpectedException('Sympathy\Db\FormatException');
        Format::toSql(Format::DATETIME, new Format());
    }

    public function testFromSqlNumberException () {
        $this->setExpectedException('Sympathy\Db\FormatException');
        Format::fromSql('#.00', 1234);
    }

    public function testToSqlDateFromEmptyValue () {
        $output = Format::toSql(Format::DATE, '');
        $this->assertEquals(null, $output);

        $output = Format::toSql(Format::DATE, null);
        $this->assertEquals(null, $output);

        $output = Format::toSql(Format::DATE, 0);
        $this->assertEquals(null, $output);
    }

    public function testToSqlDateFromLocaleFormat () {
        $output = Format::toSql(Format::DATE, '11.10.2010');
        $this->assertEquals('2010-10-11', $output);
    }

    public function testToSqlDateFromDbFormat () {
        $output = Format::toSql(Format::DATE, '2010-10-11');
        $this->assertEquals('2010-10-11', $output);
    }

    public function testToSqlDateFromDateTime () {
        $date = new DateTime('2010-10-11');
        $output = Format::toSql(Format::DATE, $date);
        $this->assertEquals('2010-10-11', $output);

        $date = new DateTime('11.10.2010');
        $output = Format::toSql(Format::DATE, $date);
        $this->assertEquals('2010-10-11', $output);
    }

    public function testToSqlTimestampFromEmptyValue () {
        $output = Format::toSql(Format::TIMESTAMP, '');
        $this->assertEquals(null, $output);

        $output = Format::toSql(Format::TIMESTAMP, null);
        $this->assertEquals(null, $output);

        $output = Format::toSql(Format::TIMESTAMP, 0);
        $this->assertEquals(null, $output);
    }

    public function testToSqlTimestampFromLocaleFormat() {
        $output = Format::toSql(Format::TIMESTAMP, '04.12.2012');
        $this->assertEquals(1354579200, $output);
    }

    public function testToSqlTimestampFromDateTime() {
        $date = new DateTime('2012-12-04');
        $output = Format::toSql(Format::TIMESTAMP, $date);
        $this->assertEquals(1354579200, $output);

        $date = new DateTime('04.12.2012');
        $output = Format::toSql(Format::TIMESTAMP, $date);
        $this->assertEquals(1354579200, $output);
    }

    public function testToSqlDatetimeFromEmptyValue () {
        $output = Format::toSql(Format::DATETIME, '');
        $this->assertEquals(null, $output);

        $output = Format::toSql(Format::DATETIME, null);
        $this->assertEquals(null, $output);

        $output = Format::toSql(Format::DATETIME, 0);
        $this->assertEquals(null, $output);
    }

    public function testToSqlDatetimeFromLocaleFormat () {
        $output = Format::toSql(Format::DATETIME, '11.10.2010 18:34:45');
        $this->assertEquals('2010-10-11 18:34:45', $output);
    }

    public function testToSqlDatetimeFromDbFormat () {
        $output = Format::toSql(Format::DATETIME, '2010-10-11 18:34:45');
        $this->assertEquals('2010-10-11 18:34:45', $output);
    }

    public function testToSqlDatetimeFromDateTime () {
        $date = new DateTime('2010-10-11 18:34:45');
        $output = Format::toSql(Format::DATETIME, $date);
        $this->assertEquals('2010-10-11 18:34:45', $output);

        $date = new DateTime('11.10.2010 18:34:45');
        $output = Format::toSql(Format::DATETIME, $date);
        $this->assertEquals('2010-10-11 18:34:45', $output);
    }

    public function testToSqlTimestamptimeFromEmptyValue () {
        $output = Format::toSql(Format::TIMESTAMP, '');
        $this->assertEquals(null, $output);

        $output = Format::toSql(Format::TIMESTAMP, null);
        $this->assertEquals(null, $output);

        $output = Format::toSql(Format::TIMESTAMP, 0);
        $this->assertEquals(null, $output);
    }

    public function testToSqlTimestamptimeFromLocaleFormat () {
        $output = Format::toSql(Format::TIMESTAMP, '04.12.2012 15:47');
        $this->assertEquals(1354636020, $output);
    }

    public function testToSqlTimestamptimeFromDbFormat () {
        $output = Format::toSql(Format::TIMESTAMP, '2012-12-04 15:47');
        $this->assertEquals(1354636020, $output);
    }

    public function testToSqlTimestamptimeFromDateTime () {
        $date = new DateTime('2012-12-04 15:47');
        $output = Format::toSql(Format::TIMESTAMP, $date);
        $this->assertEquals(1354636020, $output);

        $date = new DateTime('04.12.2012 15:47');
        $output = Format::toSql(Format::TIMESTAMP, $date);
        $this->assertEquals(1354636020, $output);
    }

    public function testFromSqlFloat () {
        $output = Format::fromSql(Format::FLOAT, '11.345');
        $this->assertEquals(11.345, $output);

        $output = Format::fromSql(Format::FLOAT, 11.345);
        $this->assertEquals(11.345, $output);
    }

    public function testToSqlFloat () {
        $output = Format::toSql(Format::FLOAT, '11,345');
        $this->assertEquals(11.345, $output);

        $output = Format::toSql(Format::FLOAT, '11.345');
        $this->assertEquals(11.345, $output);

        $output = Format::toSql(Format::FLOAT, 11.345);
        $this->assertEquals(11.345, $output);
    }

    public function testToSqlAlphanumeric () {
        $output = Format::toSql(Format::ALPHANUMERIC, 'ALKDFHE 1234567890 ;"[_+)(*&^%$');
        $this->assertEquals('ALKDFHE 1234567890 _', $output);
    }

    public function testFromSqlAlphanumeric () {
        $output = Format::fromSql(Format::ALPHANUMERIC, 'ALKDFHE 1234567890 ;"[_+)(*&^%$');
        $this->assertEquals('ALKDFHE 1234567890 _', $output);
    }

    public function testToSqlNumbers () {
        $output = Format::toSql(Format::FLOAT, '11,345');
        $this->assertEquals(11.345, $output);

        $output = Format::toSql(Format::FLOAT, '12.311,345');
        $this->assertEquals(12311.345, $output);

        $output = Format::toSql(Format::FLOAT, '11.345');
        $this->assertEquals(11.345, $output);

        $output = Format::toSql(Format::FLOAT, '11345.');
        $this->assertEquals(11345, $output);

        $output = Format::toSql(Format::FLOAT, '11.345.000,12');
        $this->assertEquals(11345000.12, $output);

        $output = Format::toSql(Format::FLOAT, '11,345,000.12');
        $this->assertEquals(11345000.12, $output);

        $output = Format::toSql(Format::FLOAT, 11.345);
        $this->assertEquals(11.345, $output);
    }

    public function testFromSqlNumbers () {
        $output = Format::fromSql(Format::FLOAT, 840293411.3450);
        $this->assertEquals('840293411.345', $output);

        $output = Format::fromSql(Format::FLOAT, 11.345);
        $this->assertEquals('11.345', $output);

        $output = Format::fromSql(Format::FLOAT, 1.345);
        $this->assertEquals('1.345', $output);
    }

    public function testToSqlJSON () {
        $output = Format::toSql(Format::JSON, array('foo' => 'bar'));
        $this->assertEquals(array('foo' => 'bar'), json_decode($output, true));
    }

    public function testFromSqlJSON () {
        $output = Format::toSql(Format::JSON, array('foo' => 'bar'));
        $output = Format::fromSql(Format::JSON, $output);
        $this->assertEquals(array('foo' => 'bar'), $output);
    }

    public function testToSqlSerialized () {
        $output = Format::toSql(Format::SERIALIZED, array('foo' => 'bar'));
        $this->assertEquals(array('foo' => 'bar'), unserialize($output));
    }

    public function testFromSqlSerialized () {
        $output = Format::toSql(Format::SERIALIZED, array('foo' => 'bar'));
        $output = Format::fromSql(Format::SERIALIZED, $output);
        $this->assertEquals(array('foo' => 'bar'), $output);
    }
}