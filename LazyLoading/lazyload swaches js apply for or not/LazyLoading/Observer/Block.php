<?php


namespace Mageplaza\LazyLoading\Observer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Mageplaza\LazyLoading\Helper\Data;

/**
 * Class Block
 * @package Mageplaza\LazyLoading\Observer
 */
class Block implements ObserverInterface
{
    /**
     * @var bool
     */
    private $isSet = false;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * Block constructor.
     *
     * @param Data $helperData
     */
    public function __construct(
        Data             $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        /** @var AbstractBlock $block */
        $block = $observer->getEvent()->getBlock();
        $transport = $observer->getEvent()->getTransport();
        $mpLazyLoadingApply = $this->helperData->isLazyLoad() ?? 0;
        $html = $transport->getHtml();
        $html .= '<script> window.mpLazyLoadingApply = ' . $mpLazyLoadingApply . ' </script>';
        if (!$this->isSet && $block->getLayout()->isBlock('require.js')) {
            $transport->setHtml($html);
            $this->isSet = true;
        }
    }
}
