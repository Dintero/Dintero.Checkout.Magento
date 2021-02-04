<?php

namespace Dintero\Checkout\Controller\Payment;

use Dintero\Checkout\Model\Api\CLient;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\OrderFactory;

/**
 * Class Success
 *
 * @package Dintero\Checkout\Controller\Payment
 */
class Success extends Action
{
    /**
     * Order factory
     *
     * @var OrderFactory $orderFactory
     */
    protected $orderFactory;

    /**
     * Success constructor.
     *
     * @param Context $context
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        Context $context,
        OrderFactory $orderFactory
    ) {
        parent::__construct($context);
        $this->orderFactory = $orderFactory;
    }

    /**
     * Processing response
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $result = $this->resultRedirectFactory->create();

        if ($this->getRequest()->getParam('transaction_id')) {
            return $result->setPath('checkout/onepage/success');
        }

        $order = $this->orderFactory->create()
            ->loadByIncrementId($this->getRequest()->getParam('merchant_reference'));
        if ($order->getId() && $order->canCancel()) {
            $order->getPayment()
                ->setTransactionId(null)
                ->cancel();

            $order->registerCancellation('Payment Failed')->save();
            $this->_eventManager->dispatch('order_cancel_after', ['order' => $order ]);
        }
        $this->messageManager->addErrorMessage(__('Payment failed'));
        return $result->setPath('checkout/cart');
    }
}
