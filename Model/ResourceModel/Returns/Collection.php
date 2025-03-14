<?php
namespace Stonewave\ReturnForm\Model\ResourceModel\Returns;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'stonewave_returns_collection';
    protected $_eventObject = 'returns_collection';
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->_init('Stonewave\ReturnForm\Model\Returns', 'Stonewave\ReturnForm\Model\ResourceModel\Returns');
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager,'sw_returns',
            \Stonewave\ReturnForm\Model\ResourceModel\Returns::class);
    }

}
