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
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\Data\CreditmemoExtension;
use Magento\Sales\Api\Data\CreditmemoExtensionFactory;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoItemExtension;
use Magento\Sales\Api\Data\CreditmemoItemExtensionFactory;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection;
use Mageplaza\RewardPoints\Api\Data\CreditmemoItemInterface;
use Mageplaza\RewardPoints\Model\CreditmemoFactory;
use Mageplaza\RewardPoints\Model\CreditmemoItemFactory;

/**
 * Class CreditmemoGet
 * @package Mageplaza\RewardPoints\Plugin\Api
 */
class CreditmemoGet
{
    /**
     * @var CreditmemoFactory
     */
    protected $creditmemoFactory;

    /**
     * @var CreditmemoItemFactory
     */
    protected $creditmemoItemFactory;

    /**
     * @var CreditmemoExtensionFactory
     */
    protected $creditmemoExtensionFactory;

    /**
     * @var CreditmemoItemExtensionFactory
     */
    protected $creditmemoItemExtensionFactory;

    /**
     * CreditmemoGet constructor.
     *
     * @param CreditmemoItemFactory $creditmemoItemFactory
     * @param CreditmemoFactory $creditmemoFactory
     * @param CreditmemoExtensionFactory $creditmemoExtensionFactory
     * @param CreditmemoItemExtensionFactory $creditmemoItemExtensionFactory
     */
    public function __construct(
        CreditmemoItemFactory $creditmemoItemFactory,
        CreditmemoFactory $creditmemoFactory,
        CreditmemoExtensionFactory $creditmemoExtensionFactory,
        CreditmemoItemExtensionFactory $creditmemoItemExtensionFactory
    ) {
        $this->creditmemoFactory = $creditmemoFactory;
        $this->creditmemoItemFactory = $creditmemoItemFactory;
        $this->creditmemoExtensionFactory = $creditmemoExtensionFactory;
        $this->creditmemoItemExtensionFactory = $creditmemoItemExtensionFactory;
    }

    /**
     * @param CreditmemoRepositoryInterface $subject
     * @param CreditmemoInterface $resultCreditmemo
     *
     * @return CreditmemoInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        CreditmemoRepositoryInterface $subject,
        CreditmemoInterface $resultCreditmemo
    ) {
        $resultCreditmemo = $this->getCreditmemoReward($resultCreditmemo);
        $resultCreditmemo = $this->getCreditmemoItemReward($resultCreditmemo);

        return $resultCreditmemo;
    }

    /**
     * @param CreditmemoInterface $creditmemo
     *
     * @return CreditmemoInterface
     */
    protected function getCreditmemoReward(CreditmemoInterface $creditmemo)
    {
        $extensionAttributes = $creditmemo->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getMpReward()) {
            return $creditmemo;
        }

        try {
            /** @var \Mageplaza\RewardPoints\Api\Data\CreditmemoInterface $rewardData */
            $rewardData = $this->creditmemoFactory->create()->load($creditmemo->getEntityId());
        } catch (NoSuchEntityException $e) {
            return $creditmemo;
        }

        /** @var CreditmemoExtension $creditmemoExtension */
        $creditmemoExtension = $extensionAttributes ? $extensionAttributes
            : $this->creditmemoExtensionFactory->create();
        $creditmemoExtension->setMpReward($rewardData);
        $creditmemo->setExtensionAttributes($creditmemoExtension);

        return $creditmemo;
    }

    /**
     * @param CreditmemoInterface $creditmemo
     *
     * @return CreditmemoInterface
     */
    protected function getCreditmemoItemReward(CreditmemoInterface $creditmemo)
    {
        $creditmemoItems = $creditmemo->getItems();
        if (null !== $creditmemoItems) {
            /** @var \Magento\Sales\Api\Data\CreditmemoItemInterface $creditmemoItem */
            foreach ($creditmemoItems as $creditmemoItem) {
                $extensionAttributes = $creditmemoItem->getExtensionAttributes();

                if ($extensionAttributes && $extensionAttributes->getMpReward()) {
                    continue;
                }

                try {
                    /** @var CreditmemoItemInterface $rewardData */
                    $rewardData = $this->creditmemoItemFactory->create()->load($creditmemoItem->getItemId());
                } catch (NoSuchEntityException $e) {
                    continue;
                }

                /** @var CreditmemoItemExtension $creditmemoItemExtension */
                $invoiceItemExtension = $extensionAttributes ? $extensionAttributes
                    : $this->creditmemoItemExtensionFactory->create();
                $invoiceItemExtension->setMpReward($rewardData);
                $creditmemoItem->setExtensionAttributes($invoiceItemExtension);
            }
        }

        return $creditmemo;
    }

    /**
     * @param CreditmemoRepositoryInterface $subject
     * @param Collection $resultCreditmemo
     *
     * @return Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        CreditmemoRepositoryInterface $subject,
        Collection $resultCreditmemo
    ) {
        /** @var  $creditmemo */
        foreach ($resultCreditmemo->getItems() as $creditmemo) {
            $this->afterGet($subject, $creditmemo);
        }

        return $resultCreditmemo;
    }
}
