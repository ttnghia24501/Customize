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
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPoints\Plugin\Api;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\InvoiceExtension;
use Magento\Sales\Api\Data\InvoiceExtensionFactory;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceItemExtension;
use Magento\Sales\Api\Data\InvoiceItemExtensionFactory;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection;
use Mageplaza\RewardPoints\Api\Data\InvoiceItemInterface;
use Mageplaza\RewardPoints\Model\InvoiceFactory;
use Mageplaza\RewardPoints\Model\InvoiceItemFactory;

/**
 * Class InvoiceGet
 * @package Mageplaza\RewardPoints\Plugin\Api
 */
class InvoiceGet
{
    /**
     * @var InvoiceFactory
     */
    protected $invoiceFactory;

    /**
     * @var InvoiceItemFactory
     */
    protected $invoiceItemFactory;

    /**
     * @var InvoiceExtensionFactory
     */
    protected $invoiceExtensionFactory;

    /**
     * @var InvoiceItemExtensionFactory
     */
    protected $invoiceItemExtensionFactory;

    /**
     * InvoiceGet constructor.
     *
     * @param InvoiceItemFactory $invoiceItemFactory
     * @param InvoiceFactory $invoiceFactory
     * @param InvoiceExtensionFactory $invoiceExtensionFactory
     * @param InvoiceItemExtensionFactory $invoiceItemExtensionFactory
     */
    public function __construct(
        InvoiceItemFactory $invoiceItemFactory,
        InvoiceFactory $invoiceFactory,
        InvoiceExtensionFactory $invoiceExtensionFactory,
        InvoiceItemExtensionFactory $invoiceItemExtensionFactory
    ) {
        $this->invoiceFactory = $invoiceFactory;
        $this->invoiceItemFactory = $invoiceItemFactory;
        $this->invoiceExtensionFactory = $invoiceExtensionFactory;
        $this->invoiceItemExtensionFactory = $invoiceItemExtensionFactory;
    }

    /**
     * @param InvoiceRepositoryInterface $subject
     * @param InvoiceInterface $resultInvoice
     *
     * @return InvoiceInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        InvoiceRepositoryInterface $subject,
        InvoiceInterface $resultInvoice
    ) {
        $resultInvoice = $this->getInvoiceReward($resultInvoice);
        $resultInvoice = $this->getInvoiceItemReward($resultInvoice);

        return $resultInvoice;
    }

    /**
     * @param InvoiceInterface $invoice
     *
     * @return InvoiceInterface
     */
    protected function getInvoiceReward(InvoiceInterface $invoice)
    {
        $extensionAttributes = $invoice->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getMpReward()) {
            return $invoice;
        }

        try {
            /** @var \Mageplaza\RewardPoints\Api\Data\InvoiceInterface $rewardData */
            $rewardData = $this->invoiceFactory->create()->load($invoice->getEntityId());
        } catch (NoSuchEntityException $e) {
            return $invoice;
        }

        /** @var InvoiceExtension $invoiceExtension */
        $invoiceExtension = $extensionAttributes ? $extensionAttributes : $this->invoiceExtensionFactory->create();
        $invoiceExtension->setMpReward($rewardData);
        $invoice->setExtensionAttributes($invoiceExtension);

        return $invoice;
    }

    /**
     * @param InvoiceInterface $invoice
     *
     * @return InvoiceInterface
     */
    protected function getInvoiceItemReward(InvoiceInterface $invoice)
    {
        $invoiceItems = $invoice->getItems();
        if (null !== $invoiceItems) {
            /** @var \Magento\Sales\Api\Data\InvoiceItemInterface $invoiceItem */
            foreach ($invoiceItems as $invoiceItem) {
                $extensionAttributes = $invoiceItem->getExtensionAttributes();
                if ($extensionAttributes && $extensionAttributes->getMpReward()) {
                    continue;
                }

                try {
                    /** @var InvoiceItemInterface $rewardData */
                    $rewardData = $this->invoiceItemFactory->create()->load($invoiceItem->getItemId());
                } catch (NoSuchEntityException $e) {
                    continue;
                }

                /** @var InvoiceItemExtension $invoiceItemExtension */
                $invoiceItemExtension = $extensionAttributes ? $extensionAttributes
                    : $this->invoiceItemExtensionFactory->create();
                $invoiceItemExtension->setMpReward($rewardData);
                $invoiceItem->setExtensionAttributes($invoiceItemExtension);
            }
        }

        return $invoice;
    }

    /**
     * @param InvoiceRepositoryInterface $subject
     * @param Collection $resultInvoice
     *
     * @return Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        InvoiceRepositoryInterface $subject,
        Collection $resultInvoice
    ) {
        /** @var  $invoice */
        foreach ($resultInvoice->getItems() as $invoice) {
            $this->afterGet($subject, $invoice);
        }

        return $resultInvoice;
    }
}
