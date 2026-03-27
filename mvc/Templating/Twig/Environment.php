<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Legacy\Templating\Twig;

use eZ\Publish\Core\MVC\Legacy\Templating\LegacyEngine;
use Twig\Environment as BaseEnvironment;
use Twig\Error\LoaderError;
use Twig\Template as BaseTemplate;

class Environment extends BaseEnvironment
{
    /**
     * @var \eZ\Publish\Core\MVC\Legacy\Templating\LegacyEngine
     */
    private $legacyEngine;

    /**
     * Template objects indexed by their identifier.
     *
     * @var \eZ\Publish\Core\MVC\Legacy\Templating\Twig\Template[]
     */
    protected $legacyTemplatesCache = [];

    public function setEzLegacyEngine(LegacyEngine $legacyEngine)
    {
        $this->legacyEngine = $legacyEngine;
    }

    public function loadTemplate(string $cls, string $name, ?int $index = null): BaseTemplate
    {
        // If legacy engine supports given template, delegate it.
        if (\is_string($name) && isset($this->legacyTemplatesCache[$name])) {
            return $this->legacyTemplatesCache[$name];
        }

        if (\is_string($name) && $this->legacyEngine !== null && $this->legacyEngine->supports($name)) {
            if (!$this->legacyEngine->exists($name)) {
                throw new LoaderError("Unable to find the template \"$name\"");
            }

            $this->legacyTemplatesCache[$name] = new Template($name, $this, $this->legacyEngine);

            return $this->legacyTemplatesCache[$name];
        }

        return parent::loadTemplate($cls, $name, $index);
    }
}
