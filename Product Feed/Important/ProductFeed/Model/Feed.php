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

namespace Mageplaza\ProductFeed\Model;

use Exception;
use Magento\Backend\Model\Session;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogRule\Model\Rule\Condition\Combine;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rule\Model\AbstractModel;
use Magento\Rule\Model\Action\Collection as RuleCollection;
use Magento\Rule\Model\Condition\Combine as RuleCombine;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\ProductFeed\Helper\Data;
use Mageplaza\ProductFeed\Model\ResourceModel\Feed as FeedResource;

/**
 * Class Feed
 * @package Mageplaza\ProductFeed\Model
 * @method getFileType()
 * @method getTemplateHtml()
 * @method getFieldSeparate()
 * @method getFieldAround()
 * @method getIncludeHeader()
 * @method getFieldsMap()
 * @method getStoreId()
 * @method getFileName()
 * @method getCampaignSource()
 * @method getCampaignMedium()
 * @method getCampaignName()
 * @method getCampaignTerm()
 * @method getCampaignContent()
 * @method getCategoryMap()
 * @method getName()
 * @method getCompressFile()
 * @method getStatus()
 * @method getCronRunTime()
 * @method getLastCron()
 * @method getFrequency()
 * @method getCronRunDayOfWeek()
 * @method getCronRunDayOfMonth()
 * @method getDeliveryEnable()
 * @method getPassiveMode()
 * @method getPassword()
 * @method getPrivateKeyPath()
 * @method getUserName()
 * @method getHostName()
 * @method getDirectoryPath()
 * @method getProtocol()
 * @method getMapping()
 * @method getRequestUrl()
 * @method getHeaders()
 */
class Feed extends AbstractModel
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'mageplaza_productfeed_feed';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'mageplaza_productfeed_feed';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_productfeed_feed';

    /**
     * @var Iterator
     */
    protected $resourceIterator;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var array
     */
    protected $productCollection = [];

    /**
     * @var array
     */
    protected $productIds;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Session
     */
    protected $backendSession;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * Feed constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TimezoneInterface $localeDate
     * @param Iterator $resourceIterator
     * @param ProductFactory $productFactory
     * @param RequestInterface $request
     * @param Session $backendSession
     * @param Data $helperData
     * @param StoreManagerInterface $storeManager
     * @param CategoryFactory $categoryFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context               $context,
        Registry              $registry,
        FormFactory           $formFactory,
        TimezoneInterface     $localeDate,
        Iterator              $resourceIterator,
        ProductFactory        $productFactory,
        RequestInterface      $request,
        Session               $backendSession,
        Data                  $helperData,
        StoreManagerInterface $storeManager,
        CategoryFactory       $categoryFactory,
        AbstractResource      $resource = null,
        AbstractDb            $resourceCollection = null,
        array                 $data = []
    ) {
        $this->resourceIterator = $resourceIterator;
        $this->productFactory   = $productFactory;
        $this->request          = $request;
        $this->backendSession   = $backendSession;
        $this->helperData       = $helperData;
        $this->storeManager     = $storeManager;
        $this->categoryFactory  = $categoryFactory;

        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }

    /**
     * @return Combine|RuleCollection|RuleCombine
     */
    public function getConditionsInstance()
    {
        return $this->getActionsInstance();
    }

    /**
     * @return Combine|RuleCollection
     */
    public function getActionsInstance()
    {
        return ObjectManager::getInstance()->create(Combine::class);
    }

    /**
     * @return array|null
     * @throws LocalizedException
     */
    public function getMatchingProductIds()
    {
        if ($this->productIds === null) {
            $data                = $this->request->getPost('rule') ?: $this->request->getParam('rule');
            $feedData            = $this->request->getPost('feed') ?: $this->request->getParam('feed');
            $limitProductPreview = null;
            if ($feedData && array_key_exists('preview_limit', $feedData)) {
                $limitProductPreview = $feedData['preview_limit'];
            }
            $storeId = isset($this->request->getPost('feed')['store_id'])
                ? $this->request->getPost('feed')['store_id']
                : $this->getStoreId();

            if ($data) {
                $this->backendSession->setProductFeedData(['rule' => $data, 'store_id' => $storeId]);
            } elseif ($productFeedData = $this->backendSession->getProductFeedData()) {
                $data    = $productFeedData['rule'];
                $storeId = $productFeedData['store_id'];
            }

            if (!$data) {
                $data = [];
            }
            $this->loadPost($data);

            if ($storeId === null) {
                $storeId = $this->getStoreId();
            }

            $this->productIds = [];
            $this->setCollectedAttributes([]);

            /** @var $productCollection Collection */
            $productCollection = $this->productFactory->create()->getCollection()->addAttributeToSelect('*')
                ->addAttributeToFilter('status', 1)->addStoreFilter($storeId);
            if ($storeId && $categoryIds = $this->getCategoryIds($storeId)) {
                $productCollection->addCategoriesFilter(['in' => $categoryIds]);
            }
            $productCollection
                ->joinField(
                    'qty',
                    'cataloginventory_stock_item',
                    'qty',
                    'product_id=entity_id',
                    '{{table}}.stock_id=1',
                    'left'
                )->joinField(
                    'is_in_stock',
                    'cataloginventory_stock_item',
                    'is_in_stock',
                    'product_id=entity_id',
                    '{{table}}.stock_id=1',
                    'left'
                );

            $this->setConditionsSerialized($this->helperData->serialize($this->getConditions()->asArray()));
            $this->getConditions()->collectValidatedAttributes($productCollection);

            if ($limitProductPreview) {
                $this->getValidateProducts($productCollection, $limitProductPreview);
            } else {
                $this->resourceIterator->walk(
                    $productCollection->getSelect(),
                    [[$this, 'callbackValidateProductConditions']],
                    [
                        'attributes' => $this->getCollectedAttributes(),
                        'product'    => $this->productFactory->create(),
                    ]
                );
            }
        }

        return $this->productIds;
    }

    /**
     * @param int $storeId
     *
     * @return array|string|null
     */
    public function getCategoryIds($storeId)
    {
        try {
            $rootCategoryId = $this->storeManager->getStore($storeId)->getRootCategoryId();
            $rootCategory   = $this->categoryFactory->create()->load($rootCategoryId);

            return $rootCategory->getAllChildren(true);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param $productCollection
     * @param $limitProductPreview
     *
     * @return void
     */
    public function getValidateProducts($productCollection, $limitProductPreview)
    {
        foreach ($productCollection as $product) {
            if ($this->checkLimit($limitProductPreview)) {
                $product->setData('quantity_and_stock_status', $product->getData('qty'));
                $product->setData('mp_is_in_stock', $product->getData('is_in_stock'));
                if ($this->getConditions()->validate($product)) {
                    $this->productIds[] = $product->getId();
                }
            } else {
                break;
            }
        };
    }

    /**
     * @param $limitProductPreview
     *
     * @return bool
     */
    public function checkLimit($limitProductPreview)
    {
        if ($limitProductPreview) {
            return count($this->productIds) < $limitProductPreview;
        }

        return true;
    }

    /**
     * Callback function for product matching (conditions)
     *
     * @param array $args
     *
     * @return void
     */
    public function callbackValidateProductConditions($args)
    {
        $product                                  = clone $args['product'];
        $args['row']['quantity_and_stock_status'] = $args['row']['qty'];
        $args['row']['mp_is_in_stock']            = $args['row']['is_in_stock'];
        $product->setData($args['row']);

        if ($this->getConditions()->validate($product)) {
            $this->productIds[] = $product->getId();
        }
    }

    /**
     * @param array $ids
     */
    public function setMatchingProductIds($ids)
    {
        $this->productIds = $ids;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(FeedResource::class);
    }
}
