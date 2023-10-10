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
 * @package     Mageplaza_OrderExport
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\OrderExport\Mail\Template;

use Exception;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\AddressConverter;
use Magento\Framework\Mail\EmailMessageInterfaceFactory;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\MimeInterface;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilder as DefaultBuilder;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Session\SessionManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Laminas\Mime\Part;
use Laminas\Mime\Mime;

/**
 * Class TransportBuilder
 * @package Mageplaza\OrderExport\Mail\Template
 */
class TransportBuilder extends DefaultBuilder
{
    const ATTACHMENT_NAME = 'orderexport.xml';

    /**
     * @var array
     */
    private $messageData = [];

    /**
     * @var State
     */
    protected $state;

    /**
     * @var AbstractData
     */
    private $_helperData;

    /**
     * TransportBuilder constructor.
     *
     * @param FactoryInterface $templateFactory
     * @param MessageInterface $message
     * @param SenderResolverInterface $senderResolver
     * @param ObjectManagerInterface $objectManager
     * @param TransportInterfaceFactory $mailTransportFactory
     * @param AbstractData $helper
     * @param State $state
     *
     * @throws LocalizedException
     */
    public function __construct(
        FactoryInterface $templateFactory,
        MessageInterface $message,
        SenderResolverInterface $senderResolver,
        ObjectManagerInterface $objectManager,
        TransportInterfaceFactory $mailTransportFactory,
        AbstractData $helper,
        State $state
    ) {
        $this->_helperData = $helper;
        $this->state       = $state;
        try {
            $this->state->getAreaCode();
        } catch (Exception $e) {
            $this->state->setAreaCode('adminhtml');
        }

        parent::__construct($templateFactory, $message, $senderResolver, $objectManager, $mailTransportFactory);
    }

    /**
     * @param        $attachFile
     * @param string $filename
     * @param string $mimeType
     * @param string $disposition
     * @param string $encoding
     *
     * @return TransportBuilder|Part
     */
    public function addAttachment(
        $attachFile,
        $filename = self::ATTACHMENT_NAME,
        $mimeType = Mime::TYPE_TEXT,
        $disposition = Mime::DISPOSITION_ATTACHMENT,
        $encoding = Mime::ENCODING_BASE64
    ) {
        if ($this->_helperData->versionCompare('2.2.9')) {
            $attachment              = new Part($attachFile);
            $attachment->type        = $mimeType;
            $attachment->encoding    = $encoding;
            $attachment->disposition = $disposition;
            $attachment->filename    = $filename;

            return $attachment;
        }

        $this->message->createAttachment($attachFile, $mimeType, $disposition, $encoding, $filename);

        return $this;
    }

    /**
     * Add to address
     *
     * @param array|string $address
     * @param string $name
     *
     * @return $this
     */
    public function addTo($address, $name = '')
    {
        if ($this->_helperData->versionCompare('2.3.3')) {
            $this->addAddressByType('to', $address, $name);
        } else {
            $this->message->addTo($address, $name);
        }

        return $this;
    }

    /**
     * Add cc address
     *
     * @param array|string $address
     * @param string $name
     *
     * @return $this
     */
    public function addCc($address, $name = '')
    {
        if ($this->_helperData->versionCompare('2.3.3')) {
            $this->addAddressByType('cc', $address, $name);
        } else {
            $this->message->addCc($address, $name);
        }

        return $this;
    }

    /**
     * Add bcc address
     *
     * @param array|string $address
     *
     * @return $this
     */
    public function addBcc($address)
    {
        if ($this->_helperData->versionCompare('2.3.3')) {
            $this->addAddressByType('bcc', $address);
        } else {
            $this->message->addBcc($address);
        }

        return $this;
    }

    /**
     * Handles possible incoming types of email (string or array)
     *
     * @param string $addressType
     * @param array|string $email
     * @param null $name
     */
    private function addAddressByType($addressType, $email, $name = null)
    {
        $addressConverter = $this->objectManager->create(AddressConverter::class);
        if (is_string($email)) {
            $this->messageData[$addressType][] = $addressConverter->convert($email, $name);

            return;
        }
        $convertedAddressArray = $addressConverter->convertMany($email);
        if (isset($this->messageData[$addressType])) {
            $this->messageData[$addressType] = array_merge(
                $this->messageData[$addressType],
                $convertedAddressArray
            );
        }
    }

    /**
     * Set mail from address
     *
     * @param array|string $from
     *
     * @return DefaultBuilder|TransportBuilder
     * @throws MailException
     */
    public function setFrom($from)
    {
        return $this->setFromByScope($from);
    }

    /**
     * Set mail from address by scopeId
     *
     * @param array|string $from
     * @param null $scopeId
     *
     * @return $this|DefaultBuilder
     * @throws MailException
     */
    public function setFromByScope($from, $scopeId = null)
    {
        $result = $this->_senderResolver->resolve($from, $scopeId);
        if ($this->_helperData->versionCompare('2.3.3')) {
            $this->addAddressByType('from', $result['email'], $result['name']);
        } else {
            $this->message->setFromAddress($result['email'], $result['name']);
        }

        return $this;
    }

    /**
     * Reset object state
     *
     * @return $this
     */
    protected function reset()
    {
        $this->messageData        = [];
        $this->templateIdentifier = null;
        $this->templateVars       = null;
        $this->templateOptions    = null;

        return $this;
    }

    /**
     * @return $this|DefaultBuilder
     * @throws LocalizedException
     */
    public function prepareMessage()
    {
        $objectManager = ObjectManager::getInstance();
        $coreSession   = $objectManager->get(SessionManagerInterface::class);
        $coreSession->start();
        $attachFile = $coreSession->getMpOrderExportAttach();
        $coreSession->unsMpOrderExportAttach();
        $template                     = $this->getTemplate();
        $content                      = $template->processTemplate();
        $mimePartInterfaceFactory     = $objectManager->get(MimePartInterfaceFactory::class);
        $mimeMessageInterfaceFactory  = $objectManager->get(MimeMessageInterfaceFactory::class);
        $emailMessageInterfaceFactory = $objectManager->get(EmailMessageInterfaceFactory::class);

        switch ($template->getType()) {
            case TemplateTypesInterface::TYPE_TEXT:
                $part['type'] = MimeInterface::TYPE_TEXT;
                break;

            case TemplateTypesInterface::TYPE_HTML:
                $part['type'] = MimeInterface::TYPE_HTML;
                break;

            default:
                throw new LocalizedException(
                    new Phrase('Unknown template type')
                );
        }
        $mimePart = $mimePartInterfaceFactory->create(['content' => $content]);
        if ($this->_helperData->versionCompare("2.3.3") && is_object($attachFile)) {
            $this->messageData['body'] = $mimeMessageInterfaceFactory->create(
                ['parts' => [$mimePart, $attachFile]]
            );
        } else {
            $this->messageData['body'] = $mimeMessageInterfaceFactory->create(
                ['parts' => [$mimePart]]
            );
        }

        $this->messageData['subject'] = html_entity_decode(
            (string) $template->getSubject(),
            ENT_QUOTES
        );
        $this->message                = $emailMessageInterfaceFactory->create($this->messageData);

        return $this;
    }
}
