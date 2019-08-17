<?php
/**
 * Copyright © Techfactory, Inc. All rights reserved.
 * See LICENSE for license details.
 */

/**
 * Category chooser for Wysiwyg CMS widget
 */
namespace Techfactory\Recommendations\Block\Adminhtml\Category\Widget;

class Chooser extends \Magento\Catalog\Block\Adminhtml\Category\Widget\Chooser
{
    /**
     * Get JSON of a tree node or an associative array
     *
     * @param \Magento\Framework\Data\Tree\Node|array $node
     * @param int $level
     * @return array
     */
    protected function _getNodeJson($node, $level = 0)
    {
        $item = parent::_getNodeJson($node, $level);

        $item['is_anchor'] = 0;

        return $item;
    }
}
