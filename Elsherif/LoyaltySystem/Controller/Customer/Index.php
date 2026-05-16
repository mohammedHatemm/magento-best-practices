<?php
/**
 * Customer Points Page
 * URL: /loyalty/customer/index
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Controller\Customer;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Controller\Result\RedirectFactory;

class Index implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @param PageFactory $resultPageFactory
     * @param CustomerSession $customerSession
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        PageFactory $resultPageFactory,
        CustomerSession $customerSession,
        RedirectFactory $resultRedirectFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\View\Result\Page|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('customer/account/login');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Loyalty Points'));
        
        return $resultPage;
    }
}
