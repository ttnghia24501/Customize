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
 * @category  Mageplaza
 * @package   Mageplaza_BetterWishlist
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\BetterWishlist\Plugin\Reports\Block;

use Magento\Framework\App\RequestInterface;
use Mageplaza\BetterWishlist\Helper\Data;

/**
 * Class Menu
 * @package Mageplaza\BetterWishlist\Plugin\Reports\Block
 */
class Menu
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Menu constructor.
     *
     * @param RequestInterface $request
     * @param Data $helperData
     */
    public function __construct(
        RequestInterface $request,
        Data $helperData
    ) {
        $this->request    = $request;
        $this->helperData = $helperData;
    }

    /**
     * @param \Mageplaza\Reports\Block\Menu $menu
     * @param $result
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetGridName(\Mageplaza\Reports\Block\Menu $menu, $result)
    {
        if (!$this->helperData->isEnabled()) {
            return $result;
        }
        if ($this->request->getFullActionName() === 'mpwishlist_wishlist_index') {
            $result = 'mpwishlist_wishlist_report_grid.mpwishlist_report_listing_data_source';
        }

        return $result;
    }
}
