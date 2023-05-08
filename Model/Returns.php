<?php
namespace Stonewave\ReturnForm\Model;

class Returns extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'stonewave_returns';

    const BASE_MEDIA_PATH = 'stonewave/returns/images';

    protected $_cacheTag = 'stonewave_returns';

    protected $_eventPrefix = 'stonewave_returns';

    protected function _construct()
    {
        $this->_init('Stonewave\ReturnForm\Model\ResourceModel\Returns');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];
        return $values;
    }
}
