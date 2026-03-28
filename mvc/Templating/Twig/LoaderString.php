<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Legacy\Templating\Twig;

use Twig\Loader\LoaderInterface;
use Twig\Source;

/**
 * This loader is supposed to directly load templates as a string, not from FS.
 *
 * {@inheritdoc}
 */
class LoaderString implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSourceContext(string $name): Source
    {
        return new Source($name, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheKey(string $name): string
    {
        return $name;
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh(string $name, int $time): bool
    {
        return true;
    }

    /**
     * Returns true if $name is a string template, false if $name is a template name (which should be loaded by Twig_Loader_Filesystem.
     *
     * @param string $name
     *
     * @return bool
     */
    public function exists(string $name)
    {
        $suffix = '.twig';
        $endsWithSuffix = strtolower(substr($name, -\strlen($suffix))) === $suffix;

        return !$endsWithSuffix;
    }
}
