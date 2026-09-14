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

use Magento\Framework\Exception\NoSuchEntityException;
use Null\Blueprint\Api\CampaignRepositoryInterface;
use Null\Blueprint\Api\Data\CampaignInterface;
use Null\Blueprint\Api\Data\CampaignInterfaceFactory;
use Null\Blueprint\Model\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * PATTERN: CLI — register in etc/di.xml on CommandListInterface.
 */
class SeedCampaignCommand extends Command
{
    private const OPTION_FORCE = 'force';

    public function __construct(
        private readonly CampaignRepositoryInterface $campaignRepository,
        private readonly CampaignInterfaceFactory $campaignFactory,
        private readonly Config $config
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('blueprint:campaign:seed');
        $this->setDescription('Create sample-active and sample-draft Blueprint campaigns.');
        $this->addOption(self::OPTION_FORCE, 'f', InputOption::VALUE_NONE, 'Update existing sample campaigns.');
        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $force = (bool)$input->getOption(self::OPTION_FORCE);
        $this->seed(
            $output,
            'sample-active',
            'Sample Active Campaign',
            CampaignInterface::STATUS_ACTIVE,
            $force
        );
        $this->seed(
            $output,
            'sample-draft',
            'Sample Draft Campaign',
            CampaignInterface::STATUS_DRAFT,
            $force
        );
        return Command::SUCCESS;
    }

    private function seed(
        OutputInterface $output,
        string $identifier,
        string $title,
        int $status,
        bool $force
    ): void {
        try {
            $campaign = $this->campaignRepository->getByIdentifier($identifier);
            if (!$force) {
                $output->writeln(sprintf('<comment>Skipped existing %s</comment>', $identifier));
                return;
            }
        } catch (NoSuchEntityException $exception) {
            $campaign = $this->campaignFactory->create();
            $campaign->setIdentifier($identifier);
        }

        $campaign->setTitle($title);
        $campaign->setStatus($status);
        $campaign->setDescription('<p>Seeded by <code>blueprint:campaign:seed</code>.</p>');
        $campaign->setSortBy('name');
        $campaign->setSortOrder('asc');
        $campaign->setProductsCount($this->config->getDefaultProductsCount());
        $campaign->setStoreIds([0]);
        $this->campaignRepository->save($campaign);
        $output->writeln(sprintf('<info>Saved %s</info>', $identifier));
    }
}
