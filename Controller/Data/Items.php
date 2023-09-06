<?php

namespace Stonewave\ReturnForm\Controller\Data;

use Magento\Framework\App\Action\Action;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\ImageBuilder;

class Items extends Action {

	protected $resourceConnection;
	protected $jsonFactorys;
    protected $order;
    protected $storeManager;
    protected $productRepository;
    protected $imageBuilder;
    protected $attrRepository;
    protected $optionFactory;
    protected $attributeOptionCollection;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactorys,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        ImageBuilder               $imageBuilder,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attrRepository,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $attributeOptionCollection  
	) {
		parent::__construct($context);
		$this->resourceConnection = $resourceConnection;
		$this->jsonFactorys = $jsonFactorys;
        $this->order = $order;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->imageBuilder = $imageBuilder;
        $this->attrRepository = $attrRepository;
        $this->optionFactory = $optionFactory;
        $this->attributeOptionCollection = $attributeOptionCollection; 
	}

	public function execute() {

        $data = $this->getRequest()->getPost();
        
        $order = $this->order->loadByIncrementId($data['order_id']);
        
        if($order->getIncrementId() == ''){
            return $this->jsonFactorys->create()->setData([
                'success' => false
            ]);
        }

        $orderItems = $order->getAllVisibleItems();

        $items = [];
        foreach( $orderItems as $item ){
            $items[] = [
                'id'       => $item->getProductId(),
                'sku'      => $item->getSku(),
                'name'     => $item->getName(),
                'fullname' => $this->getFullTitle($item),
                'image'    => $this->getProductImage($this->productRepository->getById($item->getProductId()), 'sw_giftwrap'),
                'item_id'  => $item->getId()
            ];
        }

        return $this->jsonFactorys->create()->setData([
            'success' => true,
            'data' => $items,
        ]);
	}

    private function getProductImage($product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create()
            ->toHtml();
    }

    public function getOptionValue( $optionValue ){
        $optionFactory = $this->optionFactory->create();
        $optionFactory->load($optionValue); // load by option value
        $attributeId = $optionFactory->getAttributeId(); // atribute id of given option value
        $optionData = $this->attributeOptionCollection
                        ->setPositionOrder('asc')
                        ->setAttributeFilter($attributeId)
                        ->setIdFilter($optionValue)
                        ->setStoreFilter()
                        ->load(); // load option data by attribute id and given option value
        
        return $optionData->getData();
    }

    public function getFullTitle($item){
        $buyInfoRequest = $item->getBuyRequest()->getData();
        if( array_key_exists('super_attribute',$buyInfoRequest) ){ //conf
            $return = '';
            foreach($buyInfoRequest['super_attribute'] as $key => $value ){
                $attribute = $this->attrRepository->get($key);
                $label = $attribute->getFrontendLabel();
                $val = $this->getOptionValue($value)[0]['value'];
                $return .= "<br><strong>".$label." :</strong> ".$val."<br>";
            }
            return $item->getName().' '."<br><strong>".__('Sku').' :</strong> '.$item->getSku().$return;
        }else{ 
            return $item->getName().' '."<br><strong>".__('Sku').' :</strong> '.$item->getSku();
        }
    }
}