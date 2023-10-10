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
 * @package     Mageplaza_CustomForm
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\CustomForm\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Mail\EmailMessage;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Model\Store;
use Mageplaza\CustomForm\Helper\Data;
use Laminas\Mail\Message;
use Laminas\Mime\Mime;
use Laminas\Mime\Part;

/**
 * Class MailEvent
 * @package Mageplaza\CustomForm\Model
 */
class MailEvent
{
    /**
     * @var array
     */
    const MIME_TYPES = [
        'txt'  => 'text/plain',
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'docx' => 'application/msword',
    ];

    /**
     * @var array
     */
    private $parts = [];

    /**
     * @var Mail
     */
    private $mail;

    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var SessionManagerInterface
     */
    private $coreSession;

    /**
     * @var File
     */
    private $driverFile;

    /**
     * MailEvent constructor.
     *
     * @param Mail $mail
     * @param Data $dataHelper
     * @param Filesystem $filesystem
     * @param ObjectManagerInterface $objectManager
     * @param SessionManagerInterface $coreSession
     * @param File $driverFile
     */
    public function __construct(
        Mail $mail,
        Data $dataHelper,
        Filesystem $filesystem,
        ObjectManagerInterface $objectManager,
        SessionManagerInterface $coreSession,
        File $driverFile
    ) {
        $this->mail          = $mail;
        $this->dataHelper    = $dataHelper;
        $this->filesystem    = $filesystem;
        $this->objectManager = $objectManager;
        $this->coreSession   = $coreSession;
        $this->driverFile    = $driverFile;
    }

    /**
     * @param EmailMessage $message
     *
     * @throws FileSystemException
     */
    public function dispatch($message)
    {
        $templateVars = $this->mail->getTemplateVars();
        if (!$templateVars) {
            return;
        }
        /** @var Store|null $store */
        $store   = isset($templateVars['store']) ? $templateVars['store'] : null;
        $storeId = $store ? $store->getId() : null;

        if (!$this->dataHelper->isEnabled($storeId)) {
            return;
        }

        if (!empty($templateVars['attachedFiles'])) {
            foreach ($templateVars['attachedFiles'] as $file) {
                $this->setAttachedFile($message, $file);
            }
        }

        if ($this->dataHelper->versionCompare('2.2.9')) {
            $this->setBodyAttachment($message);
            $this->parts = [];
        }

        $this->mail->setTemplateVars([]);
    }

    /**
     * @param EmailMessage $message
     * @param array $file
     *
     * @throws FileSystemException
     */
    private function setAttachedFile($message, $file)
    {
        list($content, $mimeType) = $this->getInformationFile($file);

        if ($this->dataHelper->versionCompare('2.2.9')) {
            $attachment              = new Part($content);
            $attachment->type        = $mimeType;
            $attachment->encoding    = Mime::ENCODING_BASE64;
            $attachment->disposition = Mime::DISPOSITION_ATTACHMENT;
            $attachment->filename    = $file['name'];

            $this->parts[] = $attachment;

            return;
        }

        $message->createAttachment(
            $content,
            $mimeType,
            Mime::DISPOSITION_ATTACHMENT,
            Mime::ENCODING_BASE64,
            $file['name']
        );
    }

    /**
     * @param array $file
     *
     * @return array
     * @throws FileSystemException
     */
    private function getInformationFile($file)
    {
        $filePath = $file['value'];
        $content  = $this->driverFile->fileGetContents($filePath);
        $ext      = (string) substr($filePath, strrpos($filePath, '.') + 1);
        $mine     = isset(self::MIME_TYPES[$ext]) ? self::MIME_TYPES[$ext] : 'text/plain';

        return [$content, $mine];
    }

    /**
     * @param EmailMessage $message
     */
    private function setBodyAttachment($message)
    {
        $body = Message::fromString($message->getRawMessage())->getBody();
        if ($this->dataHelper->versionCompare('2.3.3')) {
            $body = quoted_printable_decode($body);
        }

        $part = new Part($body);
        $part->setCharset('utf-8');
        $part->setEncoding(Mime::ENCODING_BASE64);
        if ($this->dataHelper->versionCompare('2.3.3')) {
            $part->setEncoding(Mime::ENCODING_QUOTEDPRINTABLE);
            $part->setDisposition(Mime::DISPOSITION_INLINE);
        }
        $part->setType(Mime::TYPE_HTML);
        array_unshift($this->parts, $part);

        $bodyPart = new \Laminas\Mime\Message();
        $bodyPart->setParts($this->parts);
        $message->setBody($bodyPart);
    }
}
