<?php
/**
 * Transactions Block (ViewModel pattern)
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Block\Customer;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Elsherif\LoyaltySystem\Model\ResourceModel\PointsTransaction\CollectionFactory;
use Elsherif\LoyaltySystem\Helper\Data as DataHelper;

class Transactions extends Template
{
    private $customerSession;
    private $transactionCollectionFactory;
    private $dataHelper;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CollectionFactory $transactionCollectionFactory,
        DataHelper $dataHelper,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get customer transactions
     *
     * @return \Elsherif\LoyaltySystem\Model\ResourceModel\PointsTransaction\Collection
     */
    public function getTransactions()
    {
        $customerId = (int) $this->customerSession->getCustomerId();
        
        $collection = $this->transactionCollectionFactory->create();
        $collection->addFieldToFilter('customer_id', $customerId)
            ->setOrder('created_at', 'DESC');
        
        return $collection;
    }

    /**
     * Format transaction date
     *
     * @param string|null $date
     * @return string
     */
    public function formatTransactionDate(?string $date): string
    {
        if ($date === null) {
            return '';
        }
        return $this->formatTime($date, \IntlDateFormatter::MEDIUM, true);
    }

    /**
     * Format transaction action label
     *
     * @param string|null $action
     * @return \Magento\Framework\Phrase
     */
    public function getTypeLabel(?string $action): \Magento\Framework\Phrase
    {
        $labels = [
            'order_complete' => __('Earned from Order'),
            'redemption' => __('Redeemed'),
            'expired' => __('Expired'),
            'admin_adjust' => __('Admin Adjustment'),
            'refund' => __('Refunded')
        ];
        
        return $labels[$action] ?? __('Other');
    }

    /**
     * Get action CSS class
     *
     * @param string|null $action
     * @return string
     */
    public function getTypeClass(?string $action): string
    {
        $classes = [
            'order_complete' => 'transaction-earn',
            'redemption' => 'transaction-spend',
            'expired' => 'transaction-expire',
            'admin_adjust' => 'transaction-adjust',
            'refund' => 'transaction-refund'
        ];
        
        return isset($classes[$action]) ? $classes[$action] : '';
    }

    /**
     * Get points page URL
     *
     * @return string
     */
    public function getPointsUrl(): string
    {
        return $this->getUrl('loyalty/customer/index');
    }

    /**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        
        if ($this->getTransactions()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'loyalty.transactions.pager'
            )->setCollection(
                $this->getTransactions()
            );
            $this->setChild('pager', $pager);
            $this->getTransactions()->load();
        }
        
        return $this;
    }

    /**
     * Get pager HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
