<?php
declare(strict_types=1);

namespace Elsherif\ControllersDemo\Controller\Form;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Message\ManagerInterface;

class Submit implements HttpPostActionInterface
{
    private RedirectFactory $redirectFactory;
    private RequestInterface $request;
    private ManagerInterface $manager;

    public function __construct(
        RedirectFactory $redirectFactory,
        RequestInterface $request,
        ManagerInterface $manager
    ) {
        $this->redirectFactory = $redirectFactory;
        $this->request = $request;
        $this->manager = $manager;
    }

    public function execute(): Redirect
    {
        $name = $this->request->getParam('name');
        if ($name) {
            $this->manager->addSuccessMessage(__('Hello, %1', $name));
        } else {
            $this->manager->addErrorMessage(__('Name is required'));
        }
        $redirect = $this->redirectFactory->create();
        $redirect->setPath('controllerdemo/index/index');
        return $redirect;
    }
}
