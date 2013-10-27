<?php

namespace Sympathy\Assetic\Filter;

use Assetic\Asset\AssetInterface;
use Assetic\Filter\BaseCssFilter;
use Sympathy\Css\Optimizer;

/**
 * This Assetic filter optimizes your CSS by removing duplicates and combining selectors
 *
 * Since the parsing is done using simple (and fast) string functions, it requires well
 * formed CSS without inline comments. Please use a pre-filter like CssMinFilter.
 *
 * Usage:
 *
 * $filter = new \Sympathy\Assetic\Filter\CssOptimizeFilter;
 * $asset = new StringAsset($inputCss);
 * $asset->ensureFilter($filter);
 * $outputCss = $asset->dump();
 *
 * @author Michael Mayer <michael@liquidbytes.net>
 * @package Sympathy
 * @license MIT
 */

class CssOptimizeFilter extends BaseCssFilter
{
    public function __construct()
    {
    }

    public function filterLoad(AssetInterface $asset)
    {
    }

    public function filterDump(AssetInterface $asset)
    {
        $optimizer = new Optimizer;

        $content = $asset->getContent();

        $content = $optimizer->optimizeCss($content);

        $asset->setContent($content);
    }
}