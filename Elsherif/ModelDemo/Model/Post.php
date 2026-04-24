<?php

declare(strict_types=1);

namespace Elsherif\ModelDemo\Model;

use Elsherif\ModelDemo\Api\Data\PostInterface;
use Elsherif\ModelDemo\Model\ResourceModel\Post as PostResourceModel;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

class Post extends AbstractModel implements PostInterface, IdentityInterface
{

    public const CACHE_TAG = 'elsherif_blog_post';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;


    protected $_eventPrefix = 'elsherif_blog_post';

    /**
     * Event object name
     */
    protected $_eventObject = 'post';


    protected function _construct(): void
    {
        $this->_init(PostResourceModel::class);
    }

    /**
     * Get identities for cache invalidation
     *
     * @return array
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }


    /**
     * @inheritdoc
     */
    public function getEntityId(): ?int
    {
        $id = $this->getData(self::ENTITY_ID);
        return $id !== null ? (int)$id : null;
    }

    /**
     * @inheritdoc
     */
    public function setEntityId($entityId): self
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): ?string
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @inheritdoc
     */
    public function setTitle(string $title): self
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @inheritdoc
     */
    public function getContent(): ?string
    {
        return $this->getData(self::CONTENT);
    }

    /**
     * @inheritdoc
     */
    public function setContent(?string $content): self
    {
        return $this->setData(self::CONTENT, $content);
    }

    /**
     * @inheritdoc
     */
    public function getUrlKey(): ?string
    {
        return $this->getData(self::URL_KEY);
    }

    /**
     * @inheritdoc
     */
    public function setUrlKey(string $urlKey): self
    {
        return $this->setData(self::URL_KEY, $urlKey);
    }

    /**
     * @inheritdoc
     */
    public function getIsActive(): bool
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    /**
     * @inheritdoc
     */
    public function setIsActive(bool $isActive): self
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt(string $createdAt): self
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt(string $updatedAt): self
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
