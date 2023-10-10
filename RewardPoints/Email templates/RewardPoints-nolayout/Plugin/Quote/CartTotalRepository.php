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

namespace Mageplaza\RewardPoints\Plugin\Quote;

use Closure;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\TotalsExtensionFactory;
use Magento\Quote\Api\Data\TotalsExtensionInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Model\Quote;
use Mageplaza\RewardPoints\Helper\Calculation;
use Mageplaza\RewardPoints\Helper\Data;
use Mageplaza\RewardPoints\Model\Source\DisplayPointLabel;

/**
 * Class CartTotalRepository
 * @package Mageplaza\RewardPoints\Plugin\Quote
 */
class CartTotalRepository
{
    /**
     * Quote repository.
     *
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var TotalsExtensionFactory
     */
    protected $totalExtensionFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Calculation
     */
    protected $calculation;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * CartTotalRepository constructor.
     *
     * @param CartRepositoryInterface $quoteRepository
     * @param TotalsExtensionFactory $totalExtensionFactory
     * @param RequestInterface $request
     * @param Calculation $helper
     * @param CheckoutSession $checkoutSession
     * @param Data $helperData
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        TotalsExtensionFactory $totalExtensionFactory,
        RequestInterface $request,
        Calculation $helper,
        CheckoutSession $checkoutSession,
        Data $helperData
    ) {
        $this->quoteRepository       = $quoteRepository;
        $this->totalExtensionFactory = $totalExtensionFactory;
        $this->request               = $request;
        $this->calculation           = $helper;
        $this->helperData            = $helperData;
        $this->checkoutSession       = $checkoutSession;
    }

    /**
     * @param CartTotalRepositoryInterface $subject
     * @param Closure $proceed
     * @param string $cartId
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function aroundGet(CartTotalRepositoryInterface $subject, Closure $proceed, $cartId)
    {
        /** @var TotalsInterface $quoteTotals */
        $quoteTotals = $proceed($cartId);

        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $this->calculation->setQuote($quote);
        $storeId = $quote->getStoreId();
        if (!$this->calculation->isEnabled($storeId)
            || !$this->calculation->isModuleOutputEnabled()
            || !$this->calculation->isRewardAccountActive()
        ) {
            return $quoteTotals;
        }

        $spendingConfig = [];
        if ($this->calculation->isAllowSpending($quote)) {
            $spendingConfig                   = $this->calculation->getSpendingConfiguration($quote);
            $spendingConfig['isCheckoutCart'] = $this->request->getFullActionName() === 'checkout_cart_index';
            $spendingConfig['useMaxPoints']   = (bool) $this->calculation->getConfigSpending('use_max_point', $storeId);
            $spendingConfig['earnWithSpent']  = (bool) $this->helperData->isEarnWithSpent($storeId);
        }

        $pointHelper   = $this->calculation->getPointHelper();
        $isLabelBefore = $pointHelper->getPointLabelPosition($storeId) == DisplayPointLabel::BEFORE_AMOUNT;

        $rewardConfig = [
            'pattern'         => [
                'single' => $isLabelBefore ? $pointHelper->getPointLabel($storeId) . ' {point}'
                    : '{point} ' . $pointHelper->getPointLabel($storeId),
                'plural' => $isLabelBefore ? $pointHelper->getPluralPointLabel($storeId) . ' {point}'
                    : '{point} ' . $pointHelper->getPluralPointLabel($storeId)
            ],
            'balance'         => $this->calculation->getAccountHelper()->get()->getBalance(),
            'spending'        => $spendingConfig,
            'isEarnWithSpent' => (bool) $this->helperData->isEarnWithSpent(),
        ];

        /** @var TotalsExtensionInterface $totalsExtension */
        $totalsExtension = $quoteTotals->getExtensionAttributes() ?: $this->totalExtensionFactory->create();
        $totalsExtension->setRewardPoints(Calculation::jsonEncode($rewardConfig));
        $fullActionName = $this->helperData->getFullActionName();
        $highlight      = $this->checkoutSession->getData('mp_rw_highlight');

        if ($fullActionName === 'checkout_cart_index') {
            $this->checkoutSession->setData('mp_rw_highlight', 'cart');
        } elseif ($fullActionName === 'checkout_index_index') {
            $this->checkoutSession->setData('mp_rw_highlight', 'checkout');
        }

        $totalsExtension->setHaveHighlight($this->helperData->checkHighlightEnabledByType($highlight));

        $quoteTotals->setExtensionAttributes($totalsExtension);

        return $quoteTotals;
    }
}
