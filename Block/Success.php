<?php

namespace Stonewave\ReturnForm\Block;

class Success extends \Magento\Framework\View\Element\Template
{
    protected $scopeConfig;
    protected $unserialize;
    protected $orderCollection;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Unserialize\Unserialize $unserialize
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->unserialize = $unserialize;
    }

    public function getPdfAction(){
        $queryParams = [
            'return_id' => '1'
        ];
        return $this->getUrl('returns/data/pdf',['_query' => $queryParams]);
    }
}
