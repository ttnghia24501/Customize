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

namespace Mageplaza\ProductFeed\Controller\Adminhtml\ManageFeeds;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Registry;
use Mageplaza\ProductFeed\Controller\Adminhtml\AbstractManageFeeds;
use Mageplaza\ProductFeed\Helper\Data;
use Mageplaza\ProductFeed\Helper\File;
use Mageplaza\ProductFeed\Model\Feed;
use Mageplaza\ProductFeed\Model\FeedFactory;
use RuntimeException;

/**
 * Class Save
 * @package Mageplaza\ProductFeed\Controller\Adminhtml\ManageFeeds
 */
class Save extends AbstractManageFeeds
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var File
     */
    protected $helperFile;

    /**
     * Save constructor.
     *
     * @param FeedFactory $feedFactory
     * @param Registry $coreRegistry
     * @param Context $context
     * @param EncryptorInterface $encryptor
     * @param Data $helperData
     * @param File $helperFile
     */
    public function __construct(
        FeedFactory $feedFactory,
        Registry $coreRegistry,
        Context $context,
        Data $helperData,
        File $helperFile,
        EncryptorInterface $encryptor
    ) {
        $this->helperData = $helperData;
        $this->helperFile = $helperFile;
        $this->encryptor  = $encryptor;

        parent::__construct($feedFactory, $coreRegistry, $context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws FileSystemException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->getRequest()->getPost('feed');
        if (isset($data['fields_map']) && $data['fields_map']) {
            $data['fields_map'] = Data::jsonEncode($data['fields_map']);
        }
        if (isset($data['category_map']) && $data['category_map']) {
            $data['category_map'] = $this->helperData->serialize($data['category_map']);
        }
        if (isset($data['cron_run_time']) && $data['cron_run_time']) {
            $data['cron_run_time'] = implode(',', $data['cron_run_time']);
        }
        if (isset($data['password']) && $data['password'] && $data['is_encryptor'] === '1') {
            $data['password'] = $this->encryptor->decrypt($data['password']);
        }

        $conditionData = $this->getRequest()->getPost('rule');
        $feed          = $this->initFeed();
        $this->_prepareData($feed, $data);
        $feed->loadPost($conditionData);

        try {
            $feed->save();
            $this->messageManager->addSuccessMessage(__('The feed has been saved.'));
            $this->_getSession()->setData('mageplaza_productfeed_feed_data', false);

            if ($this->getRequest()->getParam('back')) {
                $resultRedirect->setPath('mpproductfeed/*/edit', ['feed_id' => $feed->getId(), '_current' => true]);
            } else {
                $resultRedirect->setPath('mpproductfeed/*/');
            }

            return $resultRedirect;
        } catch (RuntimeException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Feed.'));
        }

        $this->_getSession()->setData('mageplaza_productfeed_feed_data', $data);

        $resultRedirect->setPath('mpproductfeed/*/edit', ['feed_id' => $feed->getId(), '_current' => true]);

        return $resultRedirect;
    }

    /**
     * @param Feed $feed
     * @param array $data
     *
     * @return $this
     * @throws FileSystemException
     */
    protected function _prepareData($feed, $data = [])
    {
        $data['mapping'] = Data::jsonEncode($data['mapping']);
        $this->helperFile->uploadFile(
            $data,
            'private_key_path',
            File::TEMPLATE_MEDIA_TYPE_FILE,
            $feed->getPrivateKeyPath()
        );

        $feed->addData($data);

        return $this;
    }
}
