<?php

namespace Stonewave\ReturnForm\Controller\Form;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Message\ManagerInterface;
use PDO;
use Stonewave\ReturnForm\Model\Returns as ReturnsModel;

class Returns extends Action
{
    protected $_pageFactory;

    protected $formKeyValidator;

    protected $scopeConfig;

    protected $inlineTranslation;

    protected $transportBuilder;

    protected $storeManager;

    protected $messageManager;

    protected $return;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        Validator $formKeyValidator,
        ScopeConfigInterface $scopeConfig,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        ManagerInterface $messageManager,
        ReturnsModel $return
    )
    {
        parent::__construct($context);
        $this->_pageFactory = $pageFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->scopeConfig = $scopeConfig;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
        $this->return = $return;
    }

    public function execute()
    {
        /** Validate Form */
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $response = ['success' => false, 'error' => __('Something went wrong. Please try again later.')];
            return $this->redirectBack($response);
        }

        $params = $this->getRequest()->getParams();

        // echo "<pre>";print_r($params);echo "</pre>";die;

        try{


            $this->return->setData([
                'order_id'           => $params['order_id'],
                'full_name'          => $params['name'],
                'email'              => $params['email'],
                'phone'              => $params['phone'],
                'reason'             => $params['reason'],
                'commend'            => $params['comment'],
                'commend2'           => $params['comment2'],
                'products'           => implode(",",$params['products']),
                'money_return'       => $params['type'],
                'money_return_infos' => $params['iban'] ? $params['iban'].' '.$params['cardholder'].' '.$params['bank'].' '.$params['other-bank'] : null
            ]);

            $this->return->save();

            $url = $this->_url->getUrl('returns/form/success').'/'.$this->return->getId();

        }catch(\Exception $e){
            echo $e->getMessage();die;
        }

        /** Send email code here */
        try {
            $data = [
                'templateId' => 'returns_email_template',
                'from' => [
                    'name' => $this->getConfigData('trans_email/ident_general/name'),
                    'email' => $this->getConfigData('return_form_configuration/settings/email')
                ],
                'to' => [
                    'email' => $this->getConfigData('return_form_configuration/settings/email'),
                    'name' => 'Admin'
                ],
                'variables' => [
                    'email' => $params['email'],
                    'telephone' => $params['phone'],
                    'order_id' => $params['order_id'],
                    'comment'  => $params['comment'],
                    'comment2'  => $params['comment2'],
                    'reason'  => $params['reason'],
                    'name'  => $params['name']
                ]
            ];

            $this->sendEmail($data);

            $response = ['success' => true, 'data' => [ 'redirect' => $url ]];
        } catch (\Exception $e) {
            echo $e->getMessage();die;
            $response= ['success' => false, 'Error' => __('Something went wrong. Please try again later.')];
        }

        return $this->redirectBack($response);
    }

    private function getConfigData($path)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    private function sendEmail($data)
    {
        $this->inlineTranslation->suspend();

        $transport = $this->transportBuilder
            ->setTemplateIdentifier($data['templateId'])
            ->setTemplateOptions(
                [
                    'area' => 'frontend',
                    'store' => $this->storeManager->getStore()->getId()
                ]
            )
            ->setTemplateVars($data['variables'])
            ->setFrom($data['from'])
            ->addTo($data['to']['email'], $data['to']['name'])
            ->getTransport();

        $transport->sendMessage();

        $this->inlineTranslation->resume();

        $this->messageManager->addSuccessMessage(__('Email sent successfully.'));
    }

    private function redirectBack($data){
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($data);
        return $resultJson;
    }
}
