<?php
/**
 * Post Repository Implementation
 */
declare(strict_types=1);

namespace Elsherif\ModelDemo\Model;

use Elsherif\ModelDemo\Api\Data\PostInterface;
use Elsherif\ModelDemo\Api\Data\PostInterfaceFactory;
use Elsherif\ModelDemo\Api\Data\PostSearchResultsInterface;
use Elsherif\ModelDemo\Api\Data\PostSearchResultsInterfaceFactory;
use Elsherif\ModelDemo\Api\PostRepositoryInterface;
use Elsherif\ModelDemo\Model\ResourceModel\Post as PostResourceModel;
use Elsherif\ModelDemo\Model\ResourceModel\Post\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class PostRepository implements PostRepositoryInterface
{
    /**
     * @var array
     */
    private array $instances = [];

    /**
     * Constructor
     *
     * @param PostInterfaceFactory $postFactory
     * @param PostResourceModel $postResourceModel
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param PostSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        private PostInterfaceFactory $postFactory,
        private PostResourceModel $postResourceModel,
        private CollectionFactory $collectionFactory,
        private CollectionProcessorInterface $collectionProcessor,
        private PostSearchResultsInterfaceFactory $searchResultsFactory
    ) {
    }

    /**
     * @inheritdoc
     */
    public function save(PostInterface $post): PostInterface
    {
        try {
            $this->postResourceModel->save($post);
            unset($this->instances[$post->getEntityId()]);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Unable to save post: %1', $exception->getMessage())
            );
        }
        return $post;
    }

    /**
     * @inheritdoc
     */
    public function getById(int $postId): PostInterface
    {
        if (isset($this->instances[$postId])) {
            return $this->instances[$postId];
        }

        /** @var PostInterface $post */
        $post = $this->postFactory->create();
        $this->postResourceModel->load($post, $postId);

        if (!$post->getEntityId()) {
            throw new NoSuchEntityException(
                __('Post with ID "%1" does not exist.', $postId)
            );
        }

        $this->instances[$postId] = $post;
        return $post;
    }

    /**
     * @inheritdoc
     */
    public function getByUrlKey(string $urlKey): PostInterface
    {
        /** @var PostInterface $post */
        $post = $this->postFactory->create();
        $this->postResourceModel->loadByUrlKey($post, $urlKey);

        if (!$post->getEntityId()) {
            throw new NoSuchEntityException(
                __('Post with URL Key "%1" does not exist.', $urlKey)
            );
        }

        return $post;
    }

    /**
     * @inheritdoc
     */
    public function delete(PostInterface $post): bool
    {
        try {
            $postId = $post->getEntityId();
            $this->postResourceModel->delete($post);
            unset($this->instances[$postId]);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Unable to delete post: %1', $exception->getMessage())
            );
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById(int $postId): bool
    {
        $post = $this->getById($postId);
        return $this->delete($post);
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): PostSearchResultsInterface
    {
        $collection = $this->collectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var PostSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }
}
