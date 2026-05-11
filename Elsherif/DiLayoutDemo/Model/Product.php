<?php

namespace Elsherif\DiLayoutDemo\Model\Data;

class Product extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Elsherif\DiLayoutDemo\Model\ResourceModel\Product::class);

    }

    /**
     * @return array|mixed|null
     */
    public function getName()
    {
        return $this->getData('name');

    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)

    {
        return $this->setData('name', $name);
    }

    /**
     * @return array|mixed|null
     */
    public function getSku()
    {
        return $this->getData('sku');


    }

    /**
     * @param $sku
     * @return $this
     */
    public function setSku($sku)
    {
        return $this->setData('sku', $sku);
    }

    /**
     * @return array|mixed|null
     */
    public function getPrice()
    {
        return $this->getData('price');

    }

    /**
     * @param $price
     * @return $this
     */
    public function setPrice($price)
    {
        return $this->setData('price', $price);
    }
    public function validate()
    {
        $errors = [];
        if (!$this->getName()) {
            $errors[] = 'Product name is required';
        }
        if (!$this->getSku()) {
            $errors[] = 'Product sku is required';
        }
        if (!$this->getPrice()) {
            $errors[] = 'Product price is required and it is positive';
        }
        return empty($errors);

    }



}
