<?php

namespace Stonewave\ReturnForm\Controller\Index;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    protected $scopeConfig;

    public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
        ScopeConfigInterface $scopeConfig
	){
		$this->resultPageFactory = $resultPageFactory;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
	}

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();

        $useCustomTitle = $this->getConfigData('return_form_configuration/settings/title');

        if($useCustomTitle!=''){
            $resultPage->getConfig()->getTitle()->set($useCustomTitle);
        }else{
            $resultPage->getConfig()->getTitle()->set(__('Return Form'));
        }

        return $resultPage;
    }

    private function getConfigData($path)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }
}
