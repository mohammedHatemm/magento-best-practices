<?php

namespace Elsherif\BlocksDemo\Block;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;

class Demo extends Template
{

    /**
     * @param Context $context
     * @param array $data
     */

    public function __construct(Context $context, array $data = [])
    {
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function WelcomeMassage():string
    {
        return 'Welcome to Elsherif BlocksDemo Module ';

    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreName():string
    {
        return $this->_storeManager->getStore()->getName();

    }


}
