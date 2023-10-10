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

namespace Mageplaza\RewardPoints\Model\Action;

use Magento\Framework\Exception\LocalizedException;
use Mageplaza\RewardPoints\Model\Transaction;

/**
 * Interface ActionInterface
 * @package Mageplaza\RewardPoints\Model\Action
 */
interface ActionInterface
{
    /**
     * Get action label to shown on frontend & grid
     * @return mixed
     */
    public function getActionLabel();

    /**
     * Get transaction title by area (frontend/admin)
     *
     * @param Transaction $transaction
     *
     * @return string
     */
    public function getTitle($transaction);

    /**
     * Get action type (earning/spending)
     * @return mixed
     */
    public function getActionType();

    /**
     * Prepare Transaction data
     * @return mixed
     * @throws LocalizedException
     */
    public function prepareTransaction();
}
