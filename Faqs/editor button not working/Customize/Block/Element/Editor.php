<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mageplaza\Customize\Block\Element;

/**
 * Form editor element
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Editor extends \Magento\Framework\Data\Form\Element\Editor
{

    /**
     * @param string $jsSetupObject
     * @param string $forceLoad
     *
     * @return string
     */
    protected function getInlineJs($jsSetupObject, $forceLoad)
    {
        $objectManager  = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $secureRenderer = $objectManager->get(\Magento\Framework\View\Helper\SecureHtmlRenderer::class);
        $serializer     = $objectManager->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $request     = $objectManager->get(\Magento\Framework\App\Request\Http\Proxy::class);

        if($request->getFullActionName() === 'mpfaqs_article_edit'){
            $jsString       = '
                //<![CDATA[
                window.tinyMCE_GZ = window.tinyMCE_GZ || {};
                window.tinyMCE_GZ.loaded = true;
                window.onload = function() {require([
                "jquery",
                "mage/translate",
                "mage/adminhtml/events",
                "mage/adminhtml/wysiwyg/tiny_mce/setup",
                "mage/adminhtml/wysiwyg/widget"
                ], function(jQuery){' .
            "\n" .
            '  (function($) {$.mage.translate.add(' .
            $serializer->serialize(
                $this->getButtonTranslations()
            ) .
            ')})(jQuery);' .
            "\n" .
            $jsSetupObject .
            ' = new wysiwygSetup("' .
            $this->getHtmlId() .
            '", ' .
            $this->getJsonConfig() .
            ');' .
            $forceLoad .
            '
                    editorFormValidationHandler = ' .
            $jsSetupObject .
            '.onFormValidation.bind(' .
            $jsSetupObject .
            ');
                    Event.observe("toggle' .
            $this->getHtmlId() .
            '", "click", ' .
            $jsSetupObject .
            '.toggle.bind(' .
            $jsSetupObject .
            '));
                    varienGlobalEvents.attachEventHandler("formSubmit", editorFormValidationHandler);
                //]]>
                })};';

            return $secureRenderer->renderTag('script', ['type' => 'text/javascript'], $jsString, false);
        }else{
            $jsString = '
                    //<![CDATA[
                    window.tinyMCE_GZ = window.tinyMCE_GZ || {};
                    window.tinyMCE_GZ.loaded = true;
                    require([
                    "jquery",
                    "mage/translate",
                    "mage/adminhtml/events",
                    "mage/adminhtml/wysiwyg/tiny_mce/setup",
                    "mage/adminhtml/wysiwyg/widget"
                    ], function(jQuery){' .
                "\n" .
                '  (function($) {$.mage.translate.add(' .
                $serializer->serialize(
                    $this->getButtonTranslations()
                ) .
                ')})(jQuery);' .
                "\n" .
                $jsSetupObject .
                ' = new wysiwygSetup("' .
                $this->getHtmlId() .
                '", ' .
                $this->getJsonConfig() .
                ');' .
                $forceLoad .
                '
                        editorFormValidationHandler = ' .
                $jsSetupObject .
                '.onFormValidation.bind(' .
                $jsSetupObject .
                ');
                        Event.observe("toggle' .
                $this->getHtmlId() .
                '", "click", ' .
                $jsSetupObject .
                '.toggle.bind(' .
                $jsSetupObject .
                '));
                        varienGlobalEvents.attachEventHandler("formSubmit", editorFormValidationHandler);
                    //]]>
                    });';
            return $secureRenderer->renderTag('script', ['type' => 'text/javascript'], $jsString, false);
        }
    }
}
