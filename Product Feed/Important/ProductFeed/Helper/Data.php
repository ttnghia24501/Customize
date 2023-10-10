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

namespace Mageplaza\ProductFeed\Helper;

use DateTime;
use DateTimeZone;
use Exception;
use Liquid\Tag\TagFor;
use Liquid\Template;
use Liquid\Variable;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\UrlInterface;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Config\Model\ResourceModel\Config as ModelConfig;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory as ConfigCollectionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Driver\File as DriverFile;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem\Io\Ftp;
use Magento\Framework\Filesystem\Io\Sftp;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\DateTime as StdlibDateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Url as UrlAbstract;
use Magento\Review\Model\Review;
use Magento\Review\Model\Review\SummaryFactory;
use Magento\Review\Model\ReviewFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Mageplaza\Core\Helper\AbstractData as CoreHelper;
use Mageplaza\ProductFeed\Block\Adminhtml\LiquidFilters;
use Mageplaza\ProductFeed\Block\Liquid\Tag\TagFor as ProductFeedLiquidTagFor;
use Mageplaza\ProductFeed\Model\Config\Source\Delivery;
use Mageplaza\ProductFeed\Model\Config\Source\Events;
use Mageplaza\ProductFeed\Model\Config\Source\ProductPath;
use Mageplaza\ProductFeed\Model\Config\Source\Status;
use Mageplaza\ProductFeed\Model\Feed;
use Mageplaza\ProductFeed\Model\FeedFactory;
use Mageplaza\ProductFeed\Model\HistoryFactory;
use RuntimeException;
use Zend_Http_Client;
use Zend_Http_Response;

/**
 * Class Data
 * @package Mageplaza\ProductFeed\Helper
 */
class Data extends CoreHelper
{
    const CONFIG_MODULE_PATH    = 'product_feed';
    const XML_PATH_EMAIL        = 'email';
    const FEED_FILE_PATH        = BP . '/pub/media/mageplaza/feed/';
    const FEED_KEY_FILE_PATH    = BP . '/pub/media/mageplaza/feed/key_file/';
    const GOOGLE_SHOPPING_URL   = 'https://shoppingcontent.googleapis.com/content/v2.1/';
    const GOOGLE_SHOPPING_TOKEN = 'https://oauth2.googleapis.com/token';

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var LiquidFilters
     */
    protected $liquidFilters;

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var Ftp
     */
    protected $ftp;

    /**
     * @var Sftp
     */
    protected $sftp;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var StdlibDateTime
     */
    protected $date;

    /**
     * @var HistoryFactory
     */
    protected $historyFactory;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var SummaryFactory
     */
    protected $reviewSummaryFactory;

    /**
     * @var FeedFactory
     */
    protected $feedFactory;

    /**
     * @var Resolver
     */
    protected $resolver;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var UrlInterface
     */
    protected $backendUrl;

    /**
     * @var StockRegistryInterface
     */
    protected $stockState;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var CollectionFactory
     */
    protected $prdAttrCollectionFactory;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var DriverFile
     */
    protected $driverFile;

    /**
     * @var UrlAbstract
     */
    protected $urlModel;

    /**
     * @var UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * @var CurlFactory
     */
    protected $curlFactory;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var ModelConfig
     */
    protected $modelConfig;

    /**
     * @var Mapping
     */
    protected $helperMapping;

    /**
     * @var ConfigCollectionFactory
     */
    protected $configCollectionFactory;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var CatalogHelper
     */
    protected $catalogHelper;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param ProductFactory $productFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param UrlInterface $backendUrl
     * @param Ftp $ftp
     * @param Sftp $sftp
     * @param ManagerInterface $messageManager
     * @param TransportBuilder $transportBuilder
     * @param StdlibDateTime $date
     * @param TimezoneInterface $timezone
     * @param Resolver $resolver
     * @param File $file
     * @param ReviewFactory $reviewFactory
     * @param SummaryFactory $reviewSummaryFactory
     * @param StockRegistryInterface $stockState
     * @param LiquidFilters $liquidFilters
     * @param HistoryFactory $historyFactory
     * @param FeedFactory $feedFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param CollectionFactory $prdAttrCollectionFactory
     * @param UrlFinderInterface $urlFinder
     * @param Session $session
     * @param DriverFile $driverFile
     * @param UrlAbstract $urlModel
     * @param CurlFactory $curlFactory
     * @param EncryptorInterface $encryptor
     * @param ModelConfig $modelConfig
     * @param Mapping $helperMapping
     * @param ConfigCollectionFactory $configCollectionFactory
     * @param ProductRepository $productRepository
     * @param CatalogHelper $catalogHelper
     * @param Escaper $escaper
     * @param DirectoryList $directoryList
     * @param Curl $curl
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        ProductFactory $productFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        UrlInterface $backendUrl,
        Ftp $ftp,
        Sftp $sftp,
        ManagerInterface $messageManager,
        TransportBuilder $transportBuilder,
        StdlibDateTime $date,
        TimezoneInterface $timezone,
        Resolver $resolver,
        File $file,
        ReviewFactory $reviewFactory,
        SummaryFactory $reviewSummaryFactory,
        StockRegistryInterface $stockState,
        LiquidFilters $liquidFilters,
        HistoryFactory $historyFactory,
        FeedFactory $feedFactory,
        PriceCurrencyInterface $priceCurrency,
        CollectionFactory $prdAttrCollectionFactory,
        UrlFinderInterface $urlFinder,
        Session $session,
        DriverFile $driverFile,
        UrlAbstract $urlModel,
        CurlFactory $curlFactory,
        EncryptorInterface $encryptor,
        ModelConfig $modelConfig,
        Mapping $helperMapping,
        ConfigCollectionFactory $configCollectionFactory,
        ProductRepository $productRepository,
        CatalogHelper $catalogHelper,
        Escaper $escaper,
        DirectoryList $directoryList,
        Curl $curl,
        ProductMetadataInterface $productMetadata
    ) {
        $this->productFactory            = $productFactory;
        $this->file                      = $file;
        $this->liquidFilters             = $liquidFilters;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->ftp                       = $ftp;
        $this->sftp                      = $sftp;
        $this->messageManager            = $messageManager;
        $this->date                      = $date;
        $this->historyFactory            = $historyFactory;
        $this->transportBuilder          = $transportBuilder;
        $this->reviewFactory             = $reviewFactory;
        $this->reviewSummaryFactory      = $reviewSummaryFactory;
        $this->feedFactory               = $feedFactory;
        $this->resolver                  = $resolver;
        $this->timezone                  = $timezone;
        $this->backendUrl                = $backendUrl;
        $this->stockState                = $stockState;
        $this->priceCurrency             = $priceCurrency;
        $this->prdAttrCollectionFactory  = $prdAttrCollectionFactory;
        $this->session                   = $session;
        $this->driverFile                = $driverFile;
        $this->urlModel                  = $urlModel;
        $this->urlFinder                 = $urlFinder;
        $this->curlFactory               = $curlFactory;
        $this->encryptor                 = $encryptor;
        $this->modelConfig               = $modelConfig;
        $this->helperMapping             = $helperMapping;
        $this->configCollectionFactory   = $configCollectionFactory;
        $this->productRepository         = $productRepository;
        $this->catalogHelper             = $catalogHelper;
        $this->escaper                   = $escaper;
        $this->directoryList             = $directoryList;
        $this->curl                      = $curl;
        $this->productMetadata           = $productMetadata;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * Convert to local time
     *
     * @param string $time
     *
     * @return DateTime|string
     * @throws Exception
     */
    public function convertToLocaleTime($time)
    {
        $localTime = new DateTime($time, new DateTimeZone('UTC'));
        $localTime->setTimezone(new DateTimeZone($this->timezone->getConfigTimezone()));
        $localTime = $localTime->format('Y-m-d H:i:s');

        return $localTime;
    }

    /**
     * Test connection
     *
     * @param string $protocol
     * @param string $host
     * @param string $passive
     * @param string $user
     * @param string $pass
     * @param string $path
     *
     * @return int
     */
    public function testConnection($protocol, $host, $passive, $user, $pass, $path)
    {
        try {
            if ($protocol === 'sftp') {
                if (strpos($host, ':') !== false) {
                    [$host, $port] = explode(':', $host, 2);
                } else {
                    $port = Sftp::SSH2_PORT;
                }

                if ($this->productMetadata->getVersion() < '2.4.4') {
                    $connection = new \phpseclib\Net\SFTP($host, $port, 10);
                    if ($path) {
                        $privateKey = new \phpseclib\Crypt\RSA();
                        $privateKey->setPassword($pass);
                        $privateKey->loadKey($this->driverFile->fileGetContents($path));
                        $pass = $privateKey;
                    }

                    return $connection->login($user, $pass) ? 1 : 0;
                } else {

                    $connection = new \phpseclib3\Net\SFTP($host, $port, 100);

                    if ($path) {
                        $privateKey = \phpseclib3\Crypt\RSA::loadPrivateKey(
                            $this->driverFile->fileGetContents($path),
                            $pass
                        );
                        $pass       = $privateKey;
                    }

                    return $connection->login($user, $pass) ? 1 : 0;
                }

            }

            $open = $this->ftp->open([
                'host'     => $host,
                'user'     => $user,
                'password' => $pass,
                'passive'  => $passive
            ]);

            return $open ? 1 : 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Process request
     *
     * @param Feed $feed
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function processRequest($feed)
    {
        $step = $this->_getRequest()->getParam('step');
        if ($step === 'prepare_generate') {
            return $this->prepareGenerate($feed);
        }

        if ($step === 'prepare_product_data') {
            return $this->prepareProductData($feed);
        }

        if ($step === 'render') {
            $this->generateAndDeliveryFeed($feed, true, false, true);
            $this->messageManager->getMessages(true);
            $this->driverFile->deleteDirectory(self::FEED_FILE_PATH . 'collection/' . $feed->getId() . '/');

            return [
                'complete' => true,
            ];
        }

        return [
            'error'   => true,
            'message' => __('Something went wrong while generating feed')
        ];
    }

    public function processSyncData($feed)
    {
        $step = $this->_getRequest()->getParam('step');
        if ($step === 'prepare_sync') {
            return $this->prepareGenerate($feed);
        }

        if ($step === 'sync_product_data') {
            return $this->syncProducts($feed);
        }
    }

    /**
     * Prepare generate
     *
     * @param Feed $feed
     *
     * @return array
     * @throws LocalizedException
     */
    public function prepareGenerate($feed)
    {
        $feedId = $feed->getId();
        $this->resetFeedSessionData($feedId);
        $template = $this->prepareTemplate($feed, null, true);
        $root     = $template->getRoot();
        $prdAttr  = [];
        $prdAttr  = $this->getProductAttr($root->getNodelist(), $prdAttr);
        $this->setFeedSessionData($feedId, 'product_attributes', $prdAttr);
        $productIds = $feed->getMatchingProductIds();
        $chunk      = array_chunk($productIds, 1000);
        $this->setFeedSessionData($feedId, 'product_chunk', $chunk);
        try {
            $this->driverFile->deleteDirectory(self::FEED_FILE_PATH . 'collection/' . $feedId . '/');
        } catch (Exception $e) {
            return [
                'product_count' => count($productIds)
            ];
        }

        return [
            'product_count' => count($productIds)
        ];
    }

    /**
     * Reset Feed session data
     *
     * @param string|int $feedId
     */
    public function resetFeedSessionData($feedId)
    {
        $this->session->setData("mp_product_feed_data_{$feedId}", null);
    }


    /**
     * Prepare template
     *
     * @param Feed $feed
     * @param $templateHtml
     * @param bool $saveCache
     * @param bool $isUseCache
     * @param bool $isCron
     *
     * @return Template
     * @throws LocalizedException
     */
    public function prepareTemplate(
        $feed,
        $templateHtml = null,
        $saveCache = false,
        $isUseCache = false,
        $isCron = false
    ) {
        $template = new Template;

        $template->registerFilter($this->liquidFilters);
        $templateHtml = $templateHtml ?: $this->getTemplateHtml($feed);

        if ($isUseCache) {
            $template->registerTag('for', ProductFeedLiquidTagFor::class);
        }

        if ($saveCache) {
            $this->setFeedSessionData($feed->getId(), 'template_html', $templateHtml);
        }

        if ($isCron) {
            $this->file->checkAndCreateFolder(self::FEED_FILE_PATH . 'cron/template/');
            $fileUrl = self::FEED_FILE_PATH . 'cron/template/' . $feed->getId();
            $this->file->write($fileUrl, $templateHtml);
        }

        $template->parse($templateHtml);

        return $template;
    }

    /**
     * Get Html template
     *
     * @param Feed $feed
     *
     * @return string
     */
    public function getTemplateHtml($feed)
    {
        $fileType     = $feed->getFileType();
        $templateHtml = '';

        if ($fileType === 'xml') {
            $templateHtml = $feed->getTemplateHtml();
        } else {
            $fieldSeparate = $feed->getFieldSeparate() === 'tab' ? "\t"
                : ($feed->getFieldSeparate() === 'comma' ? ',' : ';');
            $fieldAround   = $feed->getFieldAround() === 'none' ? ''
                : ($feed->getFieldAround() === 'quote' ? "'" : '"');
            $includeHeader = $feed->getIncludeHeader();
            $fieldsMap     = self::jsonDecode($feed->getFieldsMap());
            if ($fieldsMap) {
                $row = [];
                foreach ($fieldsMap as $field) {
                    $row[0][] = $field['col_name'];

                    if ($field['col_type'] === 'attribute') {
                        $row[1][] = $fieldAround . $field['col_val'] . $fieldAround;
                    } else {
                        $row[1][] = $fieldAround . $field['col_pattern_val'] . $fieldAround;
                    }
                }

                $row[0] = implode($fieldSeparate, $row[0]);
                $row[1] = implode($fieldSeparate, $row[1]);

                if ($includeHeader) {
                    $templateHtml = $row[0] . '
' . '{% for product in products %}' . $row[1] . '
{% endfor %}';
                } else {
                    $templateHtml = '{% for product in products %}' . $row[1] . '
{% endfor %}';
                }

                $templateHtml = str_replace(
                    '}}',
                    "| mpCorrect: '" . $feed->getFieldAround() . "', '" . $feed->getFieldSeparate() . "'}}",
                    $templateHtml
                );
            }
        }

        return $templateHtml;
    }

    /**
     * Set Feed session data
     *
     * @param $feedId
     * @param $path
     * @param $value
     */
    public function setFeedSessionData($feedId, $path, $value)
    {
        $data        = $this->session->getData("mp_product_feed_data_{$feedId}");
        $data[$path] = $value;
        $this->session->setData("mp_product_feed_data_{$feedId}", $data);
    }

    /**
     * Get product attribute
     *
     * @param array $nodeList
     * @param array $prdAttr
     *
     * @return array
     */
    public function getProductAttr($nodeList, $prdAttr)
    {
        /** @var Variable|TagFor $node */
        foreach ($nodeList as $node) {
            if (!is_object($node)) {
                continue;
            }
            if ($node instanceof Variable && strncmp($node->getName(), 'product.', 8) === 0) {
                $prdAttr[] = str_replace('product.', '', $node->getName());
            } elseif (method_exists($node, 'getNodelist')) {
                // phpcs:disable Magento2.Performance.ForeachArrayMerge
                $prdAttr = array_merge($this->getProductAttr($node->getNodelist(), $prdAttr), $prdAttr);
            }
        }

        return $prdAttr;
    }

    /**
     * Prepare data of product
     *
     * @param Feed $feed
     * @param bool $isSync
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function prepareProductData($feed)
    {
        $feedId       = $feed->getId();
        $productAttr  = $this->getFeedSessionData($feedId, 'product_attributes');
        $productChunk = $this->getFeedSessionData($feedId, 'product_chunk');
        $productCount = (int) $this->getFeedSessionData($feedId, 'product_count');
        $ids          = array_shift($productChunk);
        $collection   = $this->getProductsData($feed, $productAttr, $ids);
        $productCount += count($collection);
        $name         = $ids ? current($ids) . end($ids) : '0';
        $this->createFeedCollectionFile($feedId, self::jsonEncode($collection), $name);
        $this->setFeedSessionData($feedId, 'product_chunk', $productChunk);
        $this->setFeedSessionData($feedId, 'product_count', $productCount);

        return [
            'complete'      => empty($productChunk),
            'product_count' => $productCount
        ];
    }

    /**
     * @param $feed
     * @param bool $isSync
     *
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function syncProducts($feed, $isSync = true)
    {
        $feedId       = $feed->getId();
        $productAttr  = $this->getFeedSessionData($feedId, 'product_attributes');
        $productChunk = $this->getFeedSessionData($feedId, 'product_chunk');
        $productCount = (int) $this->getFeedSessionData($feedId, 'product_count');
        $ids          = array_shift($productChunk);
        $collection   = $this->getProductsData($feed, $productAttr, $ids, $isSync);
        $collection->walk([$this, 'syncProduct'], [$feed]);

        $productCount += $collection->getSize();
        $this->setFeedSessionData($feedId, 'product_chunk', $productChunk);
        $this->setFeedSessionData($feedId, 'product_count', $productCount);

        return [
            'complete'      => empty($productChunk),
            'product_count' => $productCount
        ];
    }

    /**
     * @param $product
     * @param $args
     *
     * @throws NoSuchEntityException
     */
    public function syncProduct($product, $args)
    {
        $this->syncProductToGoogleShopping($args, $product);
    }

    /**
     * Get Feed session data
     *
     * @param $feedId
     * @param $path
     *
     * @return mixed|null
     */
    public function getFeedSessionData($feedId, $path)
    {
        $data = $this->session->getData("mp_product_feed_data_{$feedId}");

        return isset($data[$path]) ? $data[$path] : null;
    }

    /**
     * Get products data
     *
     * @param Feed $feed
     * @param array $productAttributes
     * @param array $productIds
     * @param bool $isSync
     *
     * @return array | Collection
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getProductsData($feed, $productAttributes = [], $productIds = [], $isSync = false)
    {
        $categoryMap = $this->unserialize($feed->getCategoryMap());
        $storeId     = $feed->getStoreId() ?: $this->storeManager->getDefaultStoreView()->getId();

        $allCategory = $this->categoryCollectionFactory->create();
        $allCategory->setStoreId($storeId)->addAttributeToSelect('name');
        $categoriesName = [];
        /** @var $item Category */
        foreach ($allCategory as $item) {
            $categoriesName[$item->getId()] = $item->getName();
        }

        $allSelectProductAttributes = $this->prdAttrCollectionFactory->create()
            ->addFieldToFilter('frontend_input', ['in' => ['multiselect', 'select']])
            ->getColumnValues('attribute_code');

        $matchingProductIds = !empty($productIds) ? $productIds : $feed->getMatchingProductIds();
        $productCollection  = $this->productFactory->create()->getCollection()
            ->addAttributeToSelect($productAttributes)->addStoreFilter($storeId)
            ->addFieldToFilter('entity_id', ['in' => $matchingProductIds])->addMediaGalleryData();

        $result = [];
        /** @var $product Product */
        foreach ($productCollection as $product) {
            $typeInstance           = $product->getTypeInstance();
            $childProductCollection = $typeInstance->getAssociatedProducts($product);
            if ($childProductCollection) {
                $associatedData = [];
                foreach ($childProductCollection as $item) {
                    $associatedData = $item->getData();
                }
                $product->setAssociatedProducts($associatedData);
            } else {
                $product->setAssociatedProducts([]);
            }

            $stockItem       = $this->stockState->getStockItem(
                $product->getId(),
                $feed->getStoreId()
            );
            $qty             = $stockItem->getQty();
            $categories      = $product->getCategoryCollection()->addAttributeToSelect('*');
            $relatedProducts = [];
            foreach ($product->getRelatedProducts() as $item) {
                $relatedProducts[] = $item->getData();
            }
            $crossSellProducts = [];
            foreach ($product->getCrossSellProducts() as $item) {
                $crossSellProducts[] = $item->getData();
            }
            $upSellProducts = [];
            foreach ($product->getUpSellProducts() as $item) {
                $upSellProducts[] = $item->getData();
            }
            try {
                $oriProduct = $this->productRepository->getById($product->getId(), false, $storeId);
            } catch (Exception $e) {
                $oriProduct = $this->productFactory->create()->setStoreId($storeId)->load($product->getId());
            }

            $finalPrice  = $this->catalogHelper->getTaxPrice($oriProduct, $oriProduct->getFinalPrice(), true);
            $finalPrice  = $this->convertPrice($finalPrice, $storeId);
            $productLink = $this->getProductUrl($oriProduct, $storeId) . $this->getCampaignUrl($feed);
            if (!$this->getConfigGeneral('reports')) {
                $productLink = $this->getProductUrl($oriProduct, $storeId);
            }
            $imageLink = $oriProduct->getImage() ? $this->storeManager->getStore($storeId)
                    ->getBaseUrl(UrlAbstract::URL_TYPE_MEDIA)
                . 'catalog/product' . $oriProduct->getImage() : '';
            $images    = $oriProduct->getMediaGalleryImages()->getSize() ?
                $oriProduct->getMediaGalleryImages() : [[]];
            if (is_object($images)) {
                $imagesData = [];
                foreach ($images->getItems() as $item) {
                    $imagesData[] = $item->getData();
                }
                $images = $imagesData;
            }
            /** @var $category Category */
            $lv             = 0;
            $categoryPath   = '';
            $cat            = new DataObject();
            $categoriesData = [];
            foreach ($categories as $category) {
                if ($lv < $category->getLevel()) {
                    $lv  = $category->getLevel();
                    $cat = $category;
                }
                $categoriesData[] = $category->getData();
            }
            $mapping = '';
            if (isset($categoryMap[$cat->getId()])) {
                $mapping = $categoryMap[$cat->getId()];
            }
            $catPaths = $cat->getPathInStore() ? array_reverse(explode(',', $cat->getPathInStore())) : [];
            foreach ($catPaths as $index => $catId) {
                if ($index === (count($catPaths) - 1)) {
                    $categoryPath .= isset($categoriesName[$catId]) ? $categoriesName[$catId] : '';
                } else {
                    $categoryPath .= (isset($categoriesName[$catId]) ? $categoriesName[$catId] : '') . ' > ';
                }
            }

            $oriProduct->isAvailable() ? $product->setData('quantity_and_stock_status', 'in stock')
                : $product->setData('quantity_and_stock_status', 'out of stock');

            $noneAttr = [
                'categoryCollection',
                'relatedProducts',
                'crossSellProducts',
                'upSellProducts',
                'final_price',
                'link',
                'image_link',
                'images',
                'category_path',
                'mapping',
                'qty',
            ];

            // Convert attribute value to attribute text
            foreach ($productAttributes as $attributeCode) {
                try {
                    if ($attributeCode === 'quantity_and_stock_status'
                        || in_array($attributeCode, $noneAttr, true)
                        || !in_array($attributeCode, $allSelectProductAttributes, true)
                        || !$product->getData($attributeCode)
                    ) {
                        continue;
                    }
                    $attributeText = $product->getResource()->getAttribute($attributeCode)
                        ->setStoreId($feed->getStoreId())->getFrontend()->getValue($product);
                    if (is_array($attributeText)) {
                        $attributeText = implode(',', $attributeText);
                    }
                    if ($attributeText) {
                        $product->setData($attributeCode, $attributeText);
                    }
                } catch (Exception $e) {
                    continue;
                }
            }

            $product->setData('categoryCollection', $categoriesData);
            $product->setData('relatedProducts', $relatedProducts);
            $product->setData('crossSellProducts', $crossSellProducts);
            $product->setData('upSellProducts', $upSellProducts);
            $product->setData('final_price', $finalPrice);
            $product->setData('link', $productLink);
            $product->setData('image_link', $imageLink);
            $product->setData('images', $images);
            $product->setData('category_path', $categoryPath);
            $product->setData('mapping', $mapping);
            $product->setData('qty', $qty);
            $result[] = self::jsonDecode(self::jsonEncode($product->getData()));
        }

        if ($isSync) {
            return $productCollection;
        }

        return $result;
    }

    /**
     * @param Feed $feed
     *
     * @return string
     */
    public function feedKeyEncode($feed)
    {
        return base64_encode($this->encryptor->encrypt((string) $feed->getId()));
    }

    /**
     * Convert price
     *
     * @param int $amount
     * @param int $storeId
     *
     * @return float|int
     * @throws NoSuchEntityException
     */
    public function convertPrice($amount = 0, $storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getStoreId();
        }

        return (float) $this->priceCurrency->convert($amount, $storeId);
    }

    /**
     * Get product view url
     *
     * @param Product $product
     * @param int $storeId
     *
     * @return string
     */
    public function getProductUrl($product, $storeId)
    {
        $productURlConfig = $this->getConfigGeneral('product_path');
        $categoryIds      = $product->getCategoryIds();
        $categoryIds      = array_diff($categoryIds, ['2']);
        $categories       = $this->categoryCollectionFactory->create()->addFieldToSelect('is_active')
            ->setStoreId($storeId)
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('entity_id', ['in' => $categoryIds])
            ->addOrder('level', 'asc');

        switch ($productURlConfig) {
            case ProductPath::SHORT:
                $categoryId = $categories->getFirstItem()->getEntityId();
                $product->setCategoryId($categoryId);
                $productUrl = $product->getUrlModel()->getUrl($product, ['_scope_to_url' => false]);
                break;
            case ProductPath::LONG:
                $categoryId = $categories->getLastItem()->getEntityId();
                $product->setCategoryId($categoryId);
                $productUrl = $product->getUrlModel()->getUrl($product, ['_scope_to_url' => false]);
                break;
            default:
                $useCatagoriesConfig = $this->getConfigValue('catalog/seo/product_use_categories', $storeId);
                if ($useCatagoriesConfig) {
                    $categoryId = $categories->getFirstItem()->getEntityId();
                    $product->setCategoryId($categoryId);
                }
                $productUrl = $product->getUrlModel()->getUrl($product, ['_scope_to_url' => false]);
                break;
        }

        $productUrl = strtok($productUrl, '?');

        return $productUrl;
    }

    /**
     * @param Feed $feed
     *
     * @return string
     */
    public function getCampaignUrl($feed)
    {
        $campaignUrl = '?mp_feed=' . $this->feedKeyEncode($feed);
        $campaignUrl .= $feed->getCampaignSource() ? '&utm_source=' . $feed->getCampaignSource() : '';
        $campaignUrl .= $feed->getCampaignMedium() ? '&utm_medium=' . $feed->getCampaignMedium() : '';
        $campaignUrl .= $feed->getCampaignName() ? '&utm_campaign=' . $feed->getCampaignName() : '';
        $campaignUrl .= $feed->getCampaignTerm() ? '&utm_term=' . $feed->getCampaignTerm() : '';
        $campaignUrl .= $feed->getCampaignContent() ? '&utm_content=' . $feed->getCampaignContent() : '';

        return $campaignUrl;
    }

    /**
     * @param Feed $feed
     *
     * @return bool
     */
    public function useGoogleShoppingApi($feed)
    {
        return $feed->getFileType() === 'xml'
            && $this->getConfigGeneral('google_shopping/enabled')
            && $this->getConfigGeneral('google_shopping/merchant_id')
            && $this->getConfigGeneral('google_shopping/client_id')
            && $this->getConfigGeneral('google_shopping/client_secret');
    }

    /**
     * @param Feed $feed
     * @param Product $product
     *
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function syncProductToGoogleShopping(Feed $feed, Product $product)
    {
        $mapping = self::jsonDecode($feed->getMapping());
        $record  = [];
        foreach ($mapping as $field => $mappingField) {
            $record[$field] = $this->processMappingField($mappingField, $product);
        }
        $record['offerId']              = $record['id'];
        $record['price']                = [
            'value'    => $record['price'],
            'currency' => $this->storeManager->getStore($feed->getStoreId())->getCurrentCurrencyCode()
        ];
        $promotionIds                   = $record['promotionIds'];
        $productTypes                   = $record['productTypes'];
        $record['promotionIds']         = [];
        $record['productTypes']         = [];
        $record['additionalImageLinks'] = [];
        $record['promotionIds'][]       = $promotionIds;
        $record['productTypes'][]       = $productTypes;
        foreach ($product->getData('images') as $image) {
            $record['additionalImageLinks'][] = isset($image['url']) ? $image['url'] : '';
        }

        if (!$record['identifierExists']) {
            $record['identifierExists'] = false;
        }

        $merchantId = $this->getMerchantId();
        $url        = self::GOOGLE_SHOPPING_URL . $merchantId . '/products';
        try {
            $this->sendRequest($url, 'POST', $record);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param array $mappingField
     * @param Product $product
     *
     * @return string
     */
    public function processMappingField($mappingField, $product)
    {
        $value = $mappingField['value'];
        if ($mappingField['value']) {
            $data       = $this->helperMapping->matchData($mappingField['value']);
            $dataFields = [];
            foreach ($data as $field) {
                if (!isset($dataFields[$field])) {
                    $currentValue = $this->processProductField($field, $product);

                    if ($currentValue && !is_array($currentValue)) {
                        $value = $this->replaceValue($field, $currentValue, $value);
                    }

                    $dataFields[$field] = $currentValue;
                }
            }

            if (!$value) {
                $value = $mappingField['default'];
            }

            return $this->formatValue($value, $mappingField['type']);
        }

        return $mappingField['default'];
    }

    /**
     * @param string $field
     * @param Product $product
     *
     * @return mixed
     */
    public function processProductField($field, $product)
    {
        if ($field === 'manufacturer') {
            return $product->getData($field);
        }

        if ($field === 'description') {
            return $this->escaper->escapeHtml($product->getData($field));
        }

        if ($field === 'quantity_and_stock_status') {
            return $product->getData($field);
        }

        if ($product->getResource()) {
            if ($productAttribute = $product->getResource()->getAttribute($field)) {
                return $productAttribute->getFrontend()->getValue($product);
            }
        }

        return $product->getData($field);
    }

    /**
     * @param string $search
     * @param string $replace
     * @param string $value
     *
     * @return mixed
     */
    public function replaceValue($search, $replace, $value)
    {
        return str_replace('{{' . $search . '}}', $replace, $value);
    }

    /**
     * @param mixed $value
     * @param string $type
     *
     * @return string
     */
    public function formatValue($value, $type)
    {
        if ($value) {
            /** Replace all option match in {{}} */
            $value = preg_replace(Mapping::PATTERN_OPTIONS, '', $value);
        }

        switch ($type) {
            case 'int':
                $value = (int) $value;
                break;
            case 'float':
                $value = (float) $value;
                break;
            case 'boolean':
                $value = (bool) $value;
                break;
            case 'string':
                $value = (string) $value;
                break;
        }

        return $value;
    }

    /**
     * @return string
     */
    public function getMerchantId()
    {
        $merchantId = $this->getConfigGeneral('google_shopping/merchant_id');

        return $this->decrypt($merchantId);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function decrypt($value)
    {
        return $this->encryptor->decrypt($value);
    }

    /**
     * @param string $url
     * @param string $method
     * @param string $params
     * @param bool $isAccessToken
     *
     * @return mixed
     * @throws Exception
     */
    public function sendRequest($url, $method, $params = '', $isAccessToken = false)
    {
        $this->checkRefreshAccessToken();

        return $this->requestData($url, $method, $params, $isAccessToken);
    }

    /**
     * Check and refresh access token after one hour
     *
     * @throws Exception
     */
    public function checkRefreshAccessToken()
    {
        $lastRequestToken = (int) $this->getLastRequestToken();

        if ($lastRequestToken + 3600 < time()) {
            $refreshData = [
                'refresh_token' => $this->getRefreshToken(),
                'client_id'     => $this->getClientId(),
                'client_secret' => $this->getClientSecret(),
                'grant_type'    => 'refresh_token'
            ];

            $resp = $this->requestData(
                self::GOOGLE_SHOPPING_TOKEN,
                Zend_Http_Client::POST,
                http_build_query($refreshData),
                true
            );

            if (isset($resp['access_token'])) {
                $this->saveAPIData($resp, true);
            } else {
                throw new LocalizedException(__('Cannot refresh access token.'));
            }
        }
    }

    /**
     * Load config without cache
     *
     * @return string
     */
    public function getLastRequestToken()
    {
        $config = $this->configCollectionFactory->create()
            ->addFieldToFilter('path', $this->getMpProductFeedShoppingPathByKey('last_request_token'))
            ->getFirstItem();
        if ($config->getId()) {
            return $config->getValue();
        }

        return '';
    }

    /**
     * @param string $field
     *
     * @return string
     */
    public function getMpProductFeedShoppingPathByKey($field)
    {
        return self::CONFIG_MODULE_PATH . '/general/google_shopping/' . $field;
    }

    /**
     * @return mixed|string
     */
    public function getRefreshToken()
    {
        $accessData = $this->getAccessData();
        if (isset($accessData['refresh_token'])) {
            return $accessData['refresh_token'];
        }

        return '';
    }

    /**
     * @return array|mixed
     */
    public function getAccessData()
    {
        $accessData = $this->getConfigGeneral('google_shopping/access_data');

        if ($accessData) {
            return self::jsonDecode($this->decrypt($accessData));
        }

        return [];
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        $clientId = $this->getConfigGeneral('google_shopping/client_id');

        return $this->decrypt($clientId);
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        $clientSecret = $this->getConfigGeneral('google_shopping/client_secret');

        return $this->decrypt($clientSecret);
    }

    /**
     * @param Feed $feed
     *
     * @return int|void
     * @throws LocalizedException
     */
    public function prepareRunCron($feed)
    {
        $template = $this->prepareTemplate($feed, null, false, true, true);
        $root     = $template->getRoot();
        $prdAttr  = [];
        $prdAttr  = $this->getProductAttr($root->getNodelist(), $prdAttr);
        $this->createFile($feed->getId(), $prdAttr, 'prdAttr');
        $productIds = $feed->getMatchingProductIds();
        $chunk      = array_chunk($productIds, 1000);
        $this->createFile($feed->getId(), $chunk, 'productIds');

        return count($productIds);
    }

    /**
     * @param $url
     * @param string $method
     * @param string $params
     * @param false $isAccessToken
     *
     * @return array|mixed
     * @throws Exception
     */
    public function requestData($url, $method = Zend_Http_Client::POST, $params = '', $isAccessToken = false)
    {
        $httpAdapter = $this->curlFactory->create();
        if ($isAccessToken) {
            $headers = [
                'Host: oauth2.googleapis.com',
                'Content-Type: application/x-www-form-urlencoded'
            ];
        } else {
            $headers = [
                'Content-Type:application/json',
                'Authorization: Bearer ' . $this->getAccessToken()
            ];
        }

        if (($method === Zend_Http_Client::POST && !$isAccessToken)) {
            $params = self::jsonEncode($params);
        }

        $httpAdapter->write($method, $url, '1.1', $headers, $params);
        $result   = $httpAdapter->read();
        $response = Zend_Http_Response::extractBody($result);
        $response = self::jsonDecode($response);
        if (isset($response['error'])) {
            throw new Exception($response['error_description']);
        }
        $httpAdapter->close();

        return $response;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        $config = $this->configCollectionFactory->create()
            ->addFieldToFilter('path', $this->getMpProductFeedShoppingPathByKey('access_token'))
            ->getFirstItem();
        if ($config->getValue()) {
            return $this->decrypt($config->getValue());
        }

        return '';
    }

    /**
     * @param array $resp
     * @param bool $isRefreshToken
     */
    public function saveAPIData($resp, $isRefreshToken = false)
    {
        if ($isRefreshToken) {
            $resp['refresh_token'] = $this->getRefreshToken();
        }

        $accessToken = $this->encryptor->encrypt($resp['access_token']);
        $accessData  = $this->encryptor->encrypt(self::jsonEncode($resp));
        $this->saveConfig($this->getMpProductFeedShoppingPathByKey('access_token'), $accessToken);
        $this->saveConfig($this->getMpProductFeedShoppingPathByKey('access_data'), $accessData);
        $this->saveConfig($this->getMpProductFeedShoppingPathByKey('last_request_token'), time());
    }

    /**
     * @param string $path
     * @param string $value
     */
    public function saveConfig($path, $value)
    {
        $this->modelConfig->saveConfig($path, $value);
    }

    /**
     * Create file of Feed collection
     *
     * @param int|string $feedId
     * @param string $content
     * @param string $name
     *
     * @throws LocalizedException
     */
    public function createFeedCollectionFile($feedId, $content, $name)
    {
        $this->file->checkAndCreateFolder(self::FEED_FILE_PATH . 'collection/' . $feedId);
        $fileUrl = self::FEED_FILE_PATH . 'collection/' . $feedId . '/' . $name;
        $this->file->write($fileUrl, $content);
    }

    /**
     * Create file to save data
     *
     * @param int $feedId
     * @param $content
     * @param string $path
     *
     * @throws LocalizedException
     */
    public function createFile($feedId, $content, $path)
    {
        switch ($path) {
            case 'prdAttr':
                $this->file->checkAndCreateFolder(self::FEED_FILE_PATH . 'cron/prdAttr/');
                $fileUrl = self::FEED_FILE_PATH . 'cron/prdAttr/' . $feedId;
                $this->file->write($fileUrl, $this->serialize($content));
                break;
            case 'productIds':
                $this->file->checkAndCreateFolder(self::FEED_FILE_PATH . 'cron/productIds/');
                $fileUrl = self::FEED_FILE_PATH . 'cron/productIds/' . $feedId;
                $this->file->write($fileUrl, $this->serialize($content));
                break;
        }
    }

    /**
     * Generate and delivery Feed
     *
     * @param Feed $feed
     * @param bool $forceGenerate
     * @param bool $isCron
     * @param bool $isUseCache
     *
     * @throws Exception
     */
    public function generateAndDeliveryFeed($feed, $forceGenerate = false, $isCron = false, $isUseCache = false)
    {
        if (!$this->isEnabled()) {
            $this->messageManager->addErrorMessage(__('Please enable Mageplaza_ProductFeed module'));

            return;
        }
        if (!$forceGenerate && !$feed->getStatus()) {
            return;
        }

        $status       = Status::ERROR;
        $delivery     = Delivery::ERROR;
        $productCount = 0;
        try {
            $productCount = $this->generateLiquidTemplate($feed, $isUseCache, $isCron);
            if ($isCron) {
                $productCount = count($feed->getMatchingProductIds());
            }
            $this->messageManager->addSuccessMessage(__('%1 feed has been generated successfully.', $feed->getName()));
            $this->feedFactory->create()->load($feed->getId())->setLastGenerated($this->date->date())->save();
            $status = Status::SUCCESS;
        } catch (LocalizedException | RuntimeException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while generating %1 Feed. %2', $feed->getName(), $e->getMessage())
            );
        }
        if ($status === Status::SUCCESS) {
            if ($feed->getDeliveryEnable()) {
                try {
                    $this->deliveryFeed($feed);
                    $this->messageManager->addSuccessMessage(
                        __('%1 feed has been uploaded successfully', $feed->getName())
                    );
                    $delivery = Delivery::SUCCESS;
                } catch (LocalizedException | RuntimeException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (Exception $e) {
                    $this->messageManager->addErrorMessage(
                        __('Something went wrong while uploading %1 Feed. %2', $feed->getName(), $e->getMessage())
                    );
                }
            } else {
                $delivery = Delivery::DISABLED;
            }
        }
        $successMessage = [];
        $errorMessage   = [];

        foreach ($this->messageManager->getMessages()->getItems() as $message) {
            if (strpos($message->getText(), $feed->getName()) !== false) {
                if ($message->getType() === 'success') {
                    $successMessage[] = $message->getText();
                } else {
                    $errorMessage[] = $message->getText();
                }
            }
        }
        $successMessage = implode("\n", $successMessage);
        $errorMessage   = implode("\n", $errorMessage);

        if ($this->getEmailConfig('enabled')) {
            $generateStt = $status === Status::SUCCESS ? Events::GENERATE_SUCCESS : Events::GENERATE_ERROR;
            $generateMes = $generateStt === Events::GENERATE_SUCCESS
                ? ('<p style="color: green">' . __('%1 feed generated successful', $feed->getName()) . '</p>')
                : ('<p style="color: red">' . __('%1 feed generated fail', $feed->getName()) . '</p>');
            $deliveryStt = $delivery === Delivery::SUCCESS
                ? Events::DELIVERY_SUCCESS
                : ($delivery === Delivery::ERROR ? Events::DELIVERY_ERROR : Events::DELIVERY_DISABLED);
            $deliveryMes = $deliveryStt === Events::DELIVERY_SUCCESS
                ? '<p style="color: green">' . __('%1 feed delivery successful', $feed->getName()) . '</p>'
                : ($deliveryStt === Events::DELIVERY_ERROR
                    ? ('<p style="color: red">' . __('%1 feed delivery fail', $feed->getName()) . '</p>') : '');
            $events      = explode(',', $this->getEmailConfig('events'));
            $sendTo      = empty($this->getEmailConfig('send_to'))
                ? null : explode(',', $this->getEmailConfig('send_to'));
            if (in_array($generateStt, $events, true) || in_array($deliveryStt, $events, true)) {
                $this->sendMail(
                    $sendTo,
                    $generateMes . $deliveryMes,
                    'product_feed_email_template',
                    $feed->getStoreId()
                );
            }
        }

        $history = $this->historyFactory->create();
        $history->setData([
            'feed_id'         => $feed->getId(),
            'feed_name'       => $feed->getName(),
            'status'          => $status,
            'delivery'        => $delivery,
            'type'            => $isCron ? 'cron' : 'manual',
            'product_count'   => $productCount,
            'file'            => $feed->getFileName() . '.' . $feed->getFileType(),
            'success_message' => $successMessage,
            'error_message'   => $errorMessage
        ])->save();

        if ($isCron) {
            $this->messageManager->getMessages()->clear();
        }
    }

    /**
     * Generate Liquid template
     *
     * @param Feed $feed
     * @param bool $isUseCache
     * @param bool $isCron
     *
     * @return int|mixed|void|null
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function generateLiquidTemplate($feed, $isUseCache = false, $isCron = false)
    {
        $feedId       = $feed->getId();
        $templateHtml = $isUseCache ? $this->getFeedSessionData($feedId, 'template_html') : null;
        $template     = $this->prepareTemplate($feed, $templateHtml, false, $isUseCache);
        if ($isUseCache) {
            $prdAttr = $this->getFeedSessionData($feedId, 'product_attributes');
        } elseif ($isCron) {
            $prdAttr = $this->driverFile->fileGetContents(Data::FEED_FILE_PATH . 'cron/prdAttr/' . $feed->getId());
            $prdAttr = $this->unserialize($prdAttr);
        } else {
            $prdAttr = [];
            $root    = $template->getRoot();
            $prdAttr = $this->getProductAttr($root->getNodelist(), $prdAttr);
        }

        $productCollectionData = ($isUseCache || $isCron)
            ? []
            : $this->getProductsData($feed, $prdAttr);

        $reviewCollection = $this->getReviewCollection($feed);
        $content          = $template->render([
            'products' => $productCollectionData,
            'store'    => $this->getStoreData($feed->getStoreId()),
            'reviews'  => $reviewCollection,
            'feed_id'  => $feedId
        ]);

        $this->createFeedFile($feed, $content);

        return $isUseCache ? $this->getFeedSessionData($feedId, 'product_count') : count($productCollectionData);
    }

    /**
     * Get review collection
     *
     * @param $feed
     *
     * @return AbstractCollection
     * @throws Exception
     */
    public function getReviewCollection($feed)
    {
        $matchingProductIds = $feed->getMatchingProductIds();
        $collection         = $this->reviewFactory->create()->getCollection()
            ->addFieldToFilter('entity_pk_value', ['in' => $matchingProductIds])->addRateVotes();
        $storeId            = $feed->getStoreId() ?: $this->storeManager->getDefaultStoreView()->getId();
        /** @var $review Review */
        foreach ($collection as $review) {
            $review->setUrl($review->getReviewUrl());
            $product = $this->productRepository->getById($review->getEntityPkValue(), false, $storeId);

            $manufacturer = $product->getAttributeText('manufacturer');
            $product->setData('manufacturer', $manufacturer);
            $productUrl = $this->getProductUrl($product, $storeId);
            if ($this->getConfigGeneral('reports')) {
                $productUrl .= $this->getCampaignUrl($feed);
            }
            $product->setUrl($productUrl);

            $rateVotesData = $review->getRatingVotes()->getData();

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $timezone = $objectManager->get('Magento\Framework\Stdlib\DateTime\TimezoneInterface');

            $createdAt = $this->timezone->date(new \DateTime($review->getCreatedAt()));
            $review->setData('review_timestamp', $createdAt->format(DateTime::ATOM));
            if (count($rateVotesData)) {
                $totalRate = 0;
                foreach($rateVotesData as $rateVote) {
                    $totalRate += $rateVote['percent'];
                }
                $rating = $totalRate / (20 * count($rateVotesData));

                $review->setRating($rating);
            } else {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $voteCollection = $objectManager->create('Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory')->create();
                $voteCollection->setReviewFilter(
                    $review->getId()
                )->addRatingInfo()->load();
                $totalRate = 0;
                foreach($voteCollection as $vote) {
                    $totalRate += $vote->getPercent();
                }
                $rating = $totalRate / (20 * $voteCollection->getSize());
                $review->setRating($rating);
            }

            $review->setProduct($product);
        }
        return $collection;
    }

    /**
     * Get data store
     *
     * @param string|int $id
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getStoreData($id)
    {
        $store  = $this->storeManager->getStore($id);
        $locale = $this->resolver->getLocale();

        return [
            'locale_code' => $locale,
            'base_url'    => $store->getBaseUrl()
        ];
    }

    /**
     * Create Feed file
     *
     * @param Feed $feed
     * @param string $content
     *
     * @throws Exception
     */
    public function createFeedFile($feed, $content)
    {
        $this->file->checkAndCreateFolder(self::FEED_FILE_PATH);
        $fileName = $feed->getFileName() . '.' . $feed->getFileType();
        $fileUrl  = self::FEED_FILE_PATH . '/' . $fileName;
        $this->file->write($fileUrl, $content);
    }

    /**
     * Delivery Feed
     *
     * @param Feed $feed
     *
     * @throws LocalizedException
     */
    public function deliveryFeed($feed)
    {
        $protocol       = $feed->getProtocol();
        $host           = $feed->getHostName();
        $username       = $feed->getUserName();
        $password       = $feed->getPassword();
        $privateKeyPath = $feed->getPrivateKeyPath();
        $timeout        = '20';
        $passiveMode    = $feed->getPassiveMode();
        $fileName       = $feed->getFileName() . '.' . $feed->getFileType();
        $fileUrl        = $this->getFileUrl($fileName);
        $directoryPath  = $feed->getDirectoryPath() . $fileName;

        if ($feed->getProtocol() === 'sftp') {
            if (!$host || !$username || !$password) {
                throw new RuntimeException(__('Please check the Delivery information again.'));
            }
            if (strpos($host, ':') !== false) {
                [$host, $port] = explode(':', $host, 2);
            } else {
                $port = Sftp::SSH2_PORT;
            }
            if ($this->productMetadata->getVersion() < '2.4.4') {
                $connection = new \phpseclib\Net\SFTP($host, $port, $timeout);
                if ($privateKeyPath) {
                    $privateKey = new \phpseclib\Crypt\RSA();
                    $privateKey->setPassword($password);
                    $privateKey->loadKey($this->driverFile->fileGetContents(
                        self::FEED_KEY_FILE_PATH . $privateKeyPath
                    ));
                    $password = $privateKey;
                }

                if (!$connection->login($username, $password)) {
                    throw new RuntimeException(__('Unable to open SFTP connection as %1@%2', $username, $host));
                }

                $content = $this->file->read($fileUrl);
                $mode    = $this->driverFile->isReadable($fileName)
                    ? \phpseclib\Net\SFTP::SOURCE_LOCAL_FILE : \phpseclib\Net\SFTP::SOURCE_STRING;
                $connection->put($directoryPath, $content, $mode);
                $connection->disconnect();
            } else {
                $connection = new \phpseclib3\Net\SFTP($host, $port, $timeout);
                if ($privateKeyPath) {
                    $privateKey = \phpseclib3\Crypt\RSA::loadPrivateKey(
                        $this->driverFile->fileGetContents(self::FEED_KEY_FILE_PATH . $privateKeyPath),
                        $password
                    );
                    $password   = $privateKey;
                }

                if (!$connection->login($username, $password)) {
                    throw new RuntimeException(__('Unable to open SFTP connection as %1@%2', $username, $host));
                }

                $content = $this->file->read($fileUrl);
                $mode    = $this->driverFile->isReadable($fileName)
                    ? \phpseclib3\Net\SFTP::SOURCE_LOCAL_FILE : \phpseclib3\Net\SFTP::SOURCE_STRING;
                $connection->put($directoryPath, $content, $mode);
                $connection->disconnect();
            }
        }
        if ($protocol == 'ftp') {
            if (!$host || !$username || !$password) {
                throw new RuntimeException(__('Please check the Delivery information again.'));
            }
            $open = $this->ftp->open([
                'host'     => $host,
                'user'     => $username,
                'password' => $password,
                'passive'  => $passiveMode
            ]);
            if ($open) {
                $content = $this->file->read($fileUrl);
                $this->ftp->write($directoryPath, $content);
                $this->ftp->close();
            } else {
                throw new RuntimeException(__('Unable to authenticate with server'));
            }
        }
        if ($protocol == 'http_server') {
            $this->sendHttpRequest($feed);
        }

        if (in_array($protocol, ['api', 'graphql'])) {
            $this->sendApiGraphQL($feed);
        }
    }

    /**
     * @param Feed $feed
     *
     * @return array
     */
    protected function getHeader($feed)
    {
        $ruleHeaders = $feed->getHeaders() ? explode("\n", $feed->getHeaders()) : [];
        $header      = [];
        foreach ($ruleHeaders as $item) {
            $key   = $item;
            $value = '';
            if (strpos($item, ':') !== false) {
                [$key, $value] = explode(':', $key);
                $header[trim($key)] = trim($value);
            }
            $header[trim($key)] = trim($value);
        }

        return $header;
    }

    /**
     * Send HTTP Server request
     *
     * @param Feed $feed
     *
     * @throws FileSystemException
     */
    protected function sendHttpRequest($feed)
    {
        $requestUrl = $feed->getRequestUrl();
        $header     = $this->getHeader($feed);

        if (!$requestUrl) {
            throw new RuntimeException(__('Please check the Delivery information again.'));
        }

        $content = $this->getFileContent($feed);
        $curl    = $this->curlFactory->create();

        $curl->write(Zend_Http_Client::POST, $requestUrl, '1.1', $header, $content);
        $resultCurl = $curl->read();

        if (!empty($resultCurl)) {
            $result['status'] = Zend_Http_Response::extractCode($resultCurl);
            if (isset($result['status']) && in_array($result['status'], [200, 201])) {
                $result['success'] = true;
            } else {
                throw new RuntimeException(__('Cannot connect to server. Please try again later.'));
            }
        } else {
            throw new RuntimeException(__('Cannot connect to server. Please try again later.'));
        }

        $curl->close();
    }

    /**
     * Delivery feed via API/GraphQL
     *
     * @param Feed $feed
     */
    protected function sendApiGraphQL($feed)
    {
        $requestUrl = $feed->getRequestUrl();
        $header     = $this->getHeader($feed);
        $fileName   = $feed->getFileName() . '.' . $feed->getFileType();
        $fileUrl    = $this->getFileUrl($fileName);

        if (!$requestUrl) {
            throw new RuntimeException(__('Please check the Delivery information again.'));
        }

        if ($feed->getProtocol() == 'api') {
            $params = [
                'fileUrl' => $fileUrl
            ];
            $body   = $this->serialize($params);
        } else {
            $params = [
                'query' => '{mpProductFeedFile(fileUrl: "' . $fileUrl . '") { response }}',
            ];
            $body   = $this->serialize($params);
        }

        $this->curl->setHeaders($header);
        $this->curl->post($requestUrl, $body);

        $status = $this->curl->getStatus();

        if (!in_array($status, [200, 201])) {
            throw new RuntimeException(__('Cannot connect to server. Please try again later.'));
        }
    }

    /**
     * @param string $filename
     *
     * @return string
     * @throws FileSystemException
     */
    public function getFilePath($filename)
    {
        return $this->directoryList->getPath(DirectoryList::MEDIA)
            . '/mageplaza/feed/' . $filename;
    }

    /**
     * @param $feed
     *
     * @return string
     * @throws FileSystemException
     */
    protected function getFileContent($feed)
    {
        $fileName = $feed->getFileName() . '.' . $feed->getFileType();
        $filePath = $this->getFilePath($fileName);

        return $this->driverFile->fileGetContents($filePath) ?: '';
    }

    /**
     * Get url file
     *
     * @param string $filename
     *
     * @return string
     */
    public function getFileUrl($filename)
    {
        return $this->_urlBuilder->getBaseUrl([
                '_type' => UrlAbstract::URL_TYPE_MEDIA
            ]) . 'mageplaza/feed/' . $filename;
    }

    /**
     * Get email config
     *
     * @param string $code
     * @param int $storeId
     *
     * @return mixed
     */
    public function getEmailConfig($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getModuleConfig(self::XML_PATH_EMAIL . $code, $storeId);
    }

    /**
     * Send mail
     *
     * @param array $sendTo
     * @param string $mes
     * @param string $emailTemplate
     * @param int $storeId
     *
     * @return bool
     * @throws Exception
     */
    public function sendMail($sendTo, $mes, $emailTemplate, $storeId)
    {
        if (!isset($sendTo)) {
            $this->messageManager->addErrorMessage(__('Please enter the email before send.'));

            return false;
        }
        try {
            $sendTo = array_map('trim', $sendTo);
            $this->transportBuilder
                ->setTemplateIdentifier($emailTemplate)
                ->setTemplateOptions([
                    'area'  => Area::AREA_FRONTEND,
                    'store' => $storeId,
                ])
                ->setTemplateVars([
                    'viewLogUrl' => $this->backendUrl->getUrl('mpproductfeed/logs/'),
                    'mes'        => $mes
                ])
                ->setFrom('general');
            foreach ($sendTo as $email) {
                $this->transportBuilder->addTo($email);
            }
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();

            return true;
        } catch (MailException $e) {
            $this->_logger->critical($e->getLogMessage());
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__(
                'Something went wrong while sending Email. %1',
                $e->getMessage()
            ));
        }

        return false;
    }

    /**
     * @param $collection
     * @param null $storeId
     *
     * @return mixed
     */
    public function addStoreFilter($collection, $storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getDefaultStoreView()->getId();
        }

        $storeFilter = [
            ['finset' => Store::DEFAULT_STORE_ID]
        ];

        if (is_array($storeId)) {
            foreach ($storeId as $id) {
                if ($id == 0) {
                    return $collection;
                } else {
                    $storeFilter[] = ['finset' => $id];
                }
            }
        } else {
            $storeFilter[] = ['finset' => $storeId];
        }

        $collection->addFieldToFilter('store_id', $storeFilter);

        return $collection;
    }

    /**
     * Get paths of Feed collection
     *
     * @param string|int $feedId
     *
     * @return string[]
     * @throws FileSystemException
     */
    public function getFeedCollectionPaths($feedId)
    {
        $directoryUrl = self::FEED_FILE_PATH . 'collection/' . $feedId . '/';
        $paths        = $this->driverFile->readDirectory($directoryUrl);

        usort($paths, function ($pathA, $pathB) {
            $pathArrayA = explode('/', $pathA);
            $valueA     = end($pathArrayA);

            $pathArrayB = explode('/', $pathB);
            $valueB     = end($pathArrayB);

            return $valueB < $valueA ? 1 : -1;
        });

        return $paths;
    }

    /**
     * Read file
     *
     * @param string $path
     *
     * @return bool|string
     */
    public function readFile($path)
    {
        return $this->file->read($path);
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getRedirectURIs()
    {
        $redirectUris = $this->getConfigGeneral('google_shopping/redirect_URIs');

        return $redirectUris ?: $this->getAuthorizedRedirectURIs();
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getAuthorizedRedirectURIs()
    {
        $storeId = $this->getScopeUrl();
        /** @var Store $store */
        $store = $this->storeManager->getStore($storeId);

        return $this->_getUrl('mpproductfeed/index/token', [
            '_nosid'  => true,
            '_scope'  => $storeId,
            '_secure' => $store->isUrlSecure()
        ]);
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    protected function getScopeUrl()
    {
        $scope = $this->_request->getParam(ScopeInterface::SCOPE_STORE) ?: $this->storeManager->getStore()->getId();

        if ($website = $this->_request->getParam(ScopeInterface::SCOPE_WEBSITE)) {
            $scope = $this->storeManager->getWebsite($website)->getDefaultStore()->getId();
        }

        return $scope;
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function feedKeyDecode($key)
    {
        return $this->encryptor->decrypt(base64_decode((string) $key));
    }

    /**
     * @param int $number
     * @param int $precision
     *
     * @return string
     */
    public function shortenNumber($number, $precision = 1)
    {
        if ($number < 900) {
            $number_format = number_format($number, $precision);
            $suffix        = '';
        } elseif ($number < 900000) {
            $number_format = number_format($number / 1000, $precision);
            $suffix        = 'K';
        } elseif ($number < 900000000) {
            $number_format = number_format($number / 1000000, $precision);
            $suffix        = 'M';
        } elseif ($number < 900000000000) {
            $number_format = number_format($number / 1000000000, $precision);
            $suffix        = 'B';
        } else {
            $number_format = number_format($number / 1000000000000, $precision);
            $suffix        = 'T';
        }

        if ($precision > 0) {
            $dotzero       = '.' . str_repeat('0', $precision);
            $number_format = str_replace($dotzero, '', $number_format);
        }

        return $number_format . $suffix;
    }

    /**
     * @param string $format
     *
     * @return array
     */
    public function getDateRange($format = null)
    {
        try {
            if ($dateRange = $this->_request->getParam('dateRange')) {
                $startDate = $format ? $this->formatDate($format, $dateRange[0]) : $dateRange[0];
                $endDate   = $format ? $this->formatDate($format, $dateRange[1]) : $dateRange[1];
            } else {
                [$startDate, $endDate] = $this->getDateTimeRangeFormat('-1 month', 'now', null, $format);
            }
        } catch (Exception $e) {
            $this->_logger->critical($e);

            return [null, null];
        }

        return [$startDate, $endDate];
    }

    /**
     * @param string $startDate
     * @param string $endDate
     * @param bool $isConvertToLocalTime
     * @param string $format
     *
     * @return array
     * @throws Exception
     */
    public function getDateTimeRangeFormat($startDate, $endDate = null, $isConvertToLocalTime = null, $format = null)
    {
        $endDate   = (new DateTime($endDate ?: $startDate, new DateTimeZone($this->getTimezone())))->setTime(
            23,
            59,
            59
        );
        $startDate = (new DateTime($startDate, new DateTimeZone($this->getTimezone())))->setTime(00, 00, 00);

        if ($isConvertToLocalTime) {
            $startDate->setTimezone(new DateTimeZone('UTC'));
            $endDate->setTimezone(new DateTimeZone('UTC'));
        }

        return [$startDate->format($format ?: 'Y-m-d H:i:s'), $endDate->format($format ?: 'Y-m-d H:i:s')];
    }

    /**
     * @return array|mixed
     */
    public function getTimezone()
    {
        return $this->getConfigValue('general/locale/timezone');
    }
}
