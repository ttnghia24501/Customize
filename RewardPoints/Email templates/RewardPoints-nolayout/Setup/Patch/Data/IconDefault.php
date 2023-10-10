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
declare(strict_types=1);

namespace Mageplaza\RewardPoints\Setup\Patch\Data;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Psr\Log\LoggerInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class IconDefault implements
    DataPatchInterface,
    PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * IconDefault constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->logger          = $logger;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $this->copyIconDefault();
    }

    /**
     * Copy icon default to media path
     */
    protected function copyIconDefault()
    {
        try {
            /** @var Filesystem\Directory\WriteInterface $mediaDirectory */
            $mediaDirectory = ObjectManager::getInstance()->get(Filesystem::class)
                ->getDirectoryWrite(DirectoryList::MEDIA);
            $mediaDirectory->create('mageplaza/rewardpoints/default');

            $targetPath = $mediaDirectory->getAbsolutePath('mageplaza/rewardpoints/default/point.png');
            $DS         = DIRECTORY_SEPARATOR;
            $dirname    = str_replace('/Setup/Patch', '', dirname(__DIR__));
            $oriPath    = $dirname . $DS . 'view' . $DS . 'frontend' . $DS . 'web' . $DS . 'images' . $DS
                . 'default' . $DS . 'point.png';
            $mediaDirectory->getDriver()->copy($oriPath, $targetPath);
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function revert()
    {
        /** @var Filesystem\Directory\WriteInterface $mediaDirectory */
        $mediaDirectory = ObjectManager::getInstance()->get(Filesystem::class)
            ->getDirectoryWrite(DirectoryList::MEDIA);
        $mediaDirectory->getDriver()->deleteFile($mediaDirectory->getAbsolutePath('mageplaza/rewardpoints/default/point.png'));
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
