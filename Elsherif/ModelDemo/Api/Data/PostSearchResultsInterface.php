<?php
declare(strict_types=1);
namespace Elsherif\ModelDemo\Api\Data;
use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface PostSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Elsherif\ModelDemo\Api\Data\PostInterface[]
     */
    public function getItems();

    /**
     * @param \Elsherif\ModelDemo\Api\Data\PostInterface[] $items
     * @return self
     */
    public function setItems(array $items): self;



}
