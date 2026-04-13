<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle;

use Ibexa\Bundle\Core\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * @var \Closure
     */
    private $legacyKernelClosure;

    /**
     * Returns the legacy kernel object.
     *
     * @return \eZ\Publish\Core\MVC\Legacy\Kernel
     */
    final protected function getLegacyKernel()
    {
        if (!isset($this->legacyKernelClosure)) {
            $this->legacyKernelClosure = $this->get('ezpublish_legacy.kernel');
        }

        $legacyKernelClosure = $this->legacyKernelClosure;

        return $legacyKernelClosure();
    }
}
