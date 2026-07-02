<?php
declare(strict_types=1);

namespace Elsherif\BlocksDemo\ViewModel;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class DemoViewModel implements ArgumentInterface
{
    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly StoreManagerInterface $storeManager
    ){}

    /**
     * @return array[]
     */
    public function getDemoItems(): array
    {
        return [
            ['id' =>1 , 'name' => 'Demo 1'],
            ['id' =>2 , 'name' => 'Demo 2'],
            ['id' =>3 , 'name' => 'Demo 3'],
            ['id' =>4 , 'name' => 'Demo 4']
        ];

    }

    /**
     * @return string
     *
     */
    public function getStoreCode(): string
    {
        return $this->storeManager->getStore()->getCode();

    }


}
