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

namespace Mageplaza\BetterWishlist\Controller\Customer;

use Exception;
use Magento\Customer\Helper\View;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\View\Result\Layout as ResultLayout;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Model\AuthenticationStateInterface;
use Magento\Wishlist\Model\Config;
use Mageplaza\BetterWishlist\Block\Share\Email\Items;
use Mageplaza\BetterWishlist\Helper\Data;
use Magento\Framework\Validator\ValidatorChain;
use Magento\Framework\Validator\ValidateException;

/**
 * Class Send
 * @package Mageplaza\BetterWishlist\Controller\Customer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Send extends Action
{
    /**
     * @var View
     */
    protected $_customerHelperView;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var Config
     */
    protected $_wishlistConfig;

    /**
     * @var WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var Validator
     */
    protected $_formKeyValidator;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var AuthenticationStateInterface
     */
    protected $authenticationState;

    /**
     * @var RedirectInterface
     */
    protected $redirector;

    /**
     * Send constructor.
     *
     * @param Context $context
     * @param Validator $formKeyValidator
     * @param Session $customerSession
     * @param WishlistProviderInterface $wishlistProvider
     * @param Config $wishlistConfig
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param View $customerHelperView
     * @param AuthenticationStateInterface $authenticationState
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Validator $formKeyValidator,
        Session $customerSession,
        WishlistProviderInterface $wishlistProvider,
        Config $wishlistConfig,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        View $customerHelperView,
        AuthenticationStateInterface $authenticationState,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->_formKeyValidator   = $formKeyValidator;
        $this->_customerSession    = $customerSession;
        $this->wishlistProvider    = $wishlistProvider;
        $this->_wishlistConfig     = $wishlistConfig;
        $this->_transportBuilder   = $transportBuilder;
        $this->inlineTranslation   = $inlineTranslation;
        $this->_customerHelperView = $customerHelperView;
        $this->authenticationState = $authenticationState;
        $this->redirector          = $context->getRedirect();
        $this->scopeConfig         = $scopeConfig;
        $this->storeManager        = $storeManager;

        parent::__construct($context);
    }

    /**
     * Share wishlist
     * @return ResponseInterface|ResultInterface
     * @throws ValidateException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        if ($this->authenticationState->isEnabled() && !$this->_customerSession->isLoggedIn()) {
            if (!$this->_customerSession->getBeforeWishlistUrl()) {
                $this->_customerSession->setBeforeWishlistUrl($this->redirector->getRefererUrl());
            }
            $this->_customerSession->setBeforeWishlistRequest($this->getRequest()->getParams());
            $this->_customerSession->setBeforeRequestParams($this->_customerSession->getBeforeWishlistRequest());
            $this->_customerSession->setBeforeModuleName('wishlist');
            $this->_customerSession->setBeforeControllerName('index');
            $this->_customerSession->setBeforeAction('add');
            $result = [
                'error'   => true,
                'backUrl' => $this->_url->getUrl('customer/account/login')
            ];

            return $this->getResponse()->representJson(Data::jsonEncode($result));
        }
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $result = [
                'error'   => 1,
                'backUrl' => $this->_url->getUrl('wishlist/index')
            ];

            return $this->getResponse()->representJson(Data::jsonEncode($result));
        }

        $wishlist = $this->wishlistProvider->getWishlist();
        if (!$wishlist) {
            $result = [
                'error'      => 1,
                'errMessage' => __('Page not found.')
            ];

            return $this->getResponse()->representJson(Data::jsonEncode($result));
        }

        $sharingLimit = $this->_wishlistConfig->getSharingEmailLimit();
        $textLimit    = $this->_wishlistConfig->getSharingTextLimit();
        $emailsLeft   = $sharingLimit - $wishlist->getShared();

        $emails = $this->getRequest()->getPost('emails');
        $emails = empty($emails) ? $emails : explode(',', $emails);

        $error   = false;
        $message = (string) $this->getRequest()->getPost('message');
        if (strlen($message) > $textLimit) {
            $error = __('Message length must not exceed %1 symbols', $textLimit);
        } else {
            $message = nl2br(htmlspecialchars($message));
            if (empty($emails)) {
                $error = __('Please enter an email address.');
            } else {
                if (count($emails) > $emailsLeft) {
                    $error = __('This wish list can be shared %1 more times.', $emailsLeft);
                } else {
                    foreach ($emails as $index => $email) {
                        $email = trim($email);
                        if (!ValidatorChain::is($email, 'EmailAddress')) {
                            $error = __('Please enter a valid email address.');
                            break;
                        }
                        $emails[$index] = $email;
                    }
                }
            }
        }

        if ($error) {
            $result = [
                'error'      => 1,
                'errMessage' => $error
            ];

            return $this->getResponse()->representJson(Data::jsonEncode($result));
        }
        /**
         * @var ResultLayout $resultLayout
         */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        $this->addLayoutHandles($resultLayout);
        $this->inlineTranslation->suspend();

        $sent = 0;

        try {
            $customer     = $this->_customerSession->getCustomerDataObject();
            $customerName = $this->_customerHelperView->getCustomerName($customer);

            $message     .= $this->getRssLink($wishlist->getId(), $resultLayout);
            $emails      = array_unique($emails);
            $sharingCode = $wishlist->getSharingCode();
            $categoryId  = $this->getRequest()->getParam('categoryId');
            $items       = $this->getWishlistItems($resultLayout);
            try {
                foreach ($emails as $email) {
                    $transport = $this->_transportBuilder->setTemplateIdentifier(
                        $this->scopeConfig->getValue(
                            'wishlist/email/email_template',
                            ScopeInterface::SCOPE_STORE
                        )
                    )->setTemplateOptions([
                        'area'  => Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getStoreId(),
                    ])->setTemplateVars(
                        [
                            'customer'       => $customer,
                            'customerName'   => $customerName,
                            'salable'        => $wishlist->isSalable() ? 'yes' : '',
                            'items'          => $items,
                            'viewOnSiteLink' => $this->_url->getUrl(
                                'wishlist/shared/index',
                                ['code' => $sharingCode, 'categoryId' => $categoryId]
                            ),
                            'message'        => $message,
                            'store'          => $this->storeManager->getStore(),
                        ]
                    )
                        ->setFrom($this->scopeConfig->getValue(
                            'wishlist/email/email_identity',
                            ScopeInterface::SCOPE_STORE
                        ))
                        ->addTo($email)
                        ->getTransport();

                    $transport->sendMessage();

                    $sent++;
                }
            } catch (Exception $e) {
                $wishlist->setShared($wishlist->getShared() + $sent);
                $wishlist->save();
                throw $e;
            }
            $wishlist->setShared($wishlist->getShared() + $sent);
            $wishlist->save();

            $this->inlineTranslation->resume();

            $this->_eventManager->dispatch('wishlist_share', ['wishlist' => $wishlist]);
            $result = [
                'error' => 0,
            ];
            $this->messageManager->addSuccessMessage(__('Your wish list has been shared.'));

            return $this->getResponse()->representJson(Data::jsonEncode($result));
        } catch (Exception $e) {
            $this->inlineTranslation->resume();
            $result = [
                'error'      => 1,
                'errMessage' => $e->getMessage()
            ];

            return $this->getResponse()->representJson(Data::jsonEncode($result));
        }
    }

    /**
     * Prepare to load additional email blocks
     *
     * Add 'wishlist_email_rss' layout handle.
     * Add 'wishlist_email_items' layout handle.
     *
     * @param ResultLayout $resultLayout
     *
     * @return void
     */
    protected function addLayoutHandles(ResultLayout $resultLayout)
    {
        if ($this->getRequest()->getParam('rss_url')) {
            $resultLayout->addHandle('wishlist_email_rss');
        }
        $resultLayout->addHandle('wishlist_email_items');
    }

    /**
     * @param $wishlistId
     * @param ResultLayout $resultLayout
     *
     * @return mixed
     */
    protected function getRssLink($wishlistId, ResultLayout $resultLayout)
    {
        if ($this->getRequest()->getParam('rss_url')) {
            return $resultLayout->getLayout()
                ->getBlock('wishlist.email.rss')
                ->setWishlistId($wishlistId)
                ->toHtml();
        }

        return '';
    }

    /**
     * Retrieve wishlist items content (html)
     *
     * @param ResultLayout $resultLayout
     *
     * @return string
     */
    protected function getWishlistItems(ResultLayout $resultLayout)
    {
        return $resultLayout->getLayout()
            ->createBlock(Items::class)
            ->toHtml();
    }
}
