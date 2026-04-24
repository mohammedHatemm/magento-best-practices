<?php

declare(strict_types=1);

namespace Elsherif\ModelDemo\Api\Data;

/**
 * @api
 */
interface PostInterface
{

    public const ENTITY_ID = 'entity_id';
    public const TITLE = 'title';
    public const CONTENT = 'content';
    public const URL_KEY = 'url_key';
    public const IS_ACTIVE = 'is_active';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    /**
     * Get Entity ID
     *
     * @return int|null
     */
    public function getEntityId(): ?int;

    /**
     * Set Entity ID
     *
     * @param int $entityId
     * @return self
     */
    public function setEntityId(int $entityId): self;

    /**
     * Get Title
     *
     * @return string|null
     */
    public function getTitle(): ?string;

    /**
     * Set Title
     *
     * @param string $title
     * @return self
     */
    public function setTitle(string $title): self;

    /**
     * Get Content
     *
     * @return string|null
     */
    public function getContent(): ?string;

    /**
     * Set Content
     *
     * @param string|null $content
     * @return self
     */
    public function setContent(?string $content): self;

    /**
     * Get URL Key
     *
     * @return string|null
     */
    public function getUrlKey(): ?string;

    /**
     * Set URL Key
     *
     * @param string $urlKey
     * @return self
     */
    public function setUrlKey(string $urlKey): self;

    /**
     * Get Is Active
     *
     * @return bool
     */
    public function getIsActive(): bool;

    /**
     * Set Is Active
     *
     * @param bool $isActive
     * @return self
     */
    public function setIsActive(bool $isActive): self;

    /**
     * Get Created At
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Set Created At
     *
     * @param string $createdAt
     * @return self
     */
    public function setCreatedAt(string $createdAt): self;

    /**
     * @return string|null
     */
    public function getUpdatedAt(): ?string;

    /**
     * @param string $updatedAt
     * @return self
     */
    public function setUpdatedAt(string $updatedAt): self;
}
