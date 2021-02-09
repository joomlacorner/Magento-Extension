<?php

namespace Shippop\Ecommerce\Controller\Adminhtml\Ajax;

class ConfirmBooking extends \Magento\Backend\App\Action
{
    protected $resultJsonFactory;
    protected $formKeyValidator;
    protected $_utility;
    protected $urlBuilder;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Shippop\Ecommerce\Helper\Utility $Utility,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->_utility = $Utility;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        if ($this->formKeyValidator->validate($this->getRequest()) &&
            $this->getRequest()->getParam("order_ids") &&
            $this->getRequest()->getParam("select_courier")) {
            $order_ids = $this->getRequest()->getParam("order_ids");
            $select_courier = $this->getRequest()->getParam("select_courier");
            $bookingOrder = $this->_utility->bookingOrder($order_ids, $select_courier);
            if ($bookingOrder && $bookingOrder['status']) {
                $response = [
                    'status' => true,
                    'bookingOrder' => $bookingOrder,
                    'order_ids' => $order_ids
                ];
                $purchase_id = (empty($bookingOrder["purchase_id"])) ? 0 : $bookingOrder["purchase_id"];
                $payment_url = (empty($bookingOrder["payment_url"])) ? false : $bookingOrder["payment_url"];

                if ($payment_url) {
                    $response["redirect"] = $payment_url;
                } else {
                    $response['purchase_id'] = $purchase_id;
                    $confirmPurchase = $this->_utility->confirmPurchase($purchase_id, $order_ids);
                    $response['confirmPurchase'] = $confirmPurchase;
                    if (!empty($confirmPurchase['status']) && $confirmPurchase['status']) {
                        $order_ids = explode(",", $order_ids);
                        foreach ($order_ids as $order_id) {
                            $this->_utility->specm_create_shipment_tracking(
                                $order_id,
                                $bookingOrder["content_data"][$order_id]["courier_code"],
                                $bookingOrder["content_data"][$order_id]["courier"],
                                $bookingOrder["content_data"][$order_id]["tracking_code"]
                            );
                        }

                        $response['status'] = true;
                        $response['message'] = "<h3 style='text-align: center;'>" . __("Booking confirmed and Payment completed") . "</h3>";
                        $response['message2'] = "<p style='text-align: center;'>" . __("Please print waybill") . "</p>";
                        $response['print_waybill_link'] = $this->urlBuilder->getUrl("shippop/ecommerce/courierparcel");
                    } else {
                        $response['status'] = false;
                        $response['message'] = $confirmPurchase["message"];
                    }
                }
            } else {
                $tmp_message = "";
                $skip_message = ["optional"];
                if (!empty($bookingOrder["message"])) {
                    $tmp_message = $bookingOrder["message"];
                } else {
                    foreach ($bookingOrder['data'] as $courier) {
                        foreach ($courier as $value) {
                            if (!in_array($value['remark'], $skip_message)) {
                                $tmp_message = $value['courier_name'] . " : " . $value['remark'];
                                break;
                            }
                        }
                    }
                }

                $response['status'] = false;
                $response['message'] = $tmp_message;
                $response['bookingOrder'] = $bookingOrder;
            }
        } else {
            $response = [
                'status' => false,
                'message' => __("Access Denied")
            ];
        }

        return $result->setData($response);
    }
}
