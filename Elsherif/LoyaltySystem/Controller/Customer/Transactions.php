<?php
/**
 * Customer Transactions History Page
 * URL: /loyalty/customer/transactions
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Controller\Customer;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Controller\Result\RedirectFactory;

class Transactions implements HttpGetActionInterface
{
    private $resultPageFactory;
    private $customerSession;
    private $resultRedirectFactory;

    public function __construct(
        PageFactory $resultPageFactory,
        CustomerSession $customerSession,
        RedirectFactory $resultRedirectFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    public function execute()
    {
        // Require login
        if (!$this->customerSession->isLoggedIn()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('customer/account/login');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Points Transaction History'));
        
        return $resultPage;
    }
}
