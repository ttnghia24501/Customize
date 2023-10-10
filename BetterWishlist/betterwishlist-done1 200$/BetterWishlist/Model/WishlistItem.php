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

namespace Mageplaza\BetterWishlist\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageplaza\BetterWishlist\Model\ResourceModel\WishlistItem as WishlistItemResource;

/**
 * Class WishlistItem
 *
 * @package Mageplaza\BetterWishlist\Model
 * @method getQty()
 * @method setQty($param)
 * @method getWishlistItemId()
 * @method getTotalQty()
 * @method getCategoryName()
 * @method getCategoryId()
 * @method setWishlistItemId($getId)
 * @method getAddedAt()
 * @method setAddedAt($gmtDate)
 */
class WishlistItem extends AbstractModel
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'mageplaza_wishlist_item';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'mageplaza_wishlist_item';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_wishlist_item';

    /**
     * @var DateTime
     */
    protected $_date;

    /**
     * WishlistItem constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param DateTime $date
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DateTime $date,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_date = $date;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(WishlistItemResource::class);
    }

    /**
     * Check required data
     *
     * @return $this
     */
    public function beforeSave()
    {
        parent::beforeSave();
        // set current date if added at data is not defined
        if ($this->getAddedAt() === null) {
            $this->setAddedAt($this->_date->gmtDate());
        }

        return $this;
    }

    /**
     * @param $wishlistItemId
     * @param null $categoryId
     *
     * @return $this
     * @throws LocalizedException
     */
    public function loadItem($wishlistItemId, $categoryId = null)
    {
        $this->_beforeLoadItem($wishlistItemId, $categoryId);
        $this->_getResource()->loadItem($this, $wishlistItemId, $categoryId);
        $this->_afterLoad();
        $this->setOrigData();
        $this->_hasDataChanges = false;
        $this->updateItemStoredData();

        return $this;
    }

    /**
     * Processing object before load data
     *
     * @param $wishlistItemId
     * @param null $categoryId
     *
     * @return $this
     */
    protected function _beforeLoadItem($wishlistItemId, $categoryId = null)
    {
        $params = ['object' => $this, 'wishlist_item_id' => $wishlistItemId, 'category_id' => $categoryId];
        $this->_eventManager->dispatch('model_load_before', $params);
        $params = array_merge($params, $this->_getEventData());
        $this->_eventManager->dispatch($this->_eventPrefix . '_load_before', $params);

        return $this;
    }

    /**
     * Synchronize object's stored data with the actual data
     *
     * @return $this
     */
    private function updateItemStoredData()
    {
        if (isset($this->_data)) {
            $this->storedData = $this->_data;
        } else {
            $this->storedData = [];
        }

        return $this;
    }
}
