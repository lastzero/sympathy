<?php

namespace Sympathy\Db;

use DateTime;

/**
 * Utility class to convert various data types from and to SQL format
 *
 * @author Michael Mayer <michael@liquidbytes.net>
 * @package Sympathy
 * @license MIT
 */
class Format
{
    const NONE = '';
    const INT = 'int';
    const FLOAT = 'float';
    const STRING = 'string';
    const ALPHANUMERIC = 'alphanumeric';
    const SERIALIZED = 'serialized';
    const JSON = 'json';
    const BOOL = 'bool';
    const TIME = 'H:i:s';
    const DATE = 'Y-m-d';
    const DATETIME = 'Y-m-d H:i:s';
    const TIMESTAMP = 'U';

    public static function fromSql($format, $data = null)
    {
        if ($data === null) {
            return null;
        }

        switch ($format) {
            case self::NONE:
                return $data;
            case self::TIME:
            case self::DATE:
            case self::DATETIME:
            case self::TIMESTAMP:
                return DateTime::createFromFormat($format, $data);
            case self::INT:
                return (integer)$data;
            case self::BOOL:
                return (bool)$data;
            case self::FLOAT:
                return (double)$data;
            case self::STRING:
                return (string)$data;
            case self::ALPHANUMERIC:
                return preg_replace('/[^a-zA-Z0-9_ ]/', '', $data);
            case self::SERIALIZED:
                return unserialize($data);
            case self::JSON:
                return json_decode($data, true);
            default:
                throw new FormatException ('Unknown format: ' . $format);
        }
    }

    public static function toSql($format, $data = null)
    {
        if ($data === null) {
            return null;
        }

        switch ($format) {
            case self::NONE:
                return $data;
            case self::TIME:
            case self::DATE:
            case self::DATETIME:
            case self::TIMESTAMP:
                if (empty($data)) {
                    $result = null;
                } elseif (!is_object($data)) {
                    $datetime = new DateTime($data);
                    $result = $datetime->format($format);
                } elseif ($data instanceof DateTime) {
                    $result = $data->format($format);
                } else {
                    throw new FormatException('Unknown datetime object: ' . get_class($data));
                }

                return $result;
            case self::INT:
                return (integer)$data;
            case self::BOOL:
                return (integer)$data;
            case self::STRING:
                return (string)$data;
            case self::ALPHANUMERIC:
                return preg_replace('/[^a-zA-Z0-9_ ]/', '', $data);
            case self::SERIALIZED:
                return serialize($data);
            case self::JSON:
                return json_encode($data);
            case self::FLOAT:
                if (strpos($data, ',') !== false) {
                    $data = str_replace(array('.', ','), array('', '.'), $data);
                }

                return (double)$data;
            default:
                throw new FormatException ('Unknown format: ' . $format);
        }
    }
}