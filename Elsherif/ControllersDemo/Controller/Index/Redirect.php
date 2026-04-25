<?php
declare(strict_types=1);

namespace Elsherif\ControllersDemo\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Redirect as RedirectResult;

class Redirect implements HttpGetActionInterface
{
    private RedirectFactory $redirectFactory;

    public function __construct(RedirectFactory $redirectFactory)
    {
        $this->redirectFactory = $redirectFactory;
    }

    public function execute(): RedirectResult
    {
        $redirect = $this->redirectFactory->create();
        $redirect->setPath('controllerdemo/index/index');
        return $redirect;
    }
}
