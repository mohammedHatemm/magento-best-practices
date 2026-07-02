<?php
declare(strict_types=1);

namespace Elsherif\TodoList\Controller\JsonRespose;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

class SucessJson implements HttpGetActionInterface
{

    public function __construct(
        private JsonFactory $jsonFactory
    ){}



    public function execute():Json{
        $response = $this->JsonFactory->create();
        return $response->setData([
            'success' => true,
            'message' => 'jsonResponse is success',
            'data' => ['id' => 1 , 'name' => 'mohamed']
        ]);



    }

}
