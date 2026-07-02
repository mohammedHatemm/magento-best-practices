<?php

declare(strict_types=1);
namespace Elsherif\BlocksDemo\Controller\Index;

use Controller;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Result\PageFactory;

class Index implements HttpGetActionInterface
{
    /**
     * @param PageFactory $pageFactory
     */
    public function __construct(
        private readonly PageFactory $pageFactory,
    )
    {}

    /**
     * @return Page
     */
    public function execute():Page
    {
        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->set(__('Block Demo'));
        return $page;

    }

}
