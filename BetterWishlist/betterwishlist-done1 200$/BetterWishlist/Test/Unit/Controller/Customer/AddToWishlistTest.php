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
 * @category  Mageplaza
 * @package   Mageplaza_BetterWishlist
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\BetterWishlist\Test\Unit\Controller\Customer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Render;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Block\Customer\Wishlist\Item\Column\Cart;
use Magento\Wishlist\Block\Customer\Wishlist\Item\Column\Image;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Helper\Data as WishlistHelper;
use Magento\Wishlist\Model\AuthenticationStateInterface;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\Wishlist;
use Mageplaza\BetterWishlist\Controller\Customer\AddToWishlist;
use Mageplaza\BetterWishlist\Helper\Data;
use Mageplaza\BetterWishlist\Model\Category;
use Mageplaza\BetterWishlist\Model\CategoryFactory;
use Mageplaza\BetterWishlist\Model\ResourceModel\Category\Collection;
use Mageplaza\BetterWishlist\Model\WishlistItem;
use Mageplaza\BetterWishlist\Model\WishlistItemFactory as MpWishlistItemFactory;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class AddToWishlistTest
 * @package Mageplaza\BetterWishlist\Test\Unit\Controller\Customer
 */
class AddToWishlistTest extends TestCase
{
    /**
     * @var AddToWishlist
     */
    private $object;

    /**
     * @var Context|PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var WishlistProviderInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $wishlistProvider;

    /**
     * @var Session|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_customerSession;

    /**
     * @var ProductRepositoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepository;

    /**
     * @var Validator|PHPUnit_Framework_MockObject_MockObject
     */
    protected $formKeyValidator;

    /**
     * @var AuthenticationStateInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $authenticationState;

    /**
     * @var RedirectInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirector;

    /**
     * @var StoreManagerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var PageFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageFactory;

    /**
     * @var MpWishlistItemFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $mpWishlistItemFactory;

    /**
     * @var CategoryFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryFactory;

    /**
     * @var Category|PHPUnit_Framework_MockObject_MockObject
     */
    protected $category;

    /**
     * @var Data|PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperData;

    /**
     * @var ResultFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactory;

    /**
     * @var WishlistHelper|PHPUnit_Framework_MockObject_MockObject
     */
    protected $wishlistHelper;

    /**
     * @var RequestInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    /**
     * @var UrlInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_url;

    /**
     * @var ResponseInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_response;

    /**
     * @var ManagerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventManager;

    /**
     * @var ObjectManagerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->context               = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()->getMock();
        $this->_customerSession      = $this->getMockBuilder(Session::class)
            ->setMethods([
                'getBeforeWishlistRequest',
                'getBeforeWishlistUrl',
                'setBeforeRequestParams',
                'setBeforeModuleName',
                'setBeforeControllerName',
                'setBeforeWishlistRequest',
                'setBeforeAction',
                'getCustomerId',
                'isLoggedIn'
            ])
            ->disableOriginalConstructor()->getMock();
        $this->wishlistProvider      = $this->getMockBuilder(WishlistProviderInterface::class)
            ->getMock();
        $this->productRepository     = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->getMock();
        $this->authenticationState   = $this->getMockBuilder(AuthenticationStateInterface::class)
            ->getMock();
        $this->formKeyValidator      = $this->getMockBuilder(Validator::class)
            ->setMethods(['validate'])
            ->disableOriginalConstructor()->getMock();
        $this->resultPageFactory     = $this->getMockBuilder(PageFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->wishlistHelper        = $this->getMockBuilder(WishlistHelper::class)
            ->disableOriginalConstructor()->getMock();
        $this->storeManager          = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMock();
        $this->mpWishlistItemFactory = $this->getMockBuilder(MpWishlistItemFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->categoryFactory       = $this->getMockBuilder(CategoryFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->category              = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()->getMock();
        $this->helperData            = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultFactory         = $this->getMockBuilder(ResultFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->context->method('getResultFactory')->willReturn($this->resultFactory);
        $this->_request = $this->getMockBuilder(RequestInterface::class)
            ->getMock();
        $this->context->method('getRequest')->willReturn($this->_request);
        $this->_url = $this->getMockBuilder(UrlInterface::class)
            ->getMock();
        $this->context->method('getUrl')->willReturn($this->_url);
        $this->_response = $this->getMockBuilder(ResponseInterface::class)
            ->setMethods(['representJson'])
            ->getMockForAbstractClass();
        $this->context->method('getResponse')->willReturn($this->_response);
        $this->_eventManager = $this->getMockBuilder(ManagerInterface::class)
            ->getMock();
        $this->context->method('getEventManager')->willReturn($this->_eventManager);
        $this->_objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMock();
        $this->context->method('getObjectManager')->willReturn($this->_objectManager);
        $this->object = new AddToWishlist(
            $this->context,
            $this->_customerSession,
            $this->wishlistProvider,
            $this->productRepository,
            $this->authenticationState,
            $this->formKeyValidator,
            $this->resultPageFactory,
            $this->storeManager,
            $this->wishlistHelper,
            $this->mpWishlistItemFactory,
            $this->categoryFactory,
            $this->helperData
        );
    }

    public function testAdminInstance()
    {
        $this->assertInstanceOf(AddToWishlist::class, $this->object);
    }

    /**
     * @throws LocalizedException
     */
    public function testExecuteNotLogin()
    {
        $resultRedirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultFactory->method('create')->with()->willReturn($resultRedirect);

        $session = $this->_customerSession;

        $requestParams = [
            'toCategoryId'    => 'new',
            'toCategoryName'  => '',
            'newCategoryId'   => '1',
            'newCategoryName' => 'New',
            'form_key'        => 'formkey'
        ];
        $this->_request->method('getParams')->willReturn($requestParams);
        $session->method('getBeforeWishlistRequest')->willReturn(false);
        $this->authenticationState->method('isEnabled')->willReturn(1);
        $session->method('isLoggedIn')->willReturn(false);

        $session->method('getBeforeWishlistUrl')->willReturn(true);
        $session->method('setBeforeWishlistRequest')->with($requestParams);
        $session->method('setBeforeRequestParams')->with($requestParams);
        $session->method('setBeforeModuleName')->with('mpwishlist');
        $session->method('setBeforeControllerName')->with('customer');
        $session->method('setBeforeAction')->with('addToWishlist');

        $this->_url->method('getUrl')->with('customer/account/login')->willReturn('customer/account/login');

        $result = [
            'error'   => true,
            'backUrl' => 'customer/account/login'
        ];

        $this->helperData->method('jsEncode')->with($result)->willReturn('resultencoded');

        $this->_response->method('representJson')->with('resultencoded')->willReturn('abc');

        $this->assertEquals('abc', $this->object->execute());
    }

    public function testExecuteWithLogin()
    {
        $resultRedirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultFactory->method('create')->with()->willReturn($resultRedirect);
        $session       = $this->_customerSession;
        $requestParams = [
            'toCategoryId'    => 'new',
            'toCategoryName'  => '',
            'newCategoryId'   => '1',
            'newCategoryName' => 'New',
            'form_key'        => 'formkey',
            'product'         => '1'
        ];
        $this->_request->method('getParams')->willReturn($requestParams);
        $session->method('getBeforeWishlistRequest')->willReturn(false);
        $this->authenticationState->method('isEnabled')->willReturn(1);
        $session->method('isLoggedIn')->willReturn(true);
        $this->formKeyValidator->method('validate')->with($this->_request)->willReturn(true);
        $wishlist = $this->getMockBuilder(Wishlist::class)
            ->disableOriginalConstructor()->getMock();
        $this->wishlistProvider->method('getWishlist')->willReturn($wishlist);
        $productId = 1;
        $product   = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()->getMock();
        $this->productRepository->method('getById')->with($productId)->willReturn($product);
        $product->method('isVisibleInCatalog')->willReturn(true);
        $buyRequest   = new DataObject($requestParams);
        $wishlistItem = $this->getMockBuilder(Item::class)
            ->setMethods(['setCategoryName', 'setQty', 'getId', 'addNewItem'])
            ->disableOriginalConstructor()->getMock();
        $wishlist->expects($this->once())->method('addNewItem')->with($product, $buyRequest)->willReturn($wishlistItem);
        $wishlistItemId = 1;
        $wishlistItem->method('getId')->willReturn($wishlistItemId);
        $storeId = 1;
        $store   = $this->getMockBuilder(StoreInterface::class)
            ->getMockForAbstractClass();
        $this->storeManager->method('getStore')->willReturn($store);
        $store->method('getId')->willReturn($storeId);
        $toCategory      = $requestParams['toCategoryId'];
        $toCategoryName  = $requestParams['toCategoryName'];
        $newCategory     = $requestParams['newCategoryId'];
        $newCategoryName = $requestParams['newCategoryName'];
        $customerId      = 1;
        $session->method('getCustomerId')->willReturn($customerId);
        $collectionCount      = 1;
        $category0            = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()->getMock();
        $categoryCollection   =
            $this->getMockBuilder(Collection::class)
                ->setMethods([])
                ->disableOriginalConstructor()->getMock();
        $countcategoryFactory = 0;
        $this->categoryFactory->expects($this->at($countcategoryFactory++))->method('create')->willReturn($category0);
        $count = 0;
        $category0->expects($this->at($count++))->method('getCollection')->willReturn($categoryCollection);
        $categoryCollection->method('addFieldToFilter')->with('customer_id', $customerId)->willReturnSelf();
        $categoryCollection->method('getSize')->willReturn($collectionCount);
        $category1           = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()->getMock();
        $categoryCollection1 =
            $this->getMockBuilder(Collection::class)
                ->setMethods([])
                ->disableOriginalConstructor()->getMock();
        $this->categoryFactory->expects($this->at($countcategoryFactory++))->method('create')->willReturn($category1);
        $category1->method('getCollection')->willReturn($categoryCollection1);

        $categoryCollection1->expects($this->at(0))->method('addFieldToFilter')
            ->with('customer_id', $customerId)->willReturnSelf();
        $categoryCollection1->expects($this->at(1))->method('addFieldToFilter')
            ->with('category_name', $newCategoryName)->willReturnSelf();
        $category = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()->getMock();
        $categoryCollection1->method('getFirstItem')->willReturn($category);
        $category->method('getId')->willReturn(null);
        $this->helperData->method('getLimitWishlist')->with($storeId)->willReturn('3');
        $category->method('setData')->with([
            'customer_id'   => $customerId,
            'category_id'   => $newCategory,
            'category_name' => $newCategoryName,
            'store_id'      => $storeId,
        ])->willReturnSelf();
        $category->method('save')->willReturnSelf();
        $toCategory     = $newCategory;
        $toCategoryName = $newCategoryName;
        $wishlist->method('save')->willReturnSelf();
        $mpWishlistItem = $this->getMockBuilder(WishlistItem::class)
            ->setMethods(['getCategoryName', 'loadItem', 'addData', 'getQty', 'save'])
            ->disableOriginalConstructor()->getMock();
        $this->mpWishlistItemFactory->expects($this->once())->method('create')->willReturn($mpWishlistItem);
        $mpWishlistItem->method('loadItem')->with($wishlistItemId, $toCategory)->willReturnSelf();
        $mpWishlistItem->method('getQty')->willReturn('2');
        $mpWishlistItem->method('addData')->with([
            'wishlist_item_id' => $wishlistItemId,
            'category_id'      => $toCategory,
            'category_name'    => $toCategoryName,
            'qty'              => 3
        ])->willReturnSelf();
        $mpWishlistItem->method('save');
        $this->_eventManager->method('dispatch')->with(
            'wishlist_add_product',
            ['wishlist' => $wishlist, 'product' => $product, 'item' => $wishlistItem]
        );
        $session->method('getBeforeWishlistUrl')->willReturn(false);
        $wishlistHelper = $this->getMockBuilder(WishlistHelper::class)
            ->disableOriginalConstructor()->getMock();
        $this->_objectManager->method('get')->with(WishlistHelper::class)->willReturn($wishlistHelper);
        $wishlistHelper->method('calculate');
        $mpWishlistItem->method('getCategoryName')->willReturn($toCategoryName);
        $wishlistItem->method('setCategoryName')->with($toCategoryName)->willReturnSelf();
        $wishlistItem->method('setQty')->with(1)->willReturnSelf();
        $popupHtml = 'popupHtml';
        $page      = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultPageFactory->method('create')->willReturn($page);
        $popupBlock = $this->getMockBuilder(\Mageplaza\BetterWishlist\Block\Customer\Wishlist\Category::class)
            ->setMethods(['setItem', 'setChild', 'setTemplate', 'toHtml'])
            ->disableOriginalConstructor()->getMock();
        $layout     = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()->getMock();
        $page->method('getLayout')->willReturn($layout);
        $countBlock = 0;
        $layout->expects($this->at($countBlock++))->method('createBlock')
            ->with(\Mageplaza\BetterWishlist\Block\Customer\Wishlist\Category::class)->willReturn($popupBlock);
        $popupBlock->method('setTemplate')->with('addafter.phtml')->willReturnSelf();
        $imgBlock = $this->getMockBuilder(Image::class)
            ->disableOriginalConstructor()->getMock();
        $layout->expects($this->at($countBlock++))->method('createBlock')
            ->with(Image::class)->willReturn($imgBlock);
        $imgBlock->method('setTemplate')->with('Magento_Wishlist::item/column/image.phtml')->willReturnSelf();
        $priceBlock = $this->getMockBuilder(Cart::class)
            ->disableOriginalConstructor()->getMock();
        $layout->expects($this->at($countBlock++))->method('createBlock')
            ->with(Cart::class)->willReturn($priceBlock);
        $priceBlock->method('setTemplate')->with('item/column/price.phtml')->willReturnSelf();

        $priceRenderBlock = $this->getMockBuilder(Render::class)
            ->disableOriginalConstructor()->getMock();
        $layout->expects($this->at($countBlock++))->method('createBlock')
            ->with(Render::class)->willReturn($priceRenderBlock);
        $priceRenderBlock->method('setData')->with([
            'price_render'    => 'product.price.render.default',
            'price_type_code' => 'wishlist_configured_price',
            'price_label'     => false,
            'zone'            => 'item_list',
        ])->willReturnSelf();
        $priceBlock->method('setChild')
            ->with('product.price.render.mpwishlist', $priceRenderBlock)->willReturnSelf();
        $popupBlock->expects($this->at(1))->method('setChild')
            ->with('mpwishlist.item.image', $imgBlock)->willReturnSelf();
        $popupBlock->expects($this->at(2))
            ->method('setChild')->with('mpwishlist.item.price', $priceBlock)->willReturnSelf();
        $popupBlock->method('setItem')->with($wishlistItem)->willReturnSelf();
        $popupBlock->method('toHtml')->willReturn($popupHtml);
        $result = [
            'error' => false,
            'popup' => $popupHtml,
        ];
        $this->helperData->method('jsEncode')->with($result)->willReturn('resultEncoded');
        $this->_response->method('representJson')->with('resultEncoded')->willReturn('abc');

        $this->assertEquals('abc', $this->object->execute());
    }
}
