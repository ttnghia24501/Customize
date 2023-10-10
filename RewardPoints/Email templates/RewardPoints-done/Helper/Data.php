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
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPoints\Helper;

use DOMDocument;
use DOMXpath;
use DateTime;
use DateTimeInterface;
use Exception;
use IntlDateFormatter;
use Magento\Backend\Model\Session\Quote;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Backend\Customer;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Model\Quote as ModelQuote;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Item;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\RewardPoints\Model\Transaction;
use Mageplaza\RewardPointsUltimate\Model\Account as AccountReward;

/**
 * Class Data
 * @package Mageplaza\RewardPoints\Helper\Data
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH     = 'rewardpoints';
    const EARNING_CONFIGURATION  = '/earning';
    const SPENDING_CONFIGURATION = '/spending';
    const DISPLAY_CONFIGURATION  = '/display';
    const EMAIL_CONFIGURATION    = '/email';
    /**
     * Transaction Action Code
     */
    const ACTION_ADMIN              = 'admin';
    const ACTION_EARNING_ORDER      = 'earning_order';
    const ACTION_EARNING_REFUND     = 'earning_refund';
    const ACTION_SPENDING_ORDER     = 'spending_order';
    const ACTION_SPENDING_REFUND    = 'spending_refund';
    const ACTION_IMPORT_TRANSACTION = 'import_transaction';
    /**
     * Transaction Action Type
     */
    const ACTION_TYPE_EARNING  = 1;
    const ACTION_TYPE_SPENDING = 2;
    const ACTION_TYPE_ADMIN    = 4;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var ModelQuote
     */
    protected $quote;

    /**
     * @var bool
     */
    protected $isActionImport = false;

    /**
     * @var SessionFactory
     */
    protected $sessionFactory;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param TimezoneInterface $timeZone
     * @param SessionFactory $sessionFactory
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        TimezoneInterface $timeZone,
        SessionFactory $sessionFactory
    ) {
        $this->priceCurrency  = $priceCurrency;
        $this->_localeDate    = $timeZone;
        $this->sessionFactory = $sessionFactory;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getAccountNavigationLabel($storeId = null)
    {
        return $this->getConfigGeneral('account_navigation_label', $storeId);
    }

    /**
     * get max point per customer
     *
     * @param null $storeId
     *
     * @return int
     */
    public function getMaxPointPerCustomer($storeId = null)
    {
        return (int) $this->getConfigGeneral('maximum_point', $storeId);
    }

    /**
     * ======================================= Earning Configuration ===================================================
     * Get config earning
     *
     * @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getConfigEarning($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(static::CONFIG_MODULE_PATH . self::EARNING_CONFIGURATION . $code, $storeId);
    }

    /**
     * Get point expired
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getSalesPointExpiredAfter($storeId = null)
    {
        return $this->getConfigEarning('sales_earn/point_expired', $storeId);
    }

    /**
     * Is earn point form tax
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function isEarnPointFromTax($storeId = null)
    {
        return (bool) $this->getConfigEarning('earn_from', $storeId);
    }

    /**
     * Is earn point form shipping
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function isEarnPointFromShipping($storeId = null)
    {
        return (bool) $this->getConfigEarning('earn_shipping', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function isRefundPointsEarn($storeId = null)
    {
        return $this->getConfigEarning('point_refund', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function isEarnPointAfterInvoiceCreated($storeId = null)
    {
        return $this->getConfigEarning('sales_earn/earn_point_after_invoice_created', $storeId);
    }

    /**
     * ======================================= Spending Configuration ==================================================
     *
     * @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getConfigSpending($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(static::CONFIG_MODULE_PATH . self::SPENDING_CONFIGURATION . $code, $storeId);
    }

    /**
     * Is spending on shipping fee
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function isSpendingOnShippingFee($storeId = null)
    {
        return $this->getConfigSpending('spend_on_ship', $storeId);
    }

    /**
     * Get discount label
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getDiscountLabel($storeId = null)
    {
        $label = $this->getConfigSpending('discount_label', $storeId);

        return $this->objectManager->create(Phrase::class, ['text' => $label]);
    }

    /**
     * Is pending from tax
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function isSpendingFromTax($storeId = null)
    {
        return $this->getConfigSpending('spend_on_tax', $storeId);
    }

    /**
     * Get type maximum spending points per order
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getTypeMaximumSpendingPoints($storeId = null)
    {
        return $this->getConfigSpending('maximum_point_type', $storeId);
    }

    /**
     * Get maximum spending points per order
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getMaximumSpendingPointsPerOrder($storeId = null)
    {
        return $this->getConfigSpending('maximum_point_per_order', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function isRestorePointAfterRefund($storeId = null)
    {
        return $this->getConfigSpending('restore_point_after_refund', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function isDisabledSpending($storeId = null)
    {
        return !($this->isEnabled($storeId) && $this->getAccountHelper()->isCustomerLoggedIn()) &&
            $this->isRewardAccountActive();
    }

    /**
     * ======================================= Display Configuration ===================================================
     *
     * @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getConfigDisplay($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue(static::CONFIG_MODULE_PATH . self::DISPLAY_CONFIGURATION . $code, $storeId);
    }

    /**
     * Is display point on top link
     *
     * @param null $storeId
     *
     * @return bool
     */
    public function isDisplayPointOnTopLink($storeId = null)
    {
        if ($this->isEnabled($storeId) && $this->getAccountHelper()->isCustomerLoggedIn()
            && $this->getConfigDisplay('top_page', $storeId)) {
            if ($this->getConfigDisplay('hide_top_link')) {
                $account        = $this->getAccountHelper()->get();
                $accountBalance = $account->getBalance();
                if ($accountBalance > 0) {
                    return true;
                }

                return false;
            } else {
                return true;
            }
        }

        return false;
    }

    /**
     * Is disable point on mini cart
     *
     * @param null $storeId
     *
     * @return bool
     */
    public function isDisablePointOnMiniCart($storeId = null)
    {
        return !($this->isEnabled() && $this->getConfigDisplay('mini_cart', $storeId)
            && $this->isRewardAccountActive());
    }

    /**
     * Get expiration date formatted
     *
     * @param null $days
     * @param null $storeId
     *
     * @return false|string
     */
    public function getExpirationDate($days = null, $storeId = null)
    {
        if (!$days) {
            $days = $this->getSalesPointExpiredAfter($storeId);
        }

        return date('Y-m-d H:i:s', strtotime("+{$days}days"));
    }

    /**
     * Round Price
     *
     * @param float $price
     *
     * @return float
     */
    public function round($price)
    {
        return $this->priceCurrency->round($price);
    }

    /**
     * Convert price
     *
     * @param float $value
     * @param bool $format
     * @param bool $includeContainer
     * @param null $scope
     *
     * @return float|string
     */
    public function convertPrice($value, $format = true, $includeContainer = true, $scope = null)
    {
        return $format
            ? $this->priceCurrency->convertAndFormat(
                $value,
                $includeContainer,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                $scope
            )
            : $this->priceCurrency->convert($value, $scope);
    }

    /**
     * Retrieve formatting date
     *
     * @param null|string|DateTime $date
     * @param int $format
     * @param bool $showTime
     * @param null|string $timezone
     *
     * @return string
     * @throws Exception
     */
    public function formatDate(
        $date = null,
        $format = IntlDateFormatter::SHORT,
        $showTime = false,
        $timezone = null
    ) {
        $date = $date instanceof DateTimeInterface ? $date : new DateTime($date);

        return $this->_localeDate->formatDateTime(
            $date,
            $format,
            $showTime ? $format : IntlDateFormatter::NONE,
            null,
            $timezone
        );
    }

    /*************************************** Transaction **************************************************************
     * @return Transaction
     */
    public function getTransaction()
    {
        return $this->objectManager->create(Transaction::class);
    }

    /*************************************** Account ******************************************************************
     * @return Account
     */
    public function getAccountHelper()
    {
        return $this->objectManager->get(Account::class);
    }

    /**
     * Check Reward Account status
     *
     * @return bool
     */
    public function isRewardAccountActive()
    {
        if ($this->getAccountHelper()->isCustomerLoggedIn()) {
            $customerId = $this->getCustomerId();
            if (!$customerId) {
                return false;
            }
            $account = $this->getAccountHelper()->getByCustomerId($customerId);

            return $account->getIsActive() ? true : false;
        }

        return true;
    }

    /**
     * Get the Customer Id from the Session instead from the cached Session object
     *
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->objectManager->create(\Magento\Customer\Model\Session::class)->getCustomer()->getId();
    }

    /*************************************** Email ********************************************************************
     * @return Email
     */
    public function getEmailHelper()
    {
        return $this->objectManager->get(Email::class);
    }

    /**************************************** Point *******************************************************************
     * @return Point
     */
    public function getPointHelper()
    {
        return $this->objectManager->get(Point::class);
    }

    /**************************************** Calculation *************************************************************
     * @return Calculation
     */
    public function getCalculationHelper()
    {
        return $this->objectManager->get(Calculation::class);
    }

    /**
     * Get active quote
     *
     * @return ModelQuote
     */
    public function getQuote()
    {
        if ($this->quote === null) {
            if ($this->isAdmin()) {
                $this->quote = $this->objectManager->get(Quote::class)->getQuote();
            } else {
                $this->quote = $this->objectManager->get(Session::class)->getQuote();
            }
        }

        return $this->quote;
    }

    /**
     * @param ModelQuote $quote
     *
     * @return mixed
     */
    public function setQuote($quote)
    {
        return $this->quote = $quote;
    }

    /**
     * @param string $action
     * @param Customer|string $customer
     * @param int $pointAmount
     * @param Order $order
     *
     * @throws LocalizedException
     */
    public function addTransaction($action, $customer, $pointAmount, $order)
    {
        if (is_string($customer)) {
            $customer = $this->getAccountHelper()->getCustomerById($customer);
        }
        $this->getTransaction()->createTransaction(
            $action,
            $customer,
            new DataObject([
                'point_amount'  => $pointAmount,
                'order_id'      => $order->getId(),
                'store_id'      => $order->getStoreId(),
                'extra_content' => [
                    'increment_id' => $order->getIncrementId()
                ]
            ])
        );
    }

    /**
     * @return bool
     */
    public function isActionImport()
    {
        return $this->isActionImport;
    }

    /**
     * @param bool $isImport
     */
    public function setActionImport($isImport)
    {
        $this->isActionImport = $isImport;
    }

    /**************************************** Landing Page ************************************************************/

    /**
     * @param string $code
     * @param null $storeId
     *
     * @return boolean
     */
    public function getLandingPageConfig($code = '', $storeId = null)
    {
        $code = ($code !== '') ? 'landing_page/' . $code : '';

        return $this->getConfigGeneral($code, $storeId);
    }

    /**************************************** High Light **************************************************************/

    /**
     * @param string $code
     * @param null $storeId
     *
     * @return boolean
     */
    public function getHighlightConfig($code = '', $storeId = null)
    {
        $code = ($code !== '') ? 'high_light/' . $code : '';

        return $this->getConfigGeneral($code, $storeId);
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function checkHighlightEnabledByType($type)
    {
        try {
            $storeId = $this->getStore()->getId();
        } catch (Exception $exception) {
            $storeId = null;
        }

        $customerSession = $this->sessionFactory->create();

        if ($customerSession->isLoggedIn()) {
            return $this->getHighlightConfig($type, $storeId);
        }

        return $this->getHighlightConfig('guest', $storeId) && $this->getHighlightConfig($type, $storeId);
    }

    /**
     * @return string
     */
    public function getFullActionName()
    {
        return $this->_request->getFullActionName();
    }

    /**
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * @param Invoice|Order $order
     * @param AccountReward $account
     * @param float $maxBalance
     * @param float $pointAmount
     *
     * @return mixed
     * @throws Exception
     */
    public function updateRewardEarnWithMaxBalance($order, $account, $maxBalance, $pointAmount)
    {
        if ($maxBalance > 0 && $pointAmount > 0
            && ($account->getBalance() + $pointAmount > $maxBalance)) {
            $items           = [];
            $oldTotalEarn    = (int) $order->getMpRewardEarn();
            $availableAmount = $maxBalance - $account->getBalance();

            $order->setMpRewardEarn($availableAmount);

            if ($availableAmount > 0) {
                foreach ($order->getItems() as $item) {
                    if ($item->getParentItemId()) {
                        continue;
                    }

                    $items[$item->getId()] = ($item->getMpRewardEarn() / $oldTotalEarn);
                }
                if (!empty($items)) {
                    $items = $this->calculateRewardEarn($items, $availableAmount);
                    $this->updateRewardEarn($order->getItems(), $items);
                }
            } else {
                $order->setMpRewardEarn(0);
            }

            $order->save();
        }

        return $order;
    }

    /**
     * @param array $items
     * @param int $totalPointEarn
     *
     * @return mixed
     */
    public function calculateRewardEarn($items, $totalPointEarn)
    {
        $i            = 1;
        $balancePoint = 0;
        $lastElement  = count($items);

        foreach ($items as $key => $item) {
            $point        = $item * $totalPointEarn + $balancePoint;
            $balancePoint = $point - (int) $point;
            $items[$key]  = (int) $point;

            if ($i === $lastElement) {
                $items[$key] = round($point);
            }
            $i++;
        }

        return $items;
    }

    /**
     * @param array $orderItems
     * @param array $items
     *
     * @throws Exception
     */
    public function updateRewardEarn($orderItems, $items)
    {
        /** @var Item $item */
        foreach ($orderItems as $item) {
            if (isset($items[$item->getId()])) {
                $item->setData('mp_reward_earn', $items[$item->getId()]);
                $item->save();
            }
        }
    }

    /**
     * Check the following modules is installed
     *
     * @param string $moduleName
     *
     * @return bool
     */
    public function moduleIsEnable($moduleName)
    {
        $result = false;
        if ($this->_moduleManager->isEnabled($moduleName)) {
            switch ($moduleName) {
                case 'Mageplaza_StoreCredit':
                    $scHelper = $this->objectManager->create(\Mageplaza\StoreCredit\Helper\Data::class);
                    $result   = $scHelper->isEnabled() ? true : false;
                    break;
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isUltimate()
    {
        return false;
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function isEarnWithSpent($storeId = null)
    {
        return $this->getConfigEarning('earning_point_with_spend', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getMessageToGuest($storeId = null)
    {
        return $this->getConfigEarning('message_to_guest', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function isEnabledNoticeToGuest($storeId = null)
    {
        if ($this->isEnabled($storeId)) {
            return $this->getConfigEarning('notice_reward_to_guest', $storeId);
        }

        return false;
    }

    /**
     * @param $item
     * @return string
     */
    public function updateBaseRowTotal($item)
    {
        return $item->getBaseRowTotal() + $item->getBaseTaxAmount() - $item->getBaseDiscountAmount() - $item->getMpRewardBaseDiscount();
    }

    /**
     * @param $item
     * @return string
     */
    public function updateRowTotal($item)
    {
        return $item->getRowTotal() + $item->getTaxAmount() - $item->getDiscountAmount() - $item->getMpRewardDiscount();
    }

    /**
     * @param string $result
     * @param string $query
     * @param string $customHtml
     *
     * @return string
     */
    public function changeHtmlWithDOM($result, $query, $customHtml)
    {
        $dom    = new DOMDocument();
        $result = mb_encode_numericentity($result, [0x80, 0xffff, 0, 0xffff], 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML($result);
        $xpath = new DOMXpath($dom);
        $query = $xpath->query($query);
        if ($query->length > 0) {
            $template = $dom->createDocumentFragment();
            $template->appendXML($customHtml);
            $query->item(0)->appendChild($template);
            $result = $dom->saveHTML();
        }

        return $result;
    }

    /**
     * @param $block
     * @param array $changeHtml
     * @return string|string[]
     *
     */
    public function addColumnRewardDiscountHtml($block, $changeHtml = [])
    {
        $html = '';
        $html = '<th class="col-reward-discount"><span>' . $block->escapeHtml(__('Reward Discount Amount')) . '</span></th>';
        if (isset($changeHtml['result']) && $changeHtml['query']) {
            $changeHtml['result'] = str_replace('discont', 'discount', $changeHtml['result']);
            $html                 = $this->changeHtmlWithDOM($changeHtml['result'], $changeHtml['query'], $html);
            $html                 = str_replace('discount', 'discont', $html);
        }

        return $html;
    }

    /**
     * @param $block
     * @param $result
     * @return string|string[]
     */
    public function headColumn($block, $result)
    {
        $headColumn = '';
        $headColumn = $this->addColumnRewardDiscountHtml(
            $block,
            [
                'result' => $result,
                'query'  => '//th[@class="col-discount"]'
            ]
        );

        return $headColumn;
    }

    /**
     * @param $block
     * @param array $changeHtml
     * @return string
     */
    public function addItemRendererRewardDiscount($block, $changeHtml = [])
    {
        $html = '';
        $html = '<td class="col-reward-discount">' . /* @noEscape */ $block->displayPriceAttribute('mp_reward_discount') . '</td>';
        if (isset($changeHtml['result']) && $changeHtml['query']) {
            $changeHtml['result'] = str_replace('discont', 'discount', $changeHtml['result']);
            $html                 = $this->changeHtmlWithDOM($changeHtml['result'], $changeHtml['query'], $html);
            $html                 = str_replace('discount', 'discont', $html);
        }

        return $html;
    }

    /**
     * @param $block
     * @param array $changeHtml
     * @return mixed|string
     */
    public function caculateTotal($block, $changeHtml = [])
    {
        $html         = '';
        $baseRowTotal = $this->updateBaseRowTotal($block->getItem());
        $rowTotal     = $this->updateRowTotal($block->getItem());
        $html         = /* @noEscape */ $block->displayPrices($baseRowTotal, $rowTotal);

        if (isset($changeHtml['result']) && $changeHtml['query']) {
            if (strpos($changeHtml['result'], 'price-excl-tax') === false) {
                $changeHtml['result'] = $html;
                $html = $this->changeHtmlWithDOM($changeHtml['result'], $changeHtml['query'], $html);
            } else {
                return $changeHtml['result'];
            }
        }

        return $html;
    }
}
