<?php

namespace Mageplaza\BetterWishlist\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Catalog\Model\ProductFactory;
class WishlistCheck implements ObserverInterface
{
    protected $customerSession;
    protected $wishlistFactory;
    protected $productFactory;

    public function __construct(
        CustomerSession $customerSession,
        WishlistFactory $wishlistFactory,
        ProductFactory $productFactory
    ) {
        $this->customerSession = $customerSession;
        $this->wishlistFactory = $wishlistFactory;
        $this->productFactory = $productFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $collection = $observer->getEvent()->getCollection();
        $customer = $this->customerSession->getCustomer();
        if ($customer->getId()) {
            $wishlist = $this->wishlistFactory->create()->loadByCustomerId($customer->getId(), true);
            $wishlistItems = $wishlist->getItemCollection();
            $wishlistProducts = [];
            foreach ($wishlistItems as $item) {
                $wishlistProducts[] = $item->getProductId();
            }
            foreach ($collection as $product) {
                $productId = $product->getId();
                if (in_array($productId, $wishlistProducts)) {
                    $product->setData('custom_wishlist_status', true);
                } else {
                    $product->setData('custom_wishlist_status', false);
                }
            }

        }
    }
}
