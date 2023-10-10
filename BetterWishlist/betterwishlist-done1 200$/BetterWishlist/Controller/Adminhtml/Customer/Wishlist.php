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
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Math\Random;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\View\Result\LayoutFactory as ResultLayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\BetterWishlist\Helper\Data;
use Mageplaza\BetterWishlist\Model\CategoryFactory;
use Mageplaza\BetterWishlist\Model\WishlistItemFactory as MpWishlistItemFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Wishlist
 * @package Mageplaza\BetterWishlist\Controller\Adminhtml\Customer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Wishlist extends Index
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var MpWishlistItemFactory
     */
    protected $mpWishlistItemFactory;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Wishlist constructor.
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
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param MpWishlistItemFactory $mpWishlistItemFactory
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
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        MpWishlistItemFactory $mpWishlistItemFactory,
        CategoryFactory $categoryFactory,
        Data $helperData
    ) {
        $this->storeManager          = $storeManager;
        $this->logger                = $logger;
        $this->mpWishlistItemFactory = $mpWishlistItemFactory;
        $this->categoryFactory       = $categoryFactory;
        $this->helperData            = $helperData;

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
     * @return ResponseInterface|ResultInterface|Layout
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $customerId = $this->initCurrentCustomer();
        $itemId     = (int) $this->getRequest()->getParam('delete');
        if ($customerId && $itemId) {
            try {
                $categoryId = $this->getRequest()->getParam('categoryId');
                $this->deleteItem($categoryId, $itemId);
                $this->messageManager->addSuccessMessage(__('This item is deleted successfully'));
            } catch (Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
                $this->logger->critical($exception);
            }
        }
        $type = $this->getRequest()->getParam('type');
        if ($type && $customerId) {
            try {
                $wishlistItemId  = (int) $this->getRequest()->getParam('itemId');
                $fromCategory    = $this->getRequest()->getParam('fromCategoryId');
                $toCategory      = $this->getRequest()->getParam('toCategoryId');
                $toCategoryName  = $this->getRequest()->getParam('toCategoryName');
                $newCategory     = $this->getRequest()->getParam('newCategoryId');
                $newCategoryName = $this->getRequest()->getParam('newCategoryName');
                $itemIds         = explode(',', $this->getRequest()->getParam('itemIds'));
                if ($toCategory === 'new') {
                    $this->categoryFactory->create()->setData(
                        [
                            'customer_id'   => $customerId,
                            'category_id'   => $newCategory,
                            'category_name' => $newCategoryName,
                            'store_id'      => $this->storeManager->getStore()->getId(),
                        ]
                    )->save();
                    $toCategory     = $newCategory;
                    $toCategoryName = $newCategoryName;
                }
                $updateCount = 0;
                switch ($type) {
                    case 'move':
                        $this->moveItem($wishlistItemId, $fromCategory, $toCategory, $toCategoryName);
                        $this->messageManager->addSuccessMessage(__('This item is moved successfully'));
                        break;
                    case 'copy':
                        $this->copyItem($wishlistItemId, $fromCategory, $toCategory, $toCategoryName);
                        $this->messageManager->addSuccessMessage(__('This item is copied successfully'));
                        break;
                    case 'massmove':
                        foreach ($itemIds as $itemId) {
                            try {
                                $this->moveItem($itemId, $fromCategory, $toCategory, $toCategoryName);
                                $updateCount++;
                            } catch (Exception $e) {
                                $this->messageManager->addErrorMessage($e->getMessage());
                            }
                        }
                        $updateCount <= 1
                            ? $this->messageManager->addSuccessMessage(__('%1 item has been moved', $updateCount))
                            : $this->messageManager->addSuccessMessage(__('%1 item(s) have been moved', $updateCount));
                        break;
                    case 'masscopy':
                        foreach ($itemIds as $itemId) {
                            try {
                                $this->copyItem($itemId, $fromCategory, $toCategory, $toCategoryName);
                                $updateCount++;
                            } catch (Exception $e) {
                                $this->messageManager->addErrorMessage($e->getMessage());
                            }
                        }
                        $updateCount <= 1
                            ? $this->messageManager->addSuccessMessage(__('%1 item has been copied', $updateCount))
                            : $this->messageManager->addSuccessMessage(__('%1 items have been copied', $updateCount));

                        break;
                    case 'massdelete':
                        foreach ($itemIds as $itemId) {
                            try {
                                $this->deleteItem($fromCategory, $itemId);
                                $updateCount++;
                            } catch (Exception $e) {
                                $this->messageManager->addErrorMessage($e->getMessage());
                            }
                        }
                        $updateCount <= 1
                            ? $this->messageManager->addSuccessMessage(__('%1 item has been deleted', $updateCount))
                            : $this->messageManager->addSuccessMessage(__('%1 items have been deleted', $updateCount));
                        break;
                }
            } catch (Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
                $this->logger->critical($exception);
            }
        }
        if ($messType = $this->getRequest()->getParam('messType')) {
            $mess = $this->getRequest()->getParam('mess');
            if ($messType === 'success') {
                $this->messageManager->addSuccessMessage($mess);
            } else {
                $this->messageManager->addErrorMessage($mess);
            }
        }
        $page = $this->resultLayoutFactory->create();
        if ($this->getRequest()->getParam('reload')) {
            $page->getLayout()->unsetElement('mp.admin.customer.wishlist.category');
        }

        return $page;
    }

    /**
     * @param $wishlistItemId
     * @param $fromCategory
     * @param $toCategory
     * @param $toCategoryName
     *
     * @throws Exception
     */
    protected function copyItem($wishlistItemId, $fromCategory, $toCategory, $toCategoryName)
    {
        $this->helperData->copyItem($wishlistItemId, $fromCategory, $toCategory, $toCategoryName);
    }

    /**
     * @param $wishlistItemId
     * @param $fromCategory
     * @param $toCategory
     * @param $toCategoryName
     *
     * @throws Exception
     */
    protected function moveItem($wishlistItemId, $fromCategory, $toCategory, $toCategoryName)
    {
        $this->helperData->moveItem($wishlistItemId, $fromCategory, $toCategory, $toCategoryName);
    }

    /**
     * @param $categoryId
     * @param $itemId
     *
     * @throws Exception
     */
    protected function deleteItem($categoryId, $itemId)
    {
        $this->helperData->deleteItem($categoryId, $itemId);
    }
}
