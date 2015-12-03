<?php

namespace Sympathy\Css;

use \CssMin;

/**
 * This filter optimizes your CSS by removing duplicates and combining selectors
 *
 * Since the parsing is done using simple (and fast) string functions, it requires well
 * formed CSS without inline comments. Please use a pre-filter like CssMin (minifyCss() is a wrapper)
 * or any other CSS minifier.
 *
 * Usage:
 *
 * $optimizer = new \Sympathy\Css\Optimizer;
 * $inputCss = 'body { foo:bar; } body { baz:foo; } body { foo: new }';
 * $minifiedCss = $optimizer->minifyCss($inputCss);
 * $optimizedCss = $optimizer->optimizeCss($minifiedCss);
 *
 * @author Michael Mayer <michael@lastzero.net>
 * @package Sympathy
 * @license MIT
 */

class Optimizer
{
    /**
     * @var array
     */
    protected $counts;

    /**
     * Returns information about the CSS optimization
     *
     * @return array
     * @throws Exception
     */
    public function getCounts()
    {
        if (!$this->counts) {
            throw new Exception ('Counts are available after calling optimizeCss()');
        }

        return $this->counts;
    }

    /**
     * Returns minified CSS
     *
     * CssMin is a css parser and minfier. It minifies css by removing unneeded whitespace
     * character, comments, empty blocks and empty declarations. The result can be further
     * compressed by optimizeCss().
     *
     * @param $originalCSS
     * @return string
     */
    public function minifyCss($originalCSS)
    {
        return CssMin::minify($originalCSS);
    }

    /**
     * Returns optimized CSS code by removing duplicates and combining selectors
     *
     * If your CSS code contains comments, you must remove them with minifyCss() first:
     *
     * $optimizer = new \Sympathy\Css\Optimizer;
     * $optimizedCss = $optimizer->optimizeCss($optimizer->minifyCss($originalCss));
     *
     * @param string $originalCSS
     * @throws Exception
     * @return string
     */
    public function optimizeCss($originalCSS)
    {
        if (strpos($originalCSS, '/*') !== false) {
            throw new Exception ('Input CSS must not contain comments');
        }

        $counts = array(
            'skipped' => 0,
            'merged' => 0,
            'properties' => 0,
            'selectors' => 0,
            'nested' => 0,
            'unoptimized' => 0
        );

        $propertyBlacklist = array();

        $optimizedCSS = '';
        $allSelectors = array();
        $untouchedBlocks = array();
        $propertyHashes = array();
        $optimizedRules = array();

        $blocks = explode('}', $originalCSS);

        for ($blockNumber = 0; $blockNumber < count($blocks); $blockNumber++) {
            $block = trim($blocks[$blockNumber]);
            $parts = explode('{', $block);

            if ($block == '') {
                // Nothing to do
                continue;
            }

            if (count($parts) != 2) {
                $nested = $block;

                while ($blockNumber < count($blocks) && trim($blocks[$blockNumber]) != '') {
                    $blockNumber++;
                    $nested .= '}' . trim($blocks[$blockNumber]);
                }

                $nested .= '}';
                $untouchedBlocks[] = $nested;
                $counts['nested']++;
                continue;
            }

            if (strpos($block, '@') === 0) {
                $untouchedBlocks[] = $block . '}';
                $counts['unoptimized']++;
                continue;
            }

            $selectors = explode(',', $parts[0]);
            $properties = explode(';', $parts[1]);

            if (count($properties) == 0) {
                // Nothing to do
                $counts['skipped']++;
                continue;
            }

            $newProperties = array();
            $propertyName = '';
            $validProperty = false;

            foreach ($properties as $property) {
                $property = trim($property);
                $strpos = strpos($property, ':');
                $hasPropertyName = ($strpos !== false);

                if ($hasPropertyName) {
                    $propertyName = trim(substr($property, 0, $strpos));
                    $propertyValue = trim(substr($property, $strpos + 1));
                    $validProperty = !isset($propertyBlacklist[$propertyName]) || $propertyBlacklist[$propertyName] != $propertyValue;
                }

                if ($validProperty && $propertyName) {
                    if ($hasPropertyName) {
                        $newProperties[$propertyName] = $propertyName . ':' . $propertyValue;
                    } elseif ($property != '') {
                        // Base64 image data
                        $newProperties[$propertyName] .= ';' . $property;
                    }
                }

                $counts['properties']++;
            }

            foreach ($selectors as $selector) {
                $selector = trim($selector);
                $counts['selectors']++;

                if (isset($allSelectors[$selector])) {
                    $mergedProperties = array_merge($allSelectors[$selector], $newProperties);
                    $counts['merged']++;
                } else {
                    $mergedProperties = $newProperties;
                }

                ksort($mergedProperties);

                $allSelectors[$selector] = $mergedProperties;
            }
        }

        foreach ($allSelectors as $selector => $properties) {
            $hash = md5(print_r($properties, true));

            if (!isset($propertyHashes[$hash])) {
                $propertyHashes[$hash] = array();
            }

            $propertyHashes[$hash][] = $selector;
        }

        foreach ($propertyHashes as $selectors) {
            sort($selectors);
            $mainSelector = $selectors[0];
            $propertiesString = implode(';', $allSelectors[$mainSelector]);
            $selectorsString = implode(',', $selectors);
            $optimizedRules[$selectorsString] = $propertiesString;
        }

        foreach ($untouchedBlocks as $untouchedBlock) {
            $optimizedCSS .= $untouchedBlock;
        }

        foreach ($optimizedRules as $selectorsString => $propertiesString) {
            $optimizedCSS .= $selectorsString . '{' . $propertiesString . '}';
        }

        $this->counts = $counts;

        return $optimizedCSS;
    }
}