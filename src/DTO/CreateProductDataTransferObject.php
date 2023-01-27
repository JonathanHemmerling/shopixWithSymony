<?php

declare(strict_types=1);

namespace App\DTO;

class CreateProductDataTransferObject
{

        private int $mainId;
        private string $displayName;
        private string $productName;
        private string $description;
        private string $price;

    /**
     * @return int
     */
    public function getMainId(): int
    {
        return $this->mainId;
    }

    /**
     * @param int $mainId
     */
    public function setMainId(int $mainId): void
    {
        $this->mainId = $mainId;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * @param string $displayName
     */
    public function setDisplayName(string $displayName): void
    {
        $this->displayName = $displayName;
    }

    /**
     * @return string
     */
    public function getProductName(): string
    {
        return $this->productName;
    }

    /**
     * @param string $productName
     */
    public function setProductName(string $productName): void
    {
        $this->productName = $productName;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getPrice(): string
    {
        return $this->price;
    }

    /**
     * @param string $price
     */
    public function setPrice(string $price): void
    {
        $this->price = $price;
    }


}
