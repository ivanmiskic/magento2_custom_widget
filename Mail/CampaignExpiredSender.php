<?php
/**
 * Copyright © 2026
 * @category Null
 * @package Null_Blueprint
 * @author Ivan Miskic
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Null\Blueprint\Mail;

use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\Store;
use Null\Blueprint\Api\Data\CampaignInterface;
use Null\Blueprint\Model\Config;
use Psr\Log\LoggerInterface;

/**
 * PATTERN: Email — TransportBuilder + etc/email_templates.xml + view/frontend/email/*.html
 */
class CampaignExpiredSender
{
    public const TEMPLATE_ID = 'null_blueprint_campaign_expired';

    public function __construct(
        private readonly TransportBuilder $transportBuilder,
        private readonly StateInterface $inlineTranslation,
        private readonly Config $config,
        private readonly LoggerInterface $logger
    ) {
    }

    public function send(CampaignInterface $campaign): void
    {
        $recipient = $this->config->getNotificationEmail();
        if ($recipient === '') {
            return;
        }

        $this->inlineTranslation->suspend();
        try {
            $transport = $this->transportBuilder
                ->setTemplateIdentifier(self::TEMPLATE_ID)
                ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => Store::DEFAULT_STORE_ID])
                ->setTemplateVars([
                    'title' => $campaign->getTitle(),
                    'identifier' => $campaign->getIdentifier(),
                    'end_at' => $campaign->getEndAt(),
                ])
                ->setFromByScope('general')
                ->addTo($recipient)
                ->getTransport();
            $transport->sendMessage();
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
        } finally {
            $this->inlineTranslation->resume();
        }
    }
}
