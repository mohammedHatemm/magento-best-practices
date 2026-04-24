<?php

declare(strict_types=1);

namespace Elsherif\ModelDemo\Model\ResourceModel;

use Elsherif\ModelDemo\Api\Data\PostInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Post extends AbstractDb
{
    /**
     * @var DateTime
     */
    private DateTime $dateTime;

    /**
     * Constructor
     *
     * @param Context $context
     * @param DateTime $dateTime
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        DateTime $dateTime,
        ?string $connectionName = null
    ) {
        $this->dateTime = $dateTime;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('elsherif_blog_post', 'entity_id');
    }

    /**
     * Load post by URL Key
     *
     * @param AbstractModel $object
     * @param string $urlKey
     * @return self
     */
    public function loadByUrlKey(AbstractModel $object, string $urlKey): self
    {
        $connection = $this->getConnection();

        $select = $connection->select()
            ->from($this->getMainTable())
            ->where('url_key = ?', $urlKey);

        $data = $connection->fetchRow($select);

        if ($data) {
            $object->setData($data);
        }

        $this->_afterLoad($object);

        return $this;
    }

    /**
     * Check if URL Key exists
     *
     * @param string $urlKey
     * @param int|null $excludeId
     * @return bool
     */
    public function isUrlKeyExists(string $urlKey, ?int $excludeId = null): bool
    {
        $connection = $this->getConnection();

        $select = $connection->select()
            ->from($this->getMainTable(), 'entity_id')
            ->where('url_key = ?', $urlKey);

        if ($excludeId !== null) {
            $select->where('entity_id != ?', $excludeId);
        }

        return (bool)$connection->fetchOne($select);
    }

    /**
     * Before save - auto generate url_key if empty
     *
     * @param AbstractModel $object
     * @return self
     */
    protected function _beforeSave(AbstractModel $object): self
    {
        // Auto-generate URL Key from title if not set
        if (!$object->getUrlKey() && $object->getTitle()) {
            $urlKey = $this->generateUrlKey($object->getTitle());
            $object->setUrlKey($urlKey);
        }

        // Ensure URL Key is unique
        $urlKey = $object->getUrlKey();
        $originalUrlKey = $urlKey;
        $counter = 1;

        while ($this->isUrlKeyExists($urlKey, $object->getId() ? (int)$object->getId() : null)) {
            $urlKey = $originalUrlKey . '-' . $counter;
            $counter++;
        }
        $object->setUrlKey($urlKey);

        return parent::_beforeSave($object);
    }

    /**
     * Generate URL Key from string
     *
     * @param string $string
     * @return string
     */
    private function generateUrlKey(string $string): string
    {
        // Convert to lowercase
        $urlKey = strtolower($string);

        // Replace non-alphanumeric with hyphens
        $urlKey = preg_replace('/[^a-z0-9]+/', '-', $urlKey);

        // Remove leading/trailing hyphens
        $urlKey = trim($urlKey, '-');

        return $urlKey;
    }
}
