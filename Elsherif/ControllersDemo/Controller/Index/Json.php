<?php
declare(strict_types=1);

namespace Elsherif\ControllersDemo\Controller\Index;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json as JsonResult;

class Json implements HttpPostActionInterface
{
    private JsonFactory $jsonFactory;

    public function __construct(JsonFactory $jsonFactory)
    {
        $this->jsonFactory = $jsonFactory;
    }

    public function execute(): JsonResult
    {
        $result = $this->jsonFactory->create();
        $result->setData([
            'success' => true,
            'message' => 'done in demo'
        ]);
        return $result;
    }
}
