<?php

class Products
{
    private $product_id;
    private $product_name;
    private $price;
    private $table_name = "products";

    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->product_id;
    }

    /**
     * @param mixed $product_id
     */
    public function setProductId($product_id)
    {
        $this->product_id = $product_id;
    }

    /**
     * @return mixed
     */
    public function getProductName()
    {
        return $this->product_name;
    }

    /**
     * @param mixed $product_name
     */
    public function setProductName($product_name)
    {
        $this->product_name = $product_name;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->table_name;
    }

    public function setVars($data)
    {
        if (isset($data['product_id'])) $this->setProductId($data['product_id']);
        if (isset($data['product_name'])) $this->setProductName($data['product_name']);
        if (isset($data['price'])) $this->setPrice($data['price']);
    }

    public function getVars()
    {
        $return = array();
        $return['price'] = $this->getPrice();
        $return['product_id'] = $this->getProductId();
        $return['product_name'] = $this->getProductName();
        return $return;
    }

}