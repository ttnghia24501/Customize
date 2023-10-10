<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_BetterWishlist
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\BetterWishlist\Plugin\Customer;

use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\Manager;
use Mageplaza\BetterWishlist\Helper\Data as HelperData;

/**
 * Class MessageManager
 * @package Mageplaza\BetterWishlist\Plugin\Customer
 */
class MessageManager
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * MessageManager constructor.
     *
     * @param HelperData $helperData
     * @param RequestInterface $request
     */
    public function __construct(
        HelperData $helperData,
        RequestInterface $request
    ) {
        $this->helperData = $helperData;
        $this->request    = $request;
    }

    /**
     * @param Manager $subject
     * @param bool $clear
     * @param null $group
     *
     * @return array
     */
    public function beforeGetMessages(Manager $subject, $clear = false, $group = null)
    {
        try {
            $storeId = $this->helperData->getStore()->getId();
        } catch (Exception $e) {
            $storeId = null;
        }

        if ($this->helperData->isEnabled($storeId)
            && ($this->request->getFullActionName() === 'wishlist_index_index'
                || $this->request->getFullActionName() === 'wishlist_index_add')
            && $this->helperData->versionCompare('2.4.2')) {
            $clear = false;
        }

        return [$clear, $group];
    }
}
