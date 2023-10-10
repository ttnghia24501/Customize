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

namespace Mageplaza\BetterWishlist\Controller\Adminhtml\Customer;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Controller\Adminhtml\Index;
use Magento\Customer\Helper\View;
use Magento\Customer\Model\Address\Mapper;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\Customer\Mapper as CustomerMapper;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Math\Random;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Result\LayoutFactory as ResultLayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Wishlist\Model\WishlistFactory;
use Mageplaza\BetterWishlist\Helper\Data;
use Mageplaza\BetterWishlist\Model\CategoryFactory;
use Mageplaza\BetterWishlist\Model\WishlistItem;
use Mageplaza\BetterWishlist\Model\WishlistItemFactory;

/**
 * Class AddProducts
 * @package Mageplaza\BetterWishlist\Controller\Adminhtml\Customer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddProducts extends Index
{
    /**
     * @var WishlistFactory
     */
    protected $wishlistFactory;

    /**
     * @var WishlistItemFactory
     */
    protected $wishlistItemFactory;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * AddProducts constructor.
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param FileFactory $fileFactory
     * @param CustomerFactory $customerFactory
     * @param AddressFactory $addressFactory
     * @param FormFactory $formFactory
     * @param SubscriberFactory $subscriberFactory
     * @param View $viewHelper
     * @param Random $random
     * @param CustomerRepositoryInterface $customerRepository
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param Mapper $addressMapper
     * @param AccountManagementInterface $customerAccountManagement
     * @param AddressRepositoryInterface $addressRepository
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param AddressInterfaceFactory $addressDataFactory
     * @param CustomerMapper $customerMapper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectFactory $objectFactory
     * @param LayoutFactory $layoutFactory
     * @param ResultLayoutFactory $resultLayoutFactory
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param JsonFactory $resultJsonFactory
     * @param WishlistFactory $wishlistFactory
     * @param WishlistItemFactory $wishlistItemFactory
     * @param CategoryFactory $categoryFactory
     * @param Data $helperData
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        FileFactory $fileFactory,
        CustomerFactory $customerFactory,
        AddressFactory $addressFactory,
        FormFactory $formFactory,
        SubscriberFactory $subscriberFactory,
        View $viewHelper,
        Random $random,
        CustomerRepositoryInterface $customerRepository,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        Mapper $addressMapper,
        AccountManagementInterface $customerAccountManagement,
        AddressRepositoryInterface $addressRepository,
        CustomerInterfaceFactory $customerDataFactory,
        AddressInterfaceFactory $addressDataFactory,
        CustomerMapper $customerMapper,
        DataObjectProcessor $dataObjectProcessor,
        DataObjectHelper $dataObjectHelper,
        DataObjectFactory $objectFactory,
        LayoutFactory $layoutFactory,
        ResultLayoutFactory $resultLayoutFactory,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        JsonFactory $resultJsonFactory,
        WishlistFactory $wishlistFactory,
        WishlistItemFactory $wishlistItemFactory,
        CategoryFactory $categoryFactory,
        Data $helperData
    ) {
        $this->wishlistFactory     = $wishlistFactory;
        $this->wishlistItemFactory = $wishlistItemFactory;
        $this->categoryFactory     = $categoryFactory;
        $this->helperData          = $helperData;

        parent::__construct(
            $context,
            $coreRegistry,
            $fileFactory,
            $customerFactory,
            $addressFactory,
            $formFactory,
            $subscriberFactory,
            $viewHelper,
            $random,
            $customerRepository,
            $extensibleDataObjectConverter,
            $addressMapper,
            $customerAccountManagement,
            $addressRepository,
            $customerDataFactory,
            $addressDataFactory,
            $customerMapper,
            $dataObjectProcessor,
            $dataObjectHelper,
            $objectFactory,
            $layoutFactory,
            $resultLayoutFactory,
            $resultPageFactory,
            $resultForwardFactory,
            $resultJsonFactory
        );
    }

    /**
     * Execute
     *
     * @return Redirect
     */
    public function execute()
    {
        $customerId = $this->initCurrentCustomer();
        $categoryId = $this->getRequest()->getParam('categoryId');
        $items      = $this->getRequest()->getParam('item');
        // Update wishlist item
        $updateResult = new DataObject();
        try {
            $categoryId = $categoryId !== 'all' ? $categoryId : $this->helperData->getDefaultCategory()->getId();
            /**
             * @var \Mageplaza\BetterWishlist\Model\Category $category
             */
            $category = $this->categoryFactory->create()->loadByCategoryId($categoryId, $customerId);
            $prdCount = 0;

            if (isset($items)) {
                foreach ($items as $productId => $buyRequest) {
                    $buyRequest['product'] = $productId;
                    $buyRequest            = new DataObject($buyRequest);
                    $item                  = $this->wishlistFactory->create()->loadByCustomerId($customerId, true);
                    $wishlistItem          = $item->addNewItem($productId, $buyRequest);
                    /**
                     * @var WishlistItem $mpWishlistItem
                     */
                    $mpWishlistItem = $this->wishlistItemFactory->create()
                        ->loadItem($wishlistItem->getId(), $categoryId);
                    $mpWishlistItem->addData(
                        [
                            'wishlist_item_id' => $wishlistItem->getId(),
                            'category_id'      => $categoryId,
                            'category_name'    => $category->getCategoryName(),
                            'qty'              =>
                                $mpWishlistItem->getQty() + ($buyRequest['qty'] ?: 1)
                        ]
                    )->save();
                    $prdCount++;
                }
            }

            $updateResult->setMessage([
                'type' => 'success',
                'mess' => __('%1 item(s) have been added to the wishlist', $prdCount)
            ]);
            $updateResult->setOk(true);
        } catch (Exception $e) {
            $updateResult->setError(true);
            $updateResult->setMessage(['type' => 'error', 'mess' => $e->getMessage()]);
        }
        $updateResult->setJsVarName($this->getRequest()->getParam('as_js_varname'));
        $this->_session->setCompositeProductResult($updateResult);

        return $this->resultRedirectFactory->create()->setPath('catalog/product/showUpdateResult');
    }
}
