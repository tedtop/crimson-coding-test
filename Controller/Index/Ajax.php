<?php

declare(strict_types=1);

namespace CrimsonAgility\ProductsInRange\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;

class Ajax extends Action
{
    /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory */
    protected $_productCollectionFactory;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\App\Action\Context $context,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        parent::__construct($context, $data);
    }

    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);

            $lowPrice = $this->getRequest()->getParam('low-price');
            $highPrice = $this->getRequest()->getParam('high-price');
            $sortOrder = $this->getRequest()->getParam('sort-order');

            // Backend validation
            if ($highPrice > $lowPrice && ($lowPrice == 0 && $highPrice == 5 || $highPrice <= $lowPrice * 5)) {

                // Use factory to create a new product collection
                $productCollection = $this->_productCollectionFactory->create()
                        //->addFieldToSelect('*')
                        ->addFieldToSelect('name')
                        ->addFieldToSelect('thumbnail')
                        //->addMediaGalleryData()
                        ->joinField('quantity', 'cataloginventory_stock_item', 'qty', 'product_id=entity_id', 'is_in_stock=1')
                        ->addAttributeToFilter('price', ['gteq' => $lowPrice])
                        ->addAttributeToFilter('price', ['lteq' => $highPrice])
                        ->addAttributeToSort('price', $sortOrder)
                        ->setPageSize(10);
                //echo $productCollection->getSelect()->__toString();

                // Copy product data to newly indexed array to preserve sort order when passing to json_encode(); see below
                // print_r($productCollection->toArray()); // this will reorder the products by entity_id when using json_encode()
                // For testing: http://localhost:8080/productsinrange/index/ajax?low-price=5&high-price=10&sort-order=asc
                foreach ($productCollection as $product) {
                    $products[] = $product->getData();
                }

                // Return product result set
                if (isset($products)) {
                    $response->setData(json_encode($products));
                } else {
                    // ...or not
                    $this->getResponse()->setStatusCode(\Zend\Http\Response::STATUS_CODE_400);
                    $response->setData(['success' => false, 'message' => 'No results for given search criteria.']);
                }
            } else {
                // Send backend validation error
                $this->getResponse()->setStatusCode(\Zend\Http\Response::STATUS_CODE_400);
                $response->setData(['success' => false, 'message' => 'Backend validation failed.']);
            }
        } else {
            // Redirect to home page if this controller action is accessed directly
            $response = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $response->setPath('cms/index/index');
        }
        return $response;
    }
}
