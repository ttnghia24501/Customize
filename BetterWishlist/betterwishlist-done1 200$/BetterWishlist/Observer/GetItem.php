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

namespace Mageplaza\BetterWishlist\Observer;

use Magento\AdminNotification\Model\FeedFactory;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;

/**
 * Class GetItem
 * @package Mageplaza\BetterWishlist\Observer
 */
class GetItem implements ObserverInterface
{
    /**
     * @var FeedFactory
     */
    protected $_feedFactory;

    /**
     * @var Session
     */
    protected $_backendAuthSession;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * GetItem constructor.
     *
     * @param FeedFactory $feedFactory
     * @param Session $backendAuthSession
     * @param Registry $registry
     */
    public function __construct(
        FeedFactory $feedFactory,
        Session $backendAuthSession,
        Registry $registry
    ) {
        $this->_feedFactory        = $feedFactory;
        $this->_backendAuthSession = $backendAuthSession;
        $this->registry            = $registry;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $items = $observer->getData('items');
        if (isset($items[0])) {
            $this->registry->unregister('mp_wishlist_item');
            $this->registry->register('mp_wishlist_item', $items[0]);
        }
    }
}
