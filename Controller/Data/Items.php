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

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactorys,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        ImageBuilder               $imageBuilder
	) {
		parent::__construct($context);
		$this->resourceConnection = $resourceConnection;
		$this->jsonFactorys = $jsonFactorys;
        $this->order = $order;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->imageBuilder = $imageBuilder;
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
                'id'      => $item->getProductId(),
                'sku'     => $item->getSku(),
                'name'    => $item->getName(),
                'image'   => $this->getProductImage($this->productRepository->getById($item->getProductId()), 'sw_giftwrap'),
                'item_id' => $item->getId()
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
}