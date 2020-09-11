<?php

declare(strict_types=1);

namespace CrimsonAgility\ProductsInRange\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action
{
    /** @var \Magento\Customer\Model\Session */
    protected $_customerSession;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Action\Context $context,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    public function execute()
    {
        if ($this->_customerSession->isLoggedIn()) {
            $response = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        } else {
            $response = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $response->setPath('customer/account/login');
        }
        return $response;
    }
}
