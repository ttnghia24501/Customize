<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ProductFeed
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ProductFeed\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Class ManageFeeds
 * @package Mageplaza\ProductFeed\Block\Adminhtml
 */
class ManageFeeds extends Container
{
    /**
     * constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_feed';
        $this->_blockGroup = 'Mageplaza_ProductFeed';
        $this->_headerText = __('Manage Feeds');
        $this->_addButtonLabel = __('Add New Feed');

        parent::_construct();
    }
}
