<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\FieldType\Page;

/**
 * Legacy page service stub — Page/Flow field type removed in Ibexa 5.
 * PageServicePass guards with hasDefinition() so this class is never instantiated.
 */
class PageService
{
    public function getLayoutTemplate(string $layoutIdentifier): string
    {
        return '';
    }
}
