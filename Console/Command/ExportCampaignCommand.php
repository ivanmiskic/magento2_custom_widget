<?php
/**
 * Copyright © 2026
 * @category Null
 * @package Null_Blueprint
 * @author Ivan Miskic
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Null\Blueprint\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\Serializer\Json;
use Null\Blueprint\Api\Data\CampaignInterface;
use Null\Blueprint\Model\ResourceModel\Campaign\CollectionFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCampaignCommand extends Command
{
    private const OPTION_FILE = 'file';

    public function __construct(
        private readonly CollectionFactory $collectionFactory,
        private readonly Filesystem $filesystem,
        private readonly Json $json
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('blueprint:campaign:export');
        $this->setDescription('Export all Blueprint campaigns to JSON.');
        $this->addOption(
            self::OPTION_FILE,
            null,
            InputOption::VALUE_OPTIONAL,
            'Absolute path or path relative to var/',
            'export/null_blueprint_campaigns.json'
        );
        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rows = [];
        foreach ($this->collectionFactory->create() as $campaign) {
            $rows[] = [
                CampaignInterface::CAMPAIGN_ID => $campaign->getCampaignId(),
                CampaignInterface::IDENTIFIER => $campaign->getIdentifier(),
                CampaignInterface::TITLE => $campaign->getTitle(),
                CampaignInterface::STATUS => $campaign->getStatus(),
                CampaignInterface::START_AT => $campaign->getStartAt(),
                CampaignInterface::END_AT => $campaign->getEndAt(),
                CampaignInterface::SORT_BY => $campaign->getSortBy(),
                CampaignInterface::SORT_ORDER => $campaign->getSortOrder(),
                CampaignInterface::PRODUCTS_COUNT => $campaign->getProductsCount(),
                CampaignInterface::STORE_IDS => $campaign->getStoreIds(),
            ];
        }

        $relative = ltrim((string)$input->getOption(self::OPTION_FILE), '/');
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $directory->writeFile($relative, $this->json->serialize($rows));
        $output->writeln(sprintf('<info>Wrote %d campaign(s) to var/%s</info>', count($rows), $relative));
        return Command::SUCCESS;
    }
}
