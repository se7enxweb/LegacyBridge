<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Legacy\Templating\Twig;

use eZ\Publish\Core\MVC\Legacy\Templating\LegacyEngine;
use Twig\Environment;
use Twig\Source;
use Twig\Template as BaseTemplate;

/**
 * Twig Template class representation for a legacy template.
 */
class Template extends BaseTemplate
{
    private $templateName;

    /**
     * @var \eZ\Publish\Core\MVC\Legacy\Templating\LegacyEngine
     */
    private $legacyEngine;

    public function __construct(string $templateName, Environment $env, LegacyEngine $legacyEngine)
    {
        parent::__construct($env);

        $this->templateName = $templateName;
        $this->legacyEngine = $legacyEngine;
    }

    /**
     * Renders the template with the given context and returns it as string.
     *
     * @param array $context An array of parameters to pass to the template
     *
     * @return string The rendered template
     */
    public function render(array $context): string
    {
        return $this->legacyEngine->render($this->templateName, $context);
    }

    /**
     * Displays the template with the given context.
     *
     * @param array $context An array of parameters to pass to the template
     * @param array $blocks  An array of blocks to pass to the template
     */
    public function display(array $context, array $blocks = []): void
    {
        echo $this->render($context);
    }

    /**
     * @return string
     */
    public function getTemplateName(): string
    {
        return $this->templateName;
    }

    /**
     * {@inheritdoc}
     */
    public function getDebugInfo(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceContext(): Source
    {
        return new Source('', $this->templateName);
    }

    /**
     * {@inheritdoc}
     */
    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        yield $this->legacyEngine->render($this->templateName, $context);
    }
}
