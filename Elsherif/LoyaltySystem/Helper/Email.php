<?php
/**
 * Email Helper
 * سنستخدمها في Phase 4
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Elsherif\LoyaltySystem\Model\Config;
use Psr\Log\LoggerInterface;

class Email extends AbstractHelper
{
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        StoreManagerInterface $storeManager,
        Config $config,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Send points earned email
     *
     * @param string $customerEmail
     * @param string $customerName
     * @param int $points
     * @param int $newBalance
     * @return bool
     */
    public function sendPointsEarnedEmail(
        string $customerEmail,
        string $customerName,
        int $points,
        int $newBalance
    ): bool {
        if (!$this->config->isSendEarnEmailEnabled()) {
            return false;
        }

        try {
            $this->inlineTranslation->suspend();

            $templateVars = [
                'customer_name' => $customerName,
                'points_earned' => $points,
                'new_balance' => $newBalance
            ];

            $transport = $this->transportBuilder
                ->setTemplateIdentifier('loyalty_points_earned')
                ->setTemplateOptions([
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId()
                ])
                ->setTemplateVars($templateVars)
                ->setFromByScope('general')
                ->addTo($customerEmail, $customerName)
                ->getTransport();

            $transport->sendMessage();
            $this->inlineTranslation->resume();

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Error sending points earned email: ' . $e->getMessage());
            return false;
        }
    }
}
