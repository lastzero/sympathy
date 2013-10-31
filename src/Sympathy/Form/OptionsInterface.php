<?php

namespace Sympathy\Form;

use Symfony\Component\Translation\TranslatorInterface as Translator;

interface OptionsInterface {
    public function __construct (Translator $translator);

    public function get($listName);
}