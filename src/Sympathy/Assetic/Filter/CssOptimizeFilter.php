<?php

namespace Sympathy\Assetic\Filter;

use Assetic\Asset\AssetInterface;
use Assetic\Filter\BaseCssFilter;

/**
 * This Assetic filter optimizes your CSS by removing duplicate rules
 *
 * It currently only works with well formed CSS without inline comments (requires a pre-filter)
 *
 * use Assetic\FilterManager;
 * use Assetic\Asset\AssetCollection;
 * use Sympathy\Assetic\Filter\CssOptimizeFilter;
 *
 * $filter = new FilterManager();
 * $filter->set('css_optimize', new CssOptimizeFilter());
 *
 * $css = new AssetCollection();
 * $css->ensureFilter($filter->get('css_optimize'));
 *
 * @author Michael Mayer <michael@liquidbytes.net>
 * @package Sympathy
 * @license MIT
 */

class CssOptimizeFilter extends BaseCssFilter
{
    protected $counts;

    public function __construct()
    {
    }

    public function filterLoad(AssetInterface $asset)
    {
    }

    public function filterDump(AssetInterface $asset)
    {
        $content = $asset->getContent();

        $content = $this->optimizeCss($content);

        $asset->setContent($content);
    }

    public function getCounts()
    {
        if(!$this->counts) {
            throw new \LogicException ('Counts are available after calling optimizeCss()');
        }

        return $this->counts;
    }

    public function optimizeCss($originalCSS)
    {
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

        for($blockNumber = 0; $blockNumber < count($blocks); $blockNumber++) {
            $block = trim($blocks[$blockNumber]);
            $parts = explode('{', $block);

            if($block == '') {
                // Nothing to do
                continue;
            }

            if(count($parts) != 2) {
                $nested = $block;

                while($blockNumber < count($blocks) && trim($blocks[$blockNumber]) != '') {
                    $blockNumber++;
                    $nested .= '}' . trim($blocks[$blockNumber]);
                }

                $nested .= '}';
                $untouchedBlocks[] = $nested;
                $counts['nested']++;
                continue;
            }

            if(strpos($block, '@') === 0) {
                $untouchedBlocks[] = $block . '}';
                $counts['unoptimized']++;
                continue;
            }

            $selectors = explode(',', $parts[0]);
            $properties = explode(';', $parts[1]);

            if(count($properties) == 0) {
                // Nothing to do
                $counts['skipped']++;
                continue;
            }

            $newProperties = array();
            $propertyName = '';
            $validProperty = false;

            foreach($properties as $property) {
                $property = trim($property);
                $charpos = strpos($property, ':');
                $hasPropertyName = $charpos !== false;

                if($hasPropertyName) {
                    $propertyName = trim(substr($property, 0, $charpos));
                    $propertyValue = trim(substr($property, $charpos + 1));
                    $validProperty = !isset($propertyBlacklist[$propertyName]) || $propertyBlacklist[$propertyName] != $propertyValue;
                }

                if($validProperty && $propertyName) {
                    if($hasPropertyName) {
                        $newProperties[$propertyName] = $property;
                    } else {
                        // Base64 image data
                        $newProperties[$propertyName] .= ';' . $property;
                    }
                }

                $counts['properties']++;
            }

            foreach($selectors as $selector) {
                $selector = trim($selector);
                $counts['selectors']++;

                if(isset($allSelectors[$selector])) {
                    $mergedProperties = array_merge($allSelectors[$selector], $newProperties);
                    $counts['merged']++;
                } else {
                    $mergedProperties = $newProperties;
                }

                ksort($mergedProperties);

                $allSelectors[$selector] = $mergedProperties;
            }
        }

        foreach($allSelectors as $selector => $properties) {
            $hash = md5(print_r($properties, true));

            if(!isset($propertyHashes[$hash])) {
                $propertyHashes[$hash] = array();
            }

            $propertyHashes[$hash][] = $selector;
        }

        foreach($propertyHashes as $selectors) {
            sort($selectors);
            $mainSelector = $selectors[0];
            $propertiesString = implode(';', $allSelectors[$mainSelector]);
            $selectorsString = implode(',', $selectors);
            $optimizedRules[$selectorsString] = $propertiesString;
        }

        foreach($untouchedBlocks as $untouchedBlock) {
            $optimizedCSS .= $untouchedBlock;
        }

        foreach($optimizedRules as $selectorsString => $propertiesString) {
            $optimizedCSS .= $selectorsString . '{' . $propertiesString .'}';
        }

        $this->counts = $counts;

        return $optimizedCSS;
    }
}