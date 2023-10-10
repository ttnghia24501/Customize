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

namespace Mageplaza\OrderExport\Model;

use DateTime;
use Exception;
use Magento\Backend\Model\UrlInterface;
use Magento\CatalogRule\Model\Rule\Condition\Combine;
use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Framework\Registry;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rule\Model\AbstractModel;
use Magento\Rule\Model\Action\Collection;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order\InvoiceFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory as CreditmemoCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory as InvoiceCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Status\Collection as OrderStatusCollection;
use Mageplaza\OrderExport\Helper\Data;
use Mageplaza\OrderExport\Mail\Template\TransportBuilder;
use Mageplaza\OrderExport\Model\Config\Source\Events;
use Mageplaza\OrderExport\Model\ResourceModel\Profile as ProfileResourceModel;
use Mageplaza\OrderExport\Model\Rule\Condition\Combine as OrderExportCombine;
use Mageplaza\OrderExport\Model\Rule\Condition\CombineFactory as OrderExportCombineFactory;
use Laminas\Mail\Message;
use Laminas\Mime\Part;

/**
 * Class Profile
 * @package Mageplaza\OrderExport\Model
 * @method getCustomerGroups()
 * @method getStatusCondition()
 * @method getStoreIds()
 * @method getCreatedFrom()
 * @method getCreatedTo()
 * @method getOrderIdFrom()
 * @method getOrderIdTo()
 * @method getItemIdFrom()
 * @method getItemIdTo()
 * @method getExportDuplicate()
 * @method getExportedIds()
 * @method getProfileType()
 */
class Profile extends AbstractModel
{
    const TYPE_ORDER        = 'order';
    const TYPE_INVOICE      = 'invoice';
    const TYPE_SHIPMENT     = 'shipment';
    const TYPE_CREDITMEMO   = 'creditmemo';
    const EMAIL_TEMPLATE_ID = 'mp_order_export_alert_email_template';
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'mageplaza_orderexport_profile';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'mageplaza_orderexport_profile';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_orderexport_profile';

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var InvoiceCollectionFactory
     */
    protected $invoiceCollectionFactory;

    /**
     * @var ShipmentCollectionFactory
     */
    protected $shipmentCollectionFactory;

    /**
     * @var CreditmemoCollectionFactory
     */
    protected $creditmemoCollectionFactory;

    /**
     * @var array
     */
    protected $productCollection = [];

    /**
     * @var
     */
    protected $itemIds;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var UrlInterface
     */
    protected $backendUrl;

    /**
     * @var SessionManagerInterface
     */
    protected $sessionManager;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Iterator
     */
    protected $resourceIterator;

    /**
     * @var OrderExportCombineFactory
     */
    protected $orderExportCombineFactory;

    /**
     * @var InvoiceFactory
     */
    protected $invoiceFactory;

    /**
     * @var CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * @var ShipmentRepositoryInterface
     */
    protected $shipmentRepository;

    /**
     * @var OrderStatusCollection
     */
    protected $orderStatusCollection;

    /**
     * Profile constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TimezoneInterface $localeDate
     * @param OrderFactory $orderFactory
     * @param OrderStatusCollection $orderStatusCollection
     * @param InvoiceCollectionFactory $invoiceCollectionFactory
     * @param ShipmentCollectionFactory $shipmentCollectionFactory
     * @param CreditmemoCollectionFactory $creditmemoCollectionFactory
     * @param TransportBuilder $transportBuilder
     * @param Data $helper
     * @param UrlInterface $backendUrl
     * @param SessionManagerInterface $sessionManager
     * @param RequestInterface $request
     * @param Iterator $resourceIterator
     * @param OrderExportCombineFactory $orderExportCombineFactory
     * @param InvoiceFactory $invoiceFactory
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        OrderFactory $orderFactory,
        OrderStatusCollection $orderStatusCollection,
        InvoiceCollectionFactory $invoiceCollectionFactory,
        ShipmentCollectionFactory $shipmentCollectionFactory,
        CreditmemoCollectionFactory $creditmemoCollectionFactory,
        TransportBuilder $transportBuilder,
        Data $helper,
        UrlInterface $backendUrl,
        SessionManagerInterface $sessionManager,
        RequestInterface $request,
        Iterator $resourceIterator,
        OrderExportCombineFactory $orderExportCombineFactory,
        InvoiceFactory $invoiceFactory,
        ShipmentRepositoryInterface $shipmentRepository,
        CreditmemoRepositoryInterface $creditmemoRepository,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->orderFactory                = $orderFactory;
        $this->invoiceCollectionFactory    = $invoiceCollectionFactory;
        $this->shipmentCollectionFactory   = $shipmentCollectionFactory;
        $this->creditmemoCollectionFactory = $creditmemoCollectionFactory;
        $this->transportBuilder            = $transportBuilder;
        $this->helper                      = $helper;
        $this->backendUrl                  = $backendUrl;
        $this->sessionManager              = $sessionManager;
        $this->request                     = $request;
        $this->resourceIterator            = $resourceIterator;
        $this->orderExportCombineFactory   = $orderExportCombineFactory;
        $this->invoiceFactory              = $invoiceFactory;
        $this->creditmemoRepository        = $creditmemoRepository;
        $this->shipmentRepository          = $shipmentRepository;
        $this->orderStatusCollection       = $orderStatusCollection;

        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ProfileResourceModel::class);
    }

    /**
     * @return OrderExportCombine
     */
    public function getConditionsInstance()
    {
        return $this->orderExportCombineFactory->create();
    }

    /**
     * @return Combine|Collection
     */
    public function getActionsInstance()
    {
        return ObjectManager::getInstance()->create(Rule\Condition\Product\Combine::class);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getMatchingItemIds()
    {
        if (!$this->itemIds) {
            $status          = $this->getStatusCondition();
            $customerGroups  = $this->getCustomerGroups();
            $storeIds        = $this->getStoreIds();
            $createFrom      = $this->getCreatedFrom();
            $createTo        = $this->getCreatedTo();
            $orderIdFrom     = $this->getOrderIdFrom();
            $orderIdTo       = $this->getOrderIdTo();
            $itemIdFrom      = $this->getItemIdFrom();
            $itemIdTo        = $this->getItemIdTo();
            $exportDuplicate = $this->getExportDuplicate();
            $exportedIds     = $this->getExportedIds();
            $profileType     = $this->getProfileType();
            $exportLimit     = $this->getExportLimit() == "mp-use-config"
                ? $this->helper->getExportLimit()
                : $this->getExportLimit();

            $customerGroupsIds = $this->orderFactory->create()->getCollection()
                ->addFieldToFilter('customer_group_id', ['in' => explode(',', $customerGroups)])->getAllIds();

            switch ($profileType) {
                case self::TYPE_INVOICE:
                    $object     = $this->invoiceFactory->create();
                    $collection = $this->invoiceCollectionFactory->create()->addFieldToSelect('*');
                    if ($status) {
                        $collection->addFieldToFilter('state', ['in' => explode(',', $status)]);
                    }
                    break;
                case self::TYPE_SHIPMENT:
                    $object     = $this->shipmentRepository;
                    $collection = $this->shipmentCollectionFactory->create()->addFieldToSelect('*');
                    if ($status) {
                        $collection->addFieldToFilter('shipment_status', ['in' => explode(',', $status)]);
                    }
                    break;
                case self::TYPE_CREDITMEMO:
                    $object     = $this->creditmemoRepository;
                    $collection = $this->creditmemoCollectionFactory->create()->addFieldToSelect('*');
                    if ($status) {
                        $collection->addFieldToFilter('state', ['in' => explode(',', $status)]);
                    }
                    break;
                default:
                    $object     = $this->orderFactory->create();
                    $collection = $this->orderFactory->create()->getCollection();
                    if ($status) {
                        $collection->addFieldToFilter('status', ['in' => explode(',', $status)]);
                    }
            }
            $storeIds = explode(',', $storeIds);
            if (!in_array('0', $storeIds, true)) {
                $collection->addFieldToFilter('store_id', ['in' => $storeIds]);
            }
            if ($customerGroups >= 0) {
                if ($profileType === self::TYPE_ORDER) {
                    $collection->addFieldToFilter('customer_group_id', ['in' => explode(',', $customerGroups)]);
                } else {
                    $collection->addFieldToFilter('order_id', ['in' => $customerGroupsIds]);
                }
            }

            if ($createFrom) {
                $createFrom = (new DateTime($createFrom))->setTime(0, 0, 0);
                $collection->addFieldToFilter('created_at', ['from' => $createFrom]);
            }
            if ($createTo) {
                $createTo = (new DateTime($createTo))->setTime(23, 59, 59);
                $collection->addFieldToFilter('created_at', ['to' => $createTo]);
            }
            if (!$exportDuplicate && $exportedIds) {
                $collection->addFieldToFilter('entity_id', ['nin' => explode(',', $exportedIds)]);
            }
            if ($profileType !== self::TYPE_ORDER) {
                if ($orderIdFrom) {
                    $collection->addFieldToFilter('order_id', ['gteq' => $orderIdFrom]);
                }
                if ($orderIdTo) {
                    $collection->addFieldToFilter('order_id', ['lteq' => $orderIdTo]);
                }
                if ($itemIdFrom) {
                    $collection->addFieldToFilter('entity_id', ['gteq' => $itemIdFrom]);
                }
                if ($itemIdTo) {
                    $collection->addFieldToFilter('entity_id', ['lteq' => $itemIdTo]);
                }
            } else {
                if ($orderIdFrom) {
                    $collection->addFieldToFilter('entity_id', ['gteq' => $orderIdFrom]);
                }
                if ($orderIdTo) {
                    $collection->addFieldToFilter('entity_id', ['lteq' => $orderIdTo]);
                }
            }

            $this->itemIds = [];
            $step          = $this->request->getParam('step');
            if ($step && $step !== 'render') {
                $this->setConditionsSerialized($this->helper->serialize($this->getConditions()->asArray()));
            }
            $this->resourceIterator->walk(
                $exportLimit ? $collection->getSelect()->limit($exportLimit) : $collection->getSelect(),
                [[$this, 'callbackValidateOrderConditions']],
                [
                    'object' => $object
                ]
            );
        }

        return $this->itemIds;
    }

    /**
     * Callback function for object matching (conditions)
     *
     * @param array $args
     *
     * @return void
     */
    public function callbackValidateOrderConditions($args)
    {
        $object      = clone $args['object'];
        $profileType = $this->getProfileType();

        switch ($profileType) {
            case self::TYPE_SHIPMENT:
            case self::TYPE_CREDITMEMO:
                $object = $object->get($args['row']['entity_id']);
                break;
            default:
                $object->setData($args['row']);
                break;
        }

        $object->setData('profile_type', $profileType);

        if ($this->getConditions()->validate($object)) {
            $this->itemIds[] = $object->getId();
        }
    }

    /**
     * @param int $generateStt
     * @param int $deliveryStt
     *
     * @throws LocalizedException
     */
    public function sendAlertMail(
        $generateStt = Events::GENERATE_DISABLE,
        $deliveryStt = Events::DELIVERY_DISABLE
    ) {
        if (!$this->helper->getEmailConfig('enabled')) {
            return;
        }

        $generateMes = '';
        $deliveryMes = '';

        switch ($generateStt) {
            case Events::GENERATE_SUCCESS:
                $genMes   = __('Profile %1 is generated successfully', $this->getName());
                $genStyle = 'color: green';
                break;
            case Events::GENERATE_ERROR:
                $genMes   = __('Profile %1 fails to be generated', $this->getName());
                $genStyle = 'color: red';
                break;
            default:
                $genMes   = '';
                $genStyle = '';
                break;
        }

        switch ($deliveryStt) {
            case Events::DELIVERY_SUCCESS:
                $deMes   = __('Profile %1 is delivery successfully', $this->getName());
                $deStyle = 'color: green';
                break;
            case Events::DELIVERY_ERROR:
                $deMes   = __('Profile %1 fails to be delivered', $this->getName());
                $deStyle = 'color: red';
                break;
            default:
                $deMes   = '';
                $deStyle = '';
                break;
        }

        $events  = explode(',', $this->helper->getEmailConfig('events'));
        $storeId = 0;

        if (in_array($generateStt, $events, false)) {
            $generateMes = '<p style="' . $genStyle . '">' . $genMes . '</p>';
        }

        if (in_array($deliveryStt, $events, false)) {
            $deliveryMes = '<p style="' . $deStyle . '">' . $deMes . '</p>';
        }

        if (in_array($generateStt, $events, false) || in_array($deliveryStt, $events, false)) {
            if ($this->helper->getEmailConfig('send_to')) {
                $sendTo = explode(',', $this->helper->getEmailConfig('send_to'));
                $this->sendMail(
                    'general',
                    $sendTo,
                    $generateMes . $deliveryMes,
                    self::EMAIL_TEMPLATE_ID,
                    $storeId
                );
            }
        }
    }

    /**
     * @param $sendFrom
     * @param $sendTo
     * @param $mes
     * @param $emailTemplate
     * @param $storeId
     *
     * @return bool
     * @throws LocalizedException
     */
    public function sendMail($sendFrom, $sendTo, $mes, $emailTemplate, $storeId)
    {
        try {
            $this->transportBuilder
                ->setTemplateIdentifier($emailTemplate)
                ->setTemplateOptions([
                    'area'  => Area::AREA_FRONTEND,
                    'store' => $storeId,
                ])
                ->setTemplateVars([
                    'viewLogUrl' => $this->backendUrl->getUrl('mporderexport/logs/'),
                    'mes'        => $mes
                ])
                ->setFrom($sendFrom);
            foreach ($sendTo as $to) {
                $this->transportBuilder->addTo($to);
            }
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();

            return true;
        } catch (MailException $e) {
            $this->_logger->critical($e->getLogMessage());
        }

        return false;
    }

    /**
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function sendExportedFileViaMail()
    {
        $storeId       = 0;
        $emailTemplate = $this->getEmailTemplate();
        $emailSubject  = $this->getEmailSubject();
        $sendFrom      = $this->getSender();
        $sendTo        = explode(',', $this->getSendEmailTo());
        $fileName      = $this->getlastGeneratedFile();
        $attachment    = $this->helper->readAttachment($fileName);
        $mes           = __('File exported by profile %1. You can download it in the attachment.', $this->getName());

        $store            = $this->helper->getStoreById($storeId);
        $transportBuilder = $this->transportBuilder
            ->setTemplateIdentifier($emailTemplate)
            ->setTemplateOptions([
                'area'  => Area::AREA_FRONTEND,
                'store' => $storeId,
            ])
            ->setTemplateVars([
                'viewLogUrl'   => $this->backendUrl->getUrl('mporderexport/logs/'),
                'mes'          => $mes,
                'emailSubject' => $emailSubject ?: __('%1 send you exported file', $store->getName()),
            ])
            ->setFrom($sendFrom);
        foreach ($sendTo as $to) {
            $this->transportBuilder->addTo($to);
        }
        if ($attachment) {
            $attachFile = $this->transportBuilder->addAttachment($attachment, $fileName);
            if ($this->helper->versionCompare('2.3.3')) {
                $this->sessionManager->start();
                $this->sessionManager->setMpOrderExportAttach($attachFile);
                $transport = $this->transportBuilder->getTransport();
            } else {
                $transport            = $transportBuilder->getTransport();
                $html                 = $transport->getMessage();
                $message              = Message::fromString($html->getRawMessage());
                $bodyMessage          = new Part($message->getBody());
                $bodyMessage->type    = 'text/html';
                $bodyMessage->charset = 'utf-8';
                $bodyPart             = new \Laminas\Mail\Message();

                $bodyPart->setParts([$bodyMessage, $attachFile]);
                $transport->getMessage()->setBody($bodyPart);
            }
        } else {
            $transport = $transportBuilder->getTransport();
        }
        $transport->sendMessage();
    }

    /**
     * @param array $ids
     *
     * @return $this
     */
    public function setMatchingIds($ids)
    {
        $this->itemIds = $ids;

        return $this;
    }
}
