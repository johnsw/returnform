<?php

namespace Stonewave\ReturnForm\Model\ResourceModel;

class Returns extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {
        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->_init('sw_returns', 'id');
    }
}
