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
 * @package   Mageplaza_LazyLoading
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\LazyLoading\Plugin\Model\Template;

use Magento\Cms\Model\Template\Filter as CmsFilter;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\Io\File;
use Mageplaza\LazyLoading\Helper\Data as HelperData;
use Mageplaza\LazyLoading\Helper\Image as HelperImage;
use Mageplaza\LazyLoading\Model\Config\Source\System\LoadingType;
use Mageplaza\LazyLoading\Model\Config\Source\System\PlaceholderType;

/**
 * Class Filter
 *
 * @package Mageplaza\LazyLoading\Plugin\Model\Template
 */
class Filter
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var HelperImage
     */
    protected $helperImage;

    /**
     * @var File
     */
    protected $file;

    /**
     * Filter constructor.
     * @param HelperData $helperData
     * @param HelperImage $helperImage
     * @param File $file
     */
    public function __construct(
        HelperData $helperData,
        HelperImage $helperImage,
        File $file
    ) {
        $this->helperData     = $helperData;
        $this->helperImage    = $helperImage;
        $this->file           = $file;
    }

    /**
     * @param CmsFilter $filter
     * @param string $result
     *
     * @return mixed
     * @throws NoSuchEntityException
     * @SuppressWarnings("Unused")
     */
    public function afterFilter(CmsFilter $filter, $result)
    {
        if (!$this->helperData->isEnabled() || !$this->helperData->isLazyLoad()) {
            return $result;
        }

        $placeHolder = '';
        $holderType  = '';
        $loadingType = $this->helperData->getLoadingType();

        if ($loadingType === LoadingType::ICON) {
            $class       = 'mplazyload mplazyload-icon mplazyload-cms';
            $placeHolder = $this->helperData->getIcon();
        } else {
            $holderType = $this->helperData->getPlaceholderType();
            $class      = 'mplazyload mplazyload-' . $this->helperData->getPlaceholderType();
            if ($holderType === PlaceholderType::TRANSPARENT) {
                $placeHolder = HelperData::DEFAULT_IMAGE;
            }
        }

        preg_match_all('/<img.*?src="(.*?)"[^\>]+>/', $result, $matches);
        $replaced = [];
        $search   = [];

        foreach ($matches[0] as $img) {
            if ($img && !$this->helperData->isExcludeText($this->getImageText($img))) {
                if ($holderType !== PlaceholderType::TRANSPARENT && $loadingType === LoadingType::PLACEHOLDER) {
                    $imgSrc  = $this->getImageSrc($img);
                    $imgPath = substr($imgSrc, strpos($imgSrc, 'pub'));
                    $imgInfo = $this->file->getPathInfo($imgPath);
                    $this->helperData->optimizeImage($this->helperData->filterSrc($imgPath), $imgInfo);
                    $placeHolder = $this->helperImage->getBaseMediaUrl()
                        . '/mageplaza/lazyloading/'
                        . $imgInfo['basename'];
                }

                // dont replace twice, in static block widget if they already have been processed and have data-src
                if (strpos($img, 'data-src="') !== false) {
                    continue;
                }

                if (strpos($img, 'class="') !== false) {
                    $newClass = str_replace('class="', 'class="' . $class . ' ', $img);
                } else {
                    $newClass = str_replace('<img', '<img class="' . $class . '"', $img);
                }

                $strProcess = str_replace('src="', 'src="' . $placeHolder . '" data-src="', $newClass);

                if (!$this->helperData->isExcludeClass($this->getImageClass($strProcess))) {
                    $replaced[] = $strProcess;
                    $search[]   = $img;
                }
            }
        }

        return str_replace($search, $replaced, $result);
    }

    /**
     * @param string $img
     *
     * @return array
     */
    public function getImageClass($img)
    {
        preg_match('/class\s*=\s*"(.+?)"/', $img, $matches);
        if ($matches) {
            return explode(' ', $matches[1]);
        }

        return [];
    }

    /**
     * @param string $img
     *
     * @return null
     */
    public function getImageText($img)
    {
        preg_match('/alt\s*=\s*"(.+?)"/', $img, $alt);
        preg_match('/title\s*=\s*"(.+?)"/', $img, $title);
        preg_match('/src\s*=\s*"(.+?)"/', $img, $src);

        $result = '';

        if ($alt && strpos($alt[1], 'title="') !== false) {
            $result .= $alt[1];
        } elseif ($this->helperData->getConfigValue('seo/general/enabled')
            && $this->helperData->getConfigValue('seo/seo_rule/enable_automate_alt_image')) {
            $imgName = substr($src[1], strrpos($src[1], '/'));
            $result  .= preg_replace('/.jpg|.png|.gif|.bmp|.svg|\/|-/', '', $imgName);
        }

        if ($title) {
            $result .= ' ' . $title[1];
        }

        return $result ?: null;
    }

    /**
     * @param string $img
     *
     * @return mixed
     */
    public function getImageSrc($img)
    {
        preg_match('/src\s*=\s*"(.+?)"/', $img, $matches);

        return $matches[1];
    }
}
