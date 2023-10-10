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
 * @package     Mageplaza_RewardPointsPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPoints\Helper;

use Magento\Customer\Model\GroupFactory as CustomerGroupFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;

/**
 * Class Validate
 * @package Mageplaza\RewardPoints\Helper
 */
class Validate extends AbstractData
{
    /**
     * @var CustomerGroupFactory
     */
    protected $customerGroupFactory;

    /**
     * Validate constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param CustomerGroupFactory $customerGroupFactory
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        CustomerGroupFactory $customerGroupFactory
    ) {
        $this->customerGroupFactory = $customerGroupFactory;
        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param array $data
     * @param string $field
     *
     * @throws LocalizedException
     */
    public function validateGreaterThanZero($data, $field)
    {
        if (isset($data[$field]) && $data[$field] <= 0) {
            throw new LocalizedException(__('%1 must be greater than zero.', $field));
        }
    }

    /**
     * @param array $data
     *
     * @return bool
     * @throws LocalizedException
     */
    public function validateWebsiteIds($data)
    {
        if (isset($data['website_ids'])) {
            $ids = $data['website_ids'];
            if (!is_array($ids)) {
                $ids = explode(',', $ids);
            }

            foreach ($ids as $id) {
                if ($id === 0) {
                    throw new NoSuchEntityException(
                        __(sprintf("The website with code %s that was requested wasn't found.", $id))
                    );
                }
                // throw exception if the website id not exits
                $this->storeManager->getWebsite($id);
            }
        }

        return true;
    }

    /**
     * @param array $data
     * @param string $field
     *
     * @return bool
     * @throws LocalizedException
     */
    public function validateCustomerGroupIds($data, $field = 'customer_group_ids')
    {
        if (isset($data[$field])) {
            $ids = $data[$field];
            if (!is_array($ids)) {
                $ids = explode(',', $ids);
            }

            foreach ($ids as $id) {
                if ($id === 0 && isset($data['isUseGuest'])) {
                    continue;
                }

                $group = $this->customerGroupFactory->create()->load($id);
                if (!$group->getId()) {
                    $message = __('No such group %1. Details: %2 field', $id, $field);
                    throw new LocalizedException(__($message));
                }
            }
        }

        return true;
    }

    /**
     * @param array $data
     * @param array $fields
     *
     * @throws LocalizedException
     */
    public function validateRequired($data, $fields)
    {
        foreach ($fields as $field) {
            if (!isset($data[$field])) {
                throw new LocalizedException(__('%1 is required', $field));
            }
        }
    }

    /**
     * @param array $options
     * @param array $data
     * @param string $field
     *
     * @throws LocalizedException
     */
    public function validateOptions($options, $data, $field)
    {
        if (isset($data[$field])) {
            if (!isset($options[$data[$field]])) {
                throw new LocalizedException(__('%1 invalid.', $field));
            }
        }
    }
}
