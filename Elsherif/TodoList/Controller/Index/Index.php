<?php
declare(strict_types=1);
namespace Elsherif\TodoList\Controller\Index;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

class Index implements HttpGetActionInterface
{
    public function __construct(
        private PageFactory $pageFactory,
        private RequestInterface $request
    ){}

    public function execute()
    {
        $id = $this->request->getParam('id');
        $page = $this->pageFactory->create();
//        $page->setActiveMenu('TodoList::todo_list');
        $page->getConfig()->getTitle()->prepend(__('TodoList'));
        return $page;

    }

}
