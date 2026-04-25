<?php
declare(strict_types=1);

namespace Elsherif\ControllersDemo\Controller\Adminhtml\Demo;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\Page;

class Index extends Action
{
    const ADMIN_RESOURCE = 'Elsherif_ControllersDemo::demo';

    private PageFactory $pageFactory;

    public function __construct(
        Context $context,
        PageFactory $pageFactory
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
    }

    public function execute(): Page
    {
        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->prepend(__('Admin Demo'));
        return $page;
    }
}
