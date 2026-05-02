<?php

declare(strict_types=1);

namespace Elsherif\ModelDemo\Model\ResourceModel\Post;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Elsherif\ModelDemo\Model\ResourceModel\Post as PostResourceModel;
use Elsherif\ModelDemo\Model\Post as PostModel;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';
    /**
     * @var string
     */
    protected $_eventPrefix = 'elsherif_blog_post_collection';
    /**
     * @var string
     */
    protected $_eventObject = 'post_collection';

    /**
     * @return void
     */
    protected function _construct():void
    {
        $this->_init(PostModel::class , PostResourceModel::class );
    }

    /**
     * @return $this
     */
    public function addActiveFilter():self
    {
        $this->addFieldToFilter('is_active', ['eq'=>1]);
        return $this;

    }

    /**
     * @param string $urlKey
     * @return $this
     */

    public function addUrlKeyFilter(string $urlKey):self
    {
        $this->addFieldToFilter('url_key', ['eq'=>$urlKey]);
        return $this;
    }

    /**
     * @return $this
     */

    public function orderByNewest(): self
    {
        $this->setOrder('created_at', self::SORT_ORDER_DESC);
        return $this;
    }
    /**
     * Order by oldest first
     *
     * @return self
     */
    public function orderByOldest(): self
    {
        $this->setOrder('created_at', self::SORT_ORDER_ASC);
        return $this;
    }

    /**
     * Get SQL for debug
     *
     * @return string
     */
//    public function getSelectSql(): string
//    {
//        return $this->getSelect()->__toString();
//    }





}
