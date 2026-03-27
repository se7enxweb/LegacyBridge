<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Legacy\Templating\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension for eZ Publish legacy.
 */
class LegacyExtension extends AbstractExtension
{
    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'ez_legacy_render_js',
                [LegacyRuntime::class, 'renderLegacyJs'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'ez_legacy_render_css',
                [LegacyRuntime::class, 'renderLegacyCss'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
