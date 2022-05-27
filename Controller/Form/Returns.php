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

class Returns extends Action
{
    protected $_pageFactory;

    protected $formKeyValidator;

    protected $scopeConfig;

    protected $inlineTranslation;

    protected $transportBuilder;

    protected $storeManager;

    protected $messageManager;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        Validator $formKeyValidator,
        ScopeConfigInterface $scopeConfig,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        ManagerInterface $messageManager
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
    }

    public function execute()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/returns-email-failure.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        /** Validate Form */
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $logger->info(__('Invalid form key'));
            $logger->info(json_encode($this->getRequest()->getParams()));
            $response= ['success' => false, 'error' => __('Something went wrong. Please try again later.')];
            return $this->redirectBack($response);
        }

        /** Send email code here */
        try {
            $params = $this->getRequest()->getParams();

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
                    'reason'  => $params['reason'],
                    'name'  => $params['name']
                ]
            ];
            $this->sendEmail($data);
            $response= ['success' => true];
        } catch (\Exception $e) {
            $response= ['success' => false, 'Error' => __('Something went wrong. Please try again later.')];
            $logger->info(
                print_r(
                    [
                        'Exception' => [
                            'message' => $e->getMessage(),
                            'code' => $e->getCode(),
                            'line' => $e->getLine(),
                            'file' => $e->getFile(),
                            'trace' => $e->getTraceAsString(),
                        ]
                    ], TRUE
                )
            );
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