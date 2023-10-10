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
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Archive\Bz;
use Magento\Framework\Archive\Gz;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\Io\File as IoFile;
use Magento\Framework\Registry;
use Mageplaza\ProductFeed\Controller\Adminhtml\AbstractManageFeeds;
use Mageplaza\ProductFeed\Helper\Data;
use Mageplaza\ProductFeed\Model\Config\Source\CompressFileType;
use Mageplaza\ProductFeed\Model\FeedFactory;
use ZipArchive;

/**
 * Class Download
 * @package Mageplaza\ProductFeed\Controller\Adminhtml\ManageFeeds
 */
class Download extends AbstractManageFeeds
{
    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var Gz
     */
    protected $gzArchive;

    /**
     * @var Bz
     */
    protected $bzArchive;

    /**
     * @var File
     */
    protected $fileDriver;

    /**
     * @var IoFile
     */
    protected $fileSystemIo;

    /**
     * Download constructor.
     *
     * @param FeedFactory $feedFactory
     * @param Registry $coreRegistry
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param File $fileDriver
     * @param IoFile $fileSystemIo
     * @param Gz $gzArchive
     * @param Bz $bzArchive
     */
    public function __construct(
        FeedFactory $feedFactory,
        Registry $coreRegistry,
        Context $context,
        FileFactory $fileFactory,
        File $fileDriver,
        IoFile $fileSystemIo,
        Gz $gzArchive,
        Bz $bzArchive
    ) {
        $this->fileFactory  = $fileFactory;
        $this->gzArchive    = $gzArchive;
        $this->bzArchive    = $bzArchive;
        $this->fileDriver   = $fileDriver;
        $this->fileSystemIo = $fileSystemIo;

        parent::__construct($feedFactory, $coreRegistry, $context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        $feed = $this->initFeed();
        if (!$feed->getId()) {
            $this->messageManager->addErrorMessage(__('Feed no longer exits'));

            return $this->resultRedirectFactory->create()->setPath($this->_redirect->getRefererUrl());
        }

        try {
            $isCompress       = true;
            $fileName         = $feed->getFileName() . '.' . $feed->getFileType();
            $compressFileName = $feed->getFileName() . '.' . $feed->getCompressFile();
            if (in_array($feed->getCompressFile(), [CompressFileType::BZ, CompressFileType::GZ], true)) {
                $compressFileName = $feed->getFileName() . '.' . $feed->getFileType() . '.' . $feed->getCompressFile();
            }
            $fileUrl          = Data::FEED_FILE_PATH . $fileName;
            $compressFileUrl  = Data::FEED_FILE_PATH . $compressFileName;

            switch ($feed->getCompressFile()) {
                case CompressFileType::ZIP:
                case CompressFileType::RAR:
                    $destination = $this->packToZip($fileUrl, $compressFileUrl);
                    break;
                case CompressFileType::GZ:
                    $destination = $this->gzArchive->pack($fileUrl, $compressFileUrl);
                    break;
                case CompressFileType::BZ:
                    $destination = $this->bzArchive->pack($fileUrl, $compressFileUrl);
                    break;
                default:
                    $destination = 'mageplaza/feed/' . $fileName;
                    $isCompress  = false;
            }

            return $this->fileFactory->create(
                $isCompress ? $compressFileName : $fileName,
                [
                    'type'  => 'filename',
                    'value' => $destination
                ],
                'media'
            );
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('Something wrong when download file: %1', $e->getMessage()));

            return $this->resultRedirectFactory->create()->setPath($this->_redirect->getRefererUrl());
        }
    }

    /**
     * @param string $source
     * @param string $destination
     *
     * @return mixed
     * @throws FileSystemException
     */
    public function packToZip($source, $destination)
    {
        $zip = new ZipArchive();
        if ($this->fileDriver->isExists($destination)) {
            $zip->open($destination, ZipArchive::OVERWRITE);
        } else {
            $zip->open($destination, ZipArchive::CREATE);
        }

        $fileInfo = $this->fileSystemIo->getPathInfo($source);
        $zip->addFile($source, $fileInfo['basename']);
        $zip->close();

        return $destination;
    }
}
