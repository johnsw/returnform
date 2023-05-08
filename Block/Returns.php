<?php

namespace Stonewave\ReturnForm\Block;

class Returns extends \Magento\Framework\View\Element\Template
{

    protected $scopeConfig;
    protected $unserialize;

	public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Unserialize\Unserialize $unserialize
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->unserialize = $unserialize;
    }

    public function getReason(){
        $sources_raw = $this->scopeConfig->getValue('return_form_configuration/settings/reason', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $reason = json_decode($sources_raw,true);
        return $reason;
    }

    public function getReturnEmail() {
        return $this->scopeConfig->getValue('return_form_configuration/settings/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getContent() {
        return $this->scopeConfig->getValue('return_form_configuration/settings/description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getOrderItems(){
        return $this->getUrl('returns/data/items');
    }
}
 