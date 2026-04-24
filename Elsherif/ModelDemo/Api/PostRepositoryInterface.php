<?php

declare(strict_types=1);

namespace Elsherif\ModelDemo\Api;

use Elsherif\ModelDemo\Api\Data\PostInterface;
use Elsherif\ModelDemo\Api\Data\PostSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @api
 */
interface PostRepositoryInterface
{
    /**
     * Save Post
     *
     * @param PostInterface $post
     * @return PostInterface
     * @throws CouldNotSaveException
     */
    public function save(PostInterface $post): PostInterface;

    /**
     * Get Post by ID
     *
     * @param int $postId
     * @return PostInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $postId): PostInterface;

    /**
     * Get Post by URL Key
     *
     * @param string $urlKey
     * @return PostInterface
     * @throws NoSuchEntityException
     */
    public function getByUrlKey(string $urlKey): PostInterface;

    /**
     * Delete Post
     *
     * @param PostInterface $post
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(PostInterface $post): bool;

    /**
     * Delete Post by ID
     *
     * @param int $postId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $postId): bool;

    /**
     * Get List of Posts
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return PostSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): PostSearchResultsInterface;
}
