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

use Exception;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\Media;

/**
 * Class File
 * @package Mageplaza\ProductFeed\Helper
 */
class File extends Media
{
    const CONFIG_MODULE_PATH       = 'product_feed';
    const TEMPLATE_MEDIA_PATH      = 'mageplaza/feed';
    const TEMPLATE_MEDIA_TYPE_FILE = 'key_file';

    /**
     * @var UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * File constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param Filesystem $filesystem
     * @param UploaderFactory $uploaderFactory
     * @param AdapterFactory $imageFactory
     *
     * @throws FileSystemException
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        UploaderFactory $uploaderFactory,
        AdapterFactory $imageFactory
    ) {
        $this->_uploaderFactory = $uploaderFactory;

        parent::__construct($context, $objectManager, $storeManager, $filesystem, $uploaderFactory, $imageFactory);
    }

    /**
     * @param array $data
     * @param string $fileName
     * @param string $type
     * @param null $oldFile
     *
     * @return $this
     * @throws FileSystemException
     */
    public function uploadFile(&$data, $fileName, $type = '', $oldFile = null)
    {
        if (isset($data[$fileName]['delete']) && $data[$fileName]['delete']) {
            if ($oldFile) {
                $this->removeFile($oldFile, $type);
            }
            $data['private_key_path'] = '';
        } else {
            try {
                $uploader = $this->_uploaderFactory->create(['fileId' => $fileName]);
                $uploader->setAllowRenameFiles(true);
                $uploader->setAllowCreateFolders(true);
                $path         = $this->getBaseMediaPath($type);
                $uploadedFile = $uploader->save(
                    $this->mediaDirectory->getAbsolutePath($path)
                );
                if ($oldFile) {
                    $this->removeFile($oldFile, $type);
                }
                $data['private_key_path'] = $this->_prepareFile($uploadedFile['file']);
            } catch (Exception $e) {
                $data['private_key_path'] = isset($data['private_key_path']['value']) ?
                    $data['private_key_path']['value'] : '';
            }
        }

        return $this;
    }

    /**
     * @param string $uploadedFile
     * @param string $type
     *
     * @return $this
     * @throws FileSystemException
     */
    public function removeFile($uploadedFile, $type)
    {
        $file = $this->getMediaPath($uploadedFile, $type);
        if ($this->mediaDirectory->isFile($file)) {
            $this->mediaDirectory->delete($file);
        }

        return $this;
    }
}
