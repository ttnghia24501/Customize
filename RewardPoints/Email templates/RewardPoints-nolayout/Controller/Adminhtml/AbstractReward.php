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

namespace Mageplaza\RewardPoints\Controller\Adminhtml;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Mageplaza\RewardPoints\Helper\Data;
use Mageplaza\RewardPoints\Model\RateFactory;
use Mageplaza\RewardPoints\Model\Source\Direction;

/**
 * Class AbstractReward
 * @package Mageplaza\RewardPoints\Controller\Adminhtml
 */
abstract class AbstractReward extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var RateFactory
     */
    protected $rewardRateFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Massactions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * AbstractReward constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param RateFactory $rewardRateFactory
     * @param Registry $registry
     * @param Filter $filter
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        RateFactory $rewardRateFactory,
        Registry $registry,
        Filter $filter,
        Data $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->rewardRateFactory = $rewardRateFactory;
        $this->registry = $registry;
        $this->filter = $filter;
        $this->helper = $helper;

        parent::__construct($context);
    }

    /**
     * Init layout, menu and breadcrumb
     *
     * @return Page
     */
    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE);
        $resultPage->addBreadcrumb(__('Reward Points'), __('Reward Points'));

        return $resultPage;
    }

    /**
     * Initialize reward rate object
     */
    protected function _initRewardRate()
    {
        $rateId = $this->getRequest()->getParam('id', 0);
        $rewardRateModel = $this->rewardRateFactory->create();
        if ($rateId) {
            $rewardRateModel->load($rateId);
            if (!$rewardRateModel->getId()) {
                $this->messageManager->addError(__('This item does not exists.'));

                return null;
            }
        }
        $this->registry->register('reward_rate', $rewardRateModel);

        return $rewardRateModel;
    }

    /**
     * @return ResponseInterface
     */
    public function saveRewardRate()
    {
        $data = $this->getRequest()->getPost('reward_rate');
        if ($data) {
            $rewardRate = $this->_initRewardRate();
            if (!$rewardRate) {
                return $this->_redirect('*/*/');
            }

            $data['direction'] = $this->getDirection();

            try {
                $rewardRate->addData($data)
                    ->save();

                $this->messageManager->addSuccessMessage(($this->getDirection() == Direction::MONEY_TO_POINT)
                    ? __('The earning rate has been saved successfully.')
                    : __('The spending rate has been saved successfully.'));

                if ($this->getRequest()->getParam('back')) {
                    return $this->_redirect('*/*/edit', ['id' => $rewardRate->getId()]);
                }
            } catch (Exception $exception) {
                $this->messageManager->addErrorMessage(__('An error occurred while saving the rate. Please try again later.' . $exception->getMessage()));

                return $this->_redirect('*/*/edit', ['id' => $rewardRate->getId()]);
            }
        }

        return $this->_redirect('*/*/');
    }

    /**
     * @return ResponseInterface
     */
    public function deleteRewardRate()
    {
        $rewardRate = $this->_initRewardRate();
        if (!$rewardRate || !$rewardRate->getId()) {
            return $this->_redirect('*/*/');
        }

        try {
            $rewardRate->delete();
            $this->messageManager->addSuccessMessage(
                ($this->getDirection() == Direction::MONEY_TO_POINT)
                    ? __('The earning rate has been deleted successfully.')
                    : __('The spending rate has been deleted successfully.')
            );
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while deleting the rate. Please try again later.'));

            return $this->_redirect('*/*/edit');
        }

        return $this->_redirect('*/*/');
    }

    /**
     * @return ResponseInterface
     * @throws LocalizedException
     */
    public function massDelete()
    {
        $collection = $this->filter->getCollection($this->rewardRateFactory->create()->getCollection());
        $deleted = 0;
        foreach ($collection->getItems() as $item) {
            try {
                $item->delete();
                $deleted++;
            } catch (Exception $e) {
                $this->messageManager->addSuccessMessage(__('Cannot delete the rate #%1', $item->getId()));
            }
        }
        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $deleted));

        return $this->_redirect('*/*/');
    }

    /**
     * @return mixed
     */
    abstract protected function getDirection();
}
