<?php

namespace Stonewave\ReturnForm\Controller\Form;
use Magento\Framework\App\Action\Action;

class Success extends Action {
    
    public function execute() {
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }

}
