<?php

namespace Sympathy\Form;

use DateTime;
use DateInterval;

/**
 * Default Validator for Sympathy Forms
 *
 * @author Michael Mayer <michael@lastzero.net>
 * @package Sympathy
 * @license MIT
 */
class Validator
{
    /**
     * @var Form
     */
    protected $form;

    /**
     * Return form instance
     *
     * @return Form
     * @throws Exception
     */
    public function getForm()
    {
        if (!$this->form) {
            throw new Exception ('Form not set');
        }

        return $this->form;
    }

    /**
     * Sets current form instance
     *
     * @param Form $form
     * @return $this
     */
    public function setForm(Form $form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @param string $key Field key
     * @param string $token Text token
     * @param array $params Text replacements
     */
    protected function addError($key, $token, array $params = array())
    {
        $this->getForm()->addError($key, $token, $params);
    }

    /**
     * Wrapper for form translator
     *
     * @param string $token
     * @param array $param
     * @return string
     */
    protected function translate($token, array $param = array())
    {
        return $this->getForm()->translate($token, $param);
    }

    /**
     * Validator for the "required" form field property
     */
    public function validateRequired($key, $def, $value)
    {
        if (isset($def['required']) && ($def['required'] === true) && ($value === null || $value === '' || $value === false)) {
            $this->addError($key, 'form.value_must_not_be_empty');
        }
    }

    /**
     * Validator for the "matches" form field property
     */
    public function validateMatches($key, $def, $value)
    {
        if (isset($def['matches'])) {
            if ($def['matches'][0] == '!') {
                $fieldName = substr($def['matches'], 1);
                if ($value == $this->getForm()->$fieldName) {
                    $this->addError($key, 'form.value_must_not_be_the_same', array('%other_field%' => $this->getFieldCaption($fieldName)));
                }
            } else {
                if ($value != $this->getForm()->{$def['matches']}) {
                    $this->addError($key, 'form.value_must_be_the_same', array('%other_field%' => $this->getFieldCaption($def['matches'])));
                }
            }
        }
    }

    /**
     * Validator for the "min" form field property
     */
    public function validateMin($key, $def, $value)
    {
        if (!isset($def['options']) && isset($def['min']) && $value != '') {
            if (isset($def['type']) && ($def['type'] == 'int' || $def['type'] == 'numeric' || $def['type'] == 'float')) {
                if ($value < $def['min']) {
                    $this->addError($key, 'form.value_is_too_small', array('%limit%' => $def['min']));
                }
            } elseif (isset($def['type']) && ($def['type'] == 'date' || $def['type'] == 'datetime')) {
                if ($value instanceof DateTime) {
                    if (is_int($def['min'])) {
                        $limit = new DateTime();
                        $limit->sub(new DateInterval('P' . $def['min'] . 'D'));
                    } else {
                        $limit = new DateTime($def['min']);
                    }

                    if ($value < $limit) {
                        $format = $this->translate('form.' . $def['type']);

                        $this->addError($key, 'form.value_is_too_far_in_the_past', array('%limit%' => $limit->format($format)));
                    }
                }
            } elseif (isset($def['type']) && $def['type'] == 'list') {
                if (count($value) < $def['min']) {
                    $this->addError($key, 'form.value_min_options_selected', array('%limit%' => $def['min']));
                }
            } elseif (is_scalar($value) && (strlen($value) < $def['min'])) {
                $this->addError($key, 'form.value_is_too_short_chars', array('%limit%' => $def['min']));
            }
        }
    }

    /**
     * Validator for the "max" form field property
     */
    public function validateMax($key, $def, $value)
    {
        if (!isset($def['options']) && isset($def['max']) && $value != '') {
            if (isset($def['type']) && ($def['type'] == 'int' || $def['type'] == 'numeric' || $def['type'] == 'float')) {
                if ($value > $def['max']) {
                    $this->addError($key, 'form.value_is_too_big', array('%limit%' => $def['max']));
                }
            } elseif (isset($def['type']) && ($def['type'] == 'date' || $def['type'] == 'datetime')) {
                if ($value instanceof DateTime) {
                    if (is_int($def['max'])) {
                        $limit = new DateTime();
                        $limit->add(new DateInterval('P' . $def['max'] . 'D'));
                    } else {
                        $limit = new DateTime($def['max']);
                    }

                    if ($value > $limit) {
                        $format = $this->translate('form.' . $def['type']);
                        $this->addError($key, 'form.value_is_too_far_in_the_future', array('%limit%' => $limit->format($format)));
                    }
                }
            } elseif (isset($def['type']) && $def['type'] == 'list') {
                if (count($value) > $def['max']) {
                    $this->addError($key, 'form.value_max_options_selected', array('%limit%' => $def['max']));
                }
            } elseif (is_scalar($value) && strlen($value) > $def['max']) {
                $this->addError($key, 'form.value_is_too_long_chars', array('%limit%' => $def['max']));
            }
        }
    }

    /**
     * Validator for the "depends" form field property
     */
    public function validateDepends($key, $def, $value)
    {
        if (isset($def['depends'])) {
            if ($this->getForm()->{$def['depends']} != '' && $value == '' && !isset($def['depends_value_empty'])) {
                if (isset($def['depends_first_option']) && isset($this->_definition[$def['depends']]['options'])) {
                    reset($this->_definition[$def['depends']]['options']);
                    if ($this->getForm()->{$def['depends']} == key($this->_definition[$def['depends']]['options'])) {
                        $this->addError($key, 'form.value_empty_depends',
                            array(
                                '%other_field' => $this->getFieldCaption($def['depends']),
                                '%value%' => current($this->_definition[$def['depends']]['options'])
                            )
                        );
                    }
                } elseif (isset($def['depends_last_option']) && isset($this->_definition[$def['depends']]['options'])) {
                    end($this->_definition[$def['depends']]['options']);
                    if ($this->getForm()->{$def['depends']} == key($this->_definition[$def['depends']]['options'])) {
                        $this->addError($key, 'form.value_empty_depends',
                            array(
                                '%other_field' => $this->getFieldCaption($def['depends']),
                                '%value%' => current($this->_definition[$def['depends']]['options'])
                            )
                        );
                    }
                } elseif (!isset($def['depends_value']) || $this->getForm()->{$def['depends']} == $def['depends_value']) {
                    $this->addError($key, 'form.value_empty');
                }
            } elseif ($this->getForm()->{$def['depends']} == '' && $value == '' && isset($def['depends_value_empty'])) {
                $this->addError($key, 'form.value_empty',
                    array(
                        '%other_field' => $this->getFieldCaption($def['depends'])
                    )
                );
            }
        }
    }

    /**
     * Validator for the "regex" form field property
     */
    public function validateRegex($key, $def, $value)
    {
        if (isset($def['regex']) && !empty($value) && !preg_match($def['regex'], $value)) {
            $this->addError($key, 'form.value_not_valid_regex');
        }
    }

    /**
     * Validator for the "options" form field property
     */
    public function validateOptions($key, $def, $value)
    {
        if (isset($def['options']) && $value != '') {
            if (isset($def['min']) || isset($def['max'])) {
                if (!is_array($value)) {
                    $this->addError($key, 'form.value_must_be_list');
                } else {
                    if (isset($def['min']) && count($value) < $def['min']) {
                        $this->addError($key, 'form.value_min_options_selected', array('%limit%' => $def['min']));
                    }

                    if (isset($def['max']) && count($value) > $def['max']) {
                        $this->addError($key, 'form.value_max_options_selected', array('%limit%' => $def['max']));
                    }
                }
            }

            if (is_array($value)) {
                foreach ($value as $option => $order) {
                    if (is_int($option)) {
                        if (!isset($def['options'][$order])) {
                            $this->addError($key, 'form.value_invalid_option', array('%option%' => $order));
                        }
                    } else {
                        if (!isset($def['options'][$option])) {
                            $this->addError($key, 'form.value_invalid_option', array('%option%' => $option));
                        }
                    }
                }
            } else {
                if (!isset($def['options'][$value])) {
                    $this->addError($key, 'form.value_invalid_option', array('%option%' => $value));
                }
            }
        }
    }

    /**
     * Validator for the "type" form field property
     */
    public function validateType($key, $def, $value)
    {
        if (isset($def['type']) && $value != '') {
            switch ($def['type']) {
                case 'int':
                    if (!is_int($value) && !preg_match('/^\d+$/', $value)) {
                        $this->addError($key, 'form.value_type_integer');
                    }
                    break;
                case 'numeric':
                    if (!is_numeric($value)) {
                        $this->addError($key, 'form.value_type_numeric');
                    }
                    break;
                case 'scalar':
                    if (!is_scalar($value)) {
                        $this->addError($key, 'form.value_type_scalar');
                    }
                    break;
                case 'list':
                    if (!is_array($value)) {
                        $this->addError($key, 'form.value_type_list');
                    }
                    break;
                case 'float':
                    if (isset($def['type_params'])) {
                        if (!is_array($def['type_params'])) {
                            throw new Exception('type_params must be an array and contain the key "decimal"');
                        }
                        $options = $def['type_params'];
                    } else {
                        $options = array('decimal' => $this->translate('form.decimal_point'));
                    }
                    if (!filter_var($value, FILTER_VALIDATE_FLOAT, array('options' => $options))) {
                        $this->addError($key, 'form.value_type_float');
                    }
                    break;
                case 'bool':
                    if (!is_bool($value)) {
                        $this->addError($key, 'form.value_type_bool');
                    }
                    break;
                case 'string':
                    if (!is_string($value)) {
                        $this->addError($key, 'form.value_type_string');
                    }
                    break;
                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $this->addError($key, 'form.value_type_email');
                    }
                    break;
                case 'ip':
                    if (!filter_var($value, FILTER_VALIDATE_IP)) {
                        $this->addError($key, 'form.value_type_ip');
                    }
                    break;
                case 'url':
                    if (!filter_var($value, FILTER_VALIDATE_URL)) {
                        $this->addError($key, 'form.value_type_url');
                    }
                    break;
                case 'date':
                case 'time':
                case 'datetime':
                    if (!is_object($value) && DateTime::createFromFormat($this->translate('form.' . $def['type']), $value) === false) {
                        $this->addError($key, 'form.value_type_' . $def['type']);
                    }
                    break;
                case 'switch':
                    if ($value != '' && $value != 1 && $value != 0) {
                        $this->addError($key, 'form.value_type_switch');
                    }
                    break;
                default:
                    throw new Exception('Invalid field type: ' . $def['type']);
                    break;
            }
        }
    }

    /**
     * Applies the validators to a form field. Can be extended by inherited classes.
     *
     * @param $key string The field name
     * @param $def array The field definition
     * @param $value mixed The field value
     */
    public function validateField($key, $def, $value)
    {
        $this->validateRequired($key, $def, $value);
        $this->validateMin($key, $def, $value);
        $this->validateMax($key, $def, $value);
        $this->validateMatches($key, $def, $value);
        $this->validateDepends($key, $def, $value);
        $this->validateRegex($key, $def, $value);
        $this->validateOptions($key, $def, $value);
        $this->validateType($key, $def, $value);
    }
}