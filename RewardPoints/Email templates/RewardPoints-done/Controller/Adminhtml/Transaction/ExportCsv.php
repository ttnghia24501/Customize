<?php

namespace Mageplaza\RewardPoints\Controller\Adminhtml\Transaction;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\MetadataProvider;
use Psr\Log\LoggerInterface;

/**
 * Class gridToCsv
 * @package Mageplaza\RewardPoints\Controller\Adminhtml\Transaction
 */
class ExportCsv extends Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Mageplaza_RewardPoints::transaction';

    /**
     * @var DirectoryList
     */
    protected $directory;

    /**
     * @var MetadataProvider
     */
    protected $metadataProvider;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * gridToCsv constructor.
     *
     * @param Context $context
     * @param Filesystem $filesystem
     * @param MetadataProvider $metadataProvider
     * @param FileFactory $fileFactory
     * @param Filter|null $filter
     * @param LoggerInterface|null $logger
     *
     * @throws FileSystemException
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        MetadataProvider $metadataProvider,
        FileFactory $fileFactory,
        Filter $filter = null,
        LoggerInterface $logger = null
    ) {
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->metadataProvider = $metadataProvider;
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->filter = $filter ?: ObjectManager::getInstance()->get(Filter::class);
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * Export data provider to CSV
     *
     * @return ResponseInterface|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        return $this->fileFactory->create('export.csv', $this->getCsvFile(), 'var');
    }

    /**
     * Returns CSV file
     *
     * @return array
     * @throws LocalizedException
     * @throws Exception
     */
    public function getCsvFile()
    {
        $component = $this->filter->getComponent();

        $name = 'MageplazaRewardTransaction';
        $file = 'export/' . $component->getName() . $name . '.csv';

        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();
        $dataProvider = $component->getContext()->getDataProvider();
        $fields = [];

        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $header = [];
        $i = 1;
        $searchCriteria = $dataProvider->getSearchCriteria()
            ->setCurrentPage($i)
            ->setPageSize(200);
        $totalCount = (int)$dataProvider->getSearchResult()->getTotalCount();
        while ($totalCount > 0) {
            $items = $dataProvider->getSearchResult()->getItems();
            foreach ($items as $item) {
                $item->setComment($item->getTitle());
                $item->unsOrigData()->unsIdFieldName()->unsTitle();
                $this->metadataProvider->convertDate($item, $component->getName());
                if (!$header) {
                    $fields = array_keys($item->getData());
                    $header = $fields;
                    $stream->writeCsv($fields);
                }

                $stream->writeCsv($this->metadataProvider->getRowData($item, $fields, []));
            }
            $searchCriteria->setCurrentPage(++$i);
            $totalCount -= 200;
        }
        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ];
    }
}
