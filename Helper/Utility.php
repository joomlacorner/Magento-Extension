<?php

namespace Shippop\Ecommerce\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Session\SessionManagerInterface as CoreSession;

class Utility extends AbstractHelper
{
    const XML_PATH = 'shippop_configuration';

    protected $config;
    protected $_storeManager;
    protected $ShippopAPI;
    protected $_coreSession;
    protected $_crud;
    protected $_assetRepo;
    protected $_io;
    protected $_file;
    protected $_fileUploaderFactory;
    protected $_serializerInterface;

    protected $configWriter;
    protected $cacheTypeList;

    protected $_objectManager;
    private $logger;

    public function __construct(
        Config $config,
        ShippopApi $shippopApi,
        Crud $crud,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        CoreSession $coreSession,
        File $io,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Psr\Log\LoggerInterface $logger,
        SerializerInterface $serializerInterface
    ) {
        $this->config = $config;
        $this->_storeManager = $storeManager;
        $this->ShippopAPI = $shippopApi;
        $this->_coreSession = $coreSession;
        $this->_crud = $crud;
        $this->_assetRepo = $assetRepo;
        $this->_io = $io;
        $this->_file = $file;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->cacheTypeList = $cacheTypeList;
        $this->configWriter = $configWriter;
        $this->_objectManager = $objectManager;
        $this->logger = $logger;
        $this->_serializerInterface = $serializerInterface;
    }

    /**
     * @param string $order_ids
     *
     * @return array
     */
    public function chooseCourier($order_ids = "")
    {
        $data = [];
        $from = $this->getFromAddress();
        foreach (explode(",", $order_ids) as $order_id) {
            $orderData = $this->getOrderData($order_id);
            if (array_key_exists("to", $orderData) && array_key_exists("parcel", $orderData)) {
                $data[] = [
                    'from' => $from,
                    'to' => $orderData['to'],
                    'parcel' => $orderData['parcel'],
                    'showall' => 1
                ];
            } else {
                return [
                    'status' => false,
                    'message' => __("Order") . " " . $order_id . " : " . __("Shipping address's incorrect. Please edit shipping address again")
                ];
            }
        }

        $result = $this->ShippopAPI->pricelist($data);
        $result['postData'] = $data;
        if ($result['status']) {
            $result['order_ids'] = $order_ids;
            $result['data'] = $this->prepareCourierObj($result['data']);
        }

        return $result;
    }

    /**
     * @param string $order_ids
     * @param string $select_courier
     *
     * @return array
     */
    public function bookingOrder($order_ids = "", $select_courier = "")
    {
        $data = [];
        $bookings_data = [];
        $from = $this->getFromAddress();
        $order_ids = explode(",", $order_ids);
        foreach ($order_ids as $order_id) {
            $orderData = $this->getOrderData($order_id, true);
            if (array_key_exists("to", $orderData) &&
                array_key_exists("parcel", $orderData) &&
                array_key_exists("products", $orderData) &&
                array_key_exists("payment_method", $orderData)) {
                $args = [
                    'from' => $from,
                    'to' => $orderData["to"],
                    'parcel' => $orderData["parcel"],
                    'product' => $orderData["products"],
                    'courier_code' => $select_courier,
                    'order_id' => $order_id,
                    'payment_method' => $orderData["payment_method"]
                ];

                if ($orderData["cod_amount"]) {
                    $args['cod_amount'] = $orderData["cod_amount"];
                }

                $data[] = $bookings_data[$order_id] = $args;
            } else {
                return [
                    'status' => false,
                    'message' => __("Order") . " " . $order_id . " : " . __("Shipping address's incorrect. Please edit shipping address again")
                ];
            }
        }

        $result = $this->ShippopAPI->booking($data);
        $result['postData'] = $data;
        if ($result['status']) {
            $courier_list = $this->config->getShippopConfig("auth", "courier_list");
            if (!empty($courier_list)) {
                $courier_list = $this->_serializerInterface->unserialize($courier_list);
            } else {
                $courier_list = [];
            }
            $purchase_id = (empty($result["purchase_id"])) ? 0 : $result["purchase_id"];
            foreach ($order_ids as $key => $order_id) {
                if (empty($result["data"][$key])) {
                    continue;
                }
                $data = $result["data"][$key];

                $content_data = [];
                $content_data['order_id'] = $order_id;
                $content_data['purchase_id'] = $purchase_id;
                $content_data['confirm_purchase_status'] = 0;
                $content_data['shippop_status'] = "booking";
                $content_data['tracking_code'] = $data["tracking_code"];
                $content_data['courier_code'] = $data["courier_code"];
                $content_data['courier'] = (!empty($courier_list[$data["courier_code"]]["courier_name"])) ? $courier_list[$data["courier_code"]]["courier_name"] : "";
                $content_data['tracking_code'] = $data["tracking_code"];
                $content_data['courier_tracking_code'] = $data["courier_tracking_code"];
                $content_data['extra'] = ['price' => $data["price"], 'status' => $data["status"], 'booking_data' => $bookings_data[$order_id]];
                $content_data['environment_sandbox'] = 0;
                $this->_crud->update_post_meta($content_data);
                $result['content_data'][$order_id] = $content_data;
            }
        }

        return $result;
    }

    /**
     * @param int $purchase_id
     * @param string $order_ids
     *
     * @return array
     */
    public function confirmPurchase($purchase_id = 0, $order_ids = "")
    {
        $result = $this->ShippopAPI->confirm($purchase_id);
        if (!empty($result) && !empty($result['status']) && $result['status']) {
            $result['confirm_order_ids'] = $this->_crud->update_confirm_purchase($order_ids);
        }
        return $result;
    }

    /**
     * @param string $tracking_code
     *
     * @return array
     */
    public function trackingHistory($tracking_code)
    {
        $result = $this->ShippopAPI->getTrackingOrder($tracking_code);
        if ($result["status"]) {
            $this->prepareTrackingHistory($result);
            $request = [
                "tracking_code" => $tracking_code,
                "order_status" => $result["order_status"]
            ];
            $this->webHooksUpdate($request);
        }
        return $result;
    }

    /**
     * @param string $tracking_code
     * @param string $size
     * @param string $type
     *
     * @return array
     */
    public function labelPrinting($tracking_code, $size, $type)
    {
        $result = $this->ShippopAPI->labelPrinting($tracking_code, $size, $type);

        $response[] = [];
        $response['status'] = false;
        if ($result["status"]) {
            $file_name = time() . "_tracking_label" . "." . $type;
            if ($type == "html") {
                $data = stripcslashes($result["html"]);
            } else {
                $data = base64_decode($result["pdf"]);
            }

            try {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
                $mediaPath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
                $custom_path = 'shippop/preprintlabel/';
                $full_path = $mediaPath . $custom_path;

                $file_removes = [];
                if (!file_exists($full_path)) {
                    $this->_io->mkdir($full_path, 0775);
                } else {
                    $file_removes = $this->clearFileInFolder($full_path);
                }

                $file_removes = [];
                if (file_put_contents($full_path . $file_name, $data)) {
                    $pub_url = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

                    $response['file_url'] = $pub_url . $custom_path . $file_name;
                    $response['file_name'] = $file_name;
                    $response['arg'] = $file_removes;
                    $response['status'] = true;
                } else {
                    $response['message'] = __("Can't download waybill. Please try again later");
                }
            } catch (\Exception $e) {
                $this->specm_writing_log($e->getMessage(), $e);
                $response['message'] = $e->getMessage();
            }
        }

        return $response;
    }

    /**
     * @param string $start_date
     * @param string $end_date
     * @param string $filter_type
     *
     * @return array
     */
    public function reportCOD($start_date, $end_date, $filter_type)
    {
        $data_object = ['status' => true, 'data' => []];
        $result = $this->ShippopAPI->reportCOD($start_date, $end_date, $filter_type);
        if ($result["status"]) {
            $shippop_cod_status = $this->_coreSession->getShippopCodStatus();
            $shippop_status = $this->_coreSession->getShippopStatus();
            foreach ($result["data"] as $item) {
                $data = $this->_crud->get_post_by_meta("tracking_code", $item["tracking_code"], "=", true);
                if (!empty($data) && !empty($data["order_id"])) {
                    $item["increment_id"] = $data["order_id"];
                    $item["filter_type"] = $filter_type;
                    $item["_cod_status"] = $shippop_cod_status[$item["cod_status"]];
                    $item["shippop_status"] = $item["order_status"];
                    $item["_shippop_status"] = (!empty($shippop_status[$item["order_status"]])) ? $shippop_status[$item["order_status"]] : $item["order_status"];
                    $data_object["data"][] = $item;
                }
            }
        }
        return $data_object;
    }

    /**
     * @param string $tracking_code
     *
     * @return array
     */
    public function cancelPurchase($tracking_code)
    {
        $result = $this->ShippopAPI->purchaseCancel($tracking_code);
        if ($result && $result['status']) {
            $data = $this->_crud->get_post_by_meta("tracking_code", $tracking_code, "=", true);
            if (!empty($data) && !empty($data["order_id"])) {
                $args = [
                    'order_id' => $data["order_id"],
                    'shippop_status' => 'cancel'
                ];
                $this->_crud->update_post_meta($args);
            }
            $result['status'] = true;
            $result['message'] = (!empty($result["message"])) ? $result["message"] : __("Success");
        } else {
            $result['message'] = $result["code"] . " - " . $result["message"];
        }
        return $result;
    }

    /**
     * @param array $request
     *
     * @return array
     */
    public function webHooksUpdate($request)
    {
        $data = $this->_crud->get_post_by_meta("tracking_code", $request["tracking_code"], "=", true);
        if (!empty($data) && !empty($data["order_id"])) {
            $status = $request["order_status"];
            $args = [
                'order_id' => $data["order_id"],
                'shippop_status' => $status
            ];
            $this->_crud->update_post_meta($args);

            if ($status == "complete") {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $order = $objectManager->create('\Magento\Sales\Model\Order')->load($data["order_id"]);
                $order->setState("complete")->setStatus("complete");
                $order->addStatusHistoryComment(__("Update status from SHIPPOP's WebHooks", "shippop-ecommerce") . " [STATUS : $status]");
                $order->save();
            }

            $response = [
                "success" => "1"
            ];
        } else {
            $response = [
                "success" => "0"
            ];
        }

        return $response;
    }

    /**
     * @param string $shippop_email
     * @param string $shippop_password
     * @param string $shippop_server
     *
     * @return array
     */
    public function authShippop($shippop_email, $shippop_password, $shippop_server)
    {
        /* TST */
        // $key = "tdiG06240HFAwCFOrVRxzbzuRCgMmpx1";
        // $iv = "UJrkONI192qEmaBk";

        /* PRD */
        $key = "Jfkd0i20r0eif32dFis94dsafb920DKa";
        $iv = "djowr1Aj234fd0aD";

        $sign = $this->specm_encode(json_encode(['email' => $shippop_email, 'password' => $shippop_password]), $key, $iv);
        $response = $this->ShippopAPI->authBearer([
            'clientName' => 'SHIPPOP_WP',
            'clientType' => 'POSTPAID',
            'sign' => $sign
        ], $shippop_server);

        return $response;
    }

    /**
     * @param string $shippop_company
     * @param string $shippop_name
     * @param string $shippop_tel
     * @param string $shippop_email
     * @param string $shippop_courier
     * @param string $shippop_server
     *
     * @return array
     */
    public function registerShippop($shippop_company, $shippop_name, $shippop_tel, $shippop_email, $shippop_courier, $shippop_server)
    {
        $response = $this->ShippopAPI->authRegister([
            'company' => (empty($shippop_company)) ? "(Optional)" : $shippop_company,
            'name' => $shippop_name,
            'phone' => $shippop_tel,
            'email' => $shippop_email,
            'courier' => $shippop_courier,
            'detail' => [
                'domain' => $this->_storeManager->getStore()->getBaseUrl(),
                'webhooks' => $this->_storeManager->getStore()->getBaseUrl() . "rest/V1/shippop-ecommerce/update-status"
            ],
            'channel' => 'magento'
        ], $shippop_server);

        return $response;
    }

    /**
     * @param string $address
     *
     * @return array
     */
    public function addressCorrector($address)
    {
        $Pyadc = $this->ShippopAPI->prepareAddress($address, []);
        $response = ['status' => false];
        if ($Pyadc["status"] == 1) {
            $response['status'] = true;
            $response['type'] = "1";
            $response["suggestion"] = $this->prepare_address_corrector($Pyadc["data"]);
        } elseif ($Pyadc["status"] == 0 && !empty($Pyadc["data"])) {
            $response['status'] = true;
            $response['type'] = "2";
            $response["suggestion"] = $this->prepare_address_corrector($Pyadc["data"]);
        } else {
            $msg = ( !empty($response["message"]) ) ? $response["message"] : '-';
            $response['message'] = __("Incorrect address") . " [ " . $msg . " ] ";
        }
        return $response;
    }

    /**
     * @param array $billing_address
     *
     * @return array
     */
    public function updateBilling($billing_address)
    {
        $response = $this->ShippopAPI->billingUpdate($billing_address);
        return $response;
    }

    /* END API CALL */

    /**
     * @param array $data
     *
     * @return array
     */
    private function prepare_address_corrector($data)
    {
        $args = [];
        $prefix_sub_district = "ตำบล/แขวง";
        $prefix_district = "อำเภอ/เขต";
        $prefix_province = "จังหวัด";
        $prefix_zipcode = "รหัสไปรษณีย์";
        foreach ($data as $key => $value) {
            $_address = ( empty($value["address"]) ) ? "" : $value["address"];
            $args[$key]['full'] = $_address . ' ' . $value["subdistrict"]["replacer"] . ' ' . $value["district"]["replacer"] . ' ' . $value["province"]["replacer"] . ' ' . $value["zipcode"]["replacer"];
            $args[$key]['state'] = $value["subdistrict"]["replacer"];
            $args[$key]['district'] = $value["district"]["replacer"];
            $args[$key]['province'] = $value["province"]["replacer"];
            $args[$key]['postcode'] = $value["zipcode"]["replacer"];
        }

        return $args;
    }

    /**
     * @param string $group
     * @param string $code
     * @param string $value
     *
     * @return boolean
     */
    public function setShippopConfig($group = "auth", $code = "", $value = "")
    {
        $this->configWriter->save(self::XML_PATH . '/' . $group . '/' . $code, $value);

        $this->cacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
        $this->cacheTypeList->cleanType(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER);

        return true;
    }

    /**
     * @param string $group_code
     *
     * @return boolean
     */
    public function deleteShippopConfig($group_code)
    {
        $this->configWriter->delete(self::XML_PATH . '/' . $group_code);

        $this->cacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
        $this->cacheTypeList->cleanType(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER);

        return true;
    }

    /**
     * @param array $TrackingHistory
     *
     * @return boolean
     */
    private function prepareTrackingHistory(&$TrackingHistory)
    {
        $shippop_status = $this->_coreSession->getShippopStatus();
        $parcel_logo = $this->_coreSession->getParcelLogo();
        if (!empty($parcel_logo[$TrackingHistory["courier_code"]])) {
            $logo = $parcel_logo[$TrackingHistory["courier_code"]];
        } else {
            $logo = $parcel_logo["SHP"];
        }

        $TrackingHistory["shippop_status"] = $shippop_status[$TrackingHistory["order_status"]];
        $TrackingHistory["logo"] = $this->_assetRepo->getUrl("Shippop_Ecommerce::images/" . $logo);
        foreach ($TrackingHistory["state"] as $key => $state) {
            $dt = $this->convert_dateThai($state["datetime"]);
            $TrackingHistory["state"][$key]["date"] = $dt["date"];
            $TrackingHistory["state"][$key]["time"] = $dt["time"];
        }

        return true;
    }

    /**
     * @param int $order_id
     * @param bool $get_products
     *
     * @return array
     */
    public function getOrderData($order_id, $get_products = false)
    {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $objectManager->get('\Magento\Sales\Model\Order');
            $orderData = $order->load($order_id);
            $payment = $orderData->getPayment();
            $payment_method = $payment->getMethod();
            $shippingAddress = $orderData->getShippingAddress()->getData();
        } catch (\Exception $e) {
            $this->specm_writing_log($e->getMessage(), $e);
            return ["status" => false, "message" => $e->getMessage()];
        }

        $this->prepareStreet($shippingAddress['street']);
        $to = [
            'province' => (!empty($shippingAddress['province'])) ? $shippingAddress['province'] : "",
            'state' => "",
            'district' => (!empty($shippingAddress['city'])) ? $shippingAddress['city'] : "-",
            'postcode' => $shippingAddress['postcode'],
            'address' => $shippingAddress['street'],
            'name' => $shippingAddress['firstname'] . " " . $shippingAddress['lastname'],
            'tel' => $shippingAddress['telephone'],
            'email' => $shippingAddress['email'],
        ];
        if ($to["province"] == "") {
            $to["province"] = (!empty($shippingAddress['region'])) ? $shippingAddress['region'] : "-";
        }
        if ($to["state"] == "") {
            $to["state"] = (!empty($shippingAddress['city'])) ? $shippingAddress['city'] : "-";
        }
        $shippop_server = $this->config->getShippopConfig("auth", "shippop_server");
        if (strtoupper($shippop_server) === "TH") {
            $address_corrector = $this->addressCorrector($shippingAddress['street']);
            if ($address_corrector["status"] === false) {
                return ["status" => false, "message" => __("Incorrect address")];
            } elseif ($address_corrector["type"] == "1") {
                $to["province"] = $address_corrector["suggestion"][0]["province"];
                $to["state"] = $address_corrector["suggestion"][0]["state"];
                $to["district"] = $address_corrector["suggestion"][0]["district"];
                $to["postcode"] = $address_corrector["suggestion"][0]["postcode"];
            }
        }

        $products = [];
        if ($get_products) {
            $products = $this->getOrderProducts($orderData);
        }

        $cod_amount = 0;
        if ($payment_method === "cashondelivery") {
            $cod_amount = $order->getGrandTotal();
        }

        return ['payment_method' => $payment_method, 'cod_amount' => $cod_amount, 'to' => $to, 'parcel' => $this->getOrderParcel($order_id), 'products' => $products, 'status' => true];
    }

    /**
     * @param int $order_id
     *
     * @return array
     */
    public function getOrderParcel($order_id)
    {
        $order = $this->_crud->get_post_meta($order_id, "extra");
        $extra = [];
        if (!empty($order)) {
            $extra = $this->_serializerInterface->unserialize($order);
        }

        $parcel = [
            'name' => '-',
            'weight' => (!empty($extra["weight"])) ? $extra["weight"] * 1000 : 1,
            'width' => (!empty($extra["width"])) ? $extra["width"] : 1,
            'length' => (!empty($extra["length"])) ? $extra["length"] : 1,
            'height' => (!empty($extra["height"])) ? $extra["height"] : 1
        ];

        return $parcel;
    }

    /**
     * @param object $orderData
     * @param bool $grams
     *
     * @return array
     */
    public function getOrderProducts($orderData, $grams = true)
    {
        $orderItems = $orderData->getAllItems();
        $items = [];
        foreach ($orderItems as $item) {
            $itemData = $item->getData();
            $qty = 1;
            if (!empty($itemData['product_options']['info_buyRequest']['qty'])) {
                $qty = $itemData['product_options']['info_buyRequest']['qty'];
            }
            $weight = $itemData['weight'] * $qty;
            $items[] = [
                'product_code' => $itemData['sku'],
                'name' => $itemData['name'],
                'detail' => $itemData['name'],
                'price' => $itemData['price'],
                'amount' => $qty,
                'weight' =>  floatval(($grams) ? $weight * 1000 : $weight),
            ];
        }

        return $items;
    }

    /**
     * @return array
     */
    public function getFromAddress()
    {
        $pickup = $this->config->getShippopConfig("address", "pickup");
        $pickup = (!empty($pickup)) ? $this->_serializerInterface->unserialize($pickup) : [];
        $address = [
            'province' => (!empty($pickup["province"])) ? $pickup["province"] : "",
            'district' => (!empty($pickup["state"])) ? $pickup["state"] : "",
            'state' => (!empty($pickup["district"])) ? $pickup["district"] : "",
            'postcode' => (!empty($pickup["postcode"])) ? $pickup["postcode"] : "",
            'address' => (!empty($pickup["address"])) ? $pickup["address"] : "",
            'name' => (!empty($pickup["address_name"])) ? $pickup["address_name"] : "",
            'tel' => (!empty($pickup["address_tel"])) ? $pickup["address_tel"] : "",
            'email' => $this->config->getShippopConfig("auth", "shippop_auth_email")
        ];

        return $address;
    }

    /**
     * @param array $courier_list
     *
     * @return array|bool
     */
    public function prepareCourierObj($courier_list)
    {
        $parcel_delivery = $this->_coreSession->getParcelDelivery();
        $on_demand = $this->_coreSession->getOnDemand();
        $parcel_logo = $this->_coreSession->getParcelLogo();

        if (count($courier_list) == 0) {
            return false;
        }

        $new_courier = $courier_list[0];
        $this->setShippopConfig("auth", "courier_list", $this->_serializerInterface->serialize($new_courier));
        foreach ($courier_list as $k => $courier) {
            foreach ($courier as $courier_code => $courier_code_value) {
                $this->fill_courier_args($new_courier[$courier_code]);
                $this->fill_courier_args($courier_code_value);

                if ($k != 0) {
                    if ($new_courier[$courier_code]["available"] != $courier_code_value["available"]) {
                        if ($new_courier[$courier_code]["available"]) {
                            $new_courier[$courier_code] = $courier_code_value;
                        }
                    } elseif (($new_courier[$courier_code]["available"]) && ($courier_code_value["available"])) {
                        $new_courier[$courier_code]["price"] += $courier_code_value["price"];
                    }
                }

                if (!empty($parcel_logo[$courier_code])) {
                    $logo = $parcel_logo[$courier_code];
                } else {
                    $logo = $parcel_logo["SHP"];
                }
                $new_courier[$courier_code]["logo"] = $this->_assetRepo->getUrl("Shippop_Ecommerce::images/" . $logo);
                $new_courier[$courier_code]["pick_up_mode"] = "";
                if (in_array($courier_code, $parcel_delivery["drop_off"])) {
                    $new_courier[$courier_code]["pick_up_mode"] = __("Deliver to Drop Off point yourself") . " ";
                }
                if (in_array($courier_code, $parcel_delivery["pick_up"])) {
                    $new_courier[$courier_code]["pick_up_mode"] .= __("Pick-up service");
                }

                $new_courier[$courier_code]["remark"] = $this->translate_error_code($new_courier[$courier_code]["remark"]);
            }
        }

        $on_demand_args = [];
        foreach ($new_courier as $courier_code => $courier) {
            if (in_array($courier_code, $on_demand)) {
                $on_demand_args[$courier_code] = $courier;
                unset($new_courier[$courier_code]);
            }
        }

        $this->specm_helper_array_sort_by_column($new_courier, 'price');
        $this->specm_helper_array_sort_by_column($on_demand_args, 'price');

        $this->specm_helper_price_format($new_courier);
        $this->specm_helper_price_format($on_demand_args);

        return ['normal' => $new_courier, 'on_demand' => $on_demand_args];
    }

    /**
     * @param array $couriers
     *
     * @return bool
     */
    private function specm_helper_price_format(&$couriers)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $priceHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data');
        foreach ($couriers as $courier_code => $courier) {
            $couriers[$courier_code]["price"] = $priceHelper->currency($couriers[$courier_code]["price"], true, false);
        }

        return true;
    }

    /**
     * @param int $order_id
     * @param string $courier_code
     * @param string $courier_name
     * @param string $tracking_number
     *
     * @return bool
     */
    public function specm_create_shipment_tracking($order_id, $courier_code, $courier_name, $tracking_number)
    {
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')
            ->load($order_id);

        if (!$order->canShip()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('You can\'t create an shipment.')
            );
        }

        $convertOrder = $this->_objectManager->create('Magento\Sales\Model\Convert\Order');
        $shipment = $convertOrder->toShipment($order);

        foreach ($order->getAllItems() as $orderItem) {
            if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                continue;
            }

            $qtyShipped = $orderItem->getQtyToShip();

            $shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);

            $shipment->addItem($shipmentItem);
        }

        $shipment->register();

        $data = [
            'carrier_code' => $courier_code,
            'title' => $courier_name,
            'number' => $tracking_number
        ];

        $shipment->getOrder()->setIsInProcess(false);

        try {
            $track = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment\TrackFactory')->create()->addData($data);
            $shipment->addTrack($track)->save();
            $shipment->save();
            $shipment->getOrder()->save();

            $this->_objectManager->create('Magento\Shipping\Model\ShipmentNotifier')
                ->notify($shipment);

            $shipment->save();
        } catch (\Exception $e) {
            $this->specm_writing_log($e->getMessage(), $e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }

        return true;
    }

    /**
     * @param string $message
     * @param array $exception
     *
     * @return bool
     */
    public function specm_writing_log($message = "Error message", $exception = [])
    {
        try {
            $this->logger->critical($message, ['exception' => $exception]);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param string $msg
     *
     * @return string
     */
    private function translate_error_code($msg = "")
    {
        $msg = strtoupper($msg);
        if ($msg === "OPTIONAL") {
            return "";
        }
        if (strpos($msg, 'MINIMUM') !== false && strpos($msg, 'ORDER') !== false) {
            $min = trim(str_replace(["MINIMUM", "ORDER"], "", $msg));
            $msg = __('MINIMUM %1 ORDER', $min);
        } else {
            $msg = __($msg);
        }
        // If ENG
        if (preg_match('/[^A-Za-z0-9]+/', $msg)) {
            return ucfirst(strtolower($msg));
        }
        return $msg;
    }

    /**
     * @param array $arr
     * @param string $col
     * @param string $dir
     *
     * @return void
     */
    private function specm_helper_array_sort_by_column(&$arr, $col, $dir = SORT_ASC)
    {
        $sort_col = [];
        foreach ($arr as $key => $row) {
            $sort_col[$key] = ($row["available"]) ? $row[$col] : 100000000000;
        }

        array_multisort($sort_col, $dir, $arr);
    }

    /**
     * @param string $full_path
     *
     * @return string
     */
    private function clearFileInFolder($full_path)
    {
        $paths = [];
        try {
            $paths =  $this->_file->readDirectory($full_path);

            foreach ($paths as $path) {
                if ($this->_file->isExists($path)) {
                    $this->_file->deleteFile($path);
                }
            }
        } catch (\Exception $e) {
            $this->specm_writing_log($e->getMessage(), $e);
            $paths = [];
        }

        return $paths;
    }

    /**
     * @param array $args
     *
     * @return void
     */
    private function fill_courier_args(&$args)
    {
        $kv = [
            'available' => false,
            'remark' => ""
        ];

        foreach ($kv as $key => $value) {
            if (empty($args[$key])) {
                $args[$key] = $value;
            }
        }
    }

    /**
     * @param string $strDate
     * @param bool $inline
     *
     * @return array
     */
    public function convert_dateThai($strDate, $inline = false)
    {
        if ($strDate == "0000-00-00 00:00:00") {
            if ($inline) {
                return "";
            } else {
                return [
                    'date' => "",
                    'time' => ""
                ];
            }
        }

        if ($strDate == "" || $strDate == null || empty($strDate)) {
            if ($inline) {
                return "";
            } else {
                return [
                    'date' => "",
                    'time' => ""
                ];
            }
        }
        $strYear = date("Y", strtotime($strDate)) + 543;
        $strMonth = date("n", strtotime($strDate));
        $strDay = date("j", strtotime($strDate));
        $strHour = date("H", strtotime($strDate));
        $strMinute = date("i", strtotime($strDate));
        $strSeconds = date("s", strtotime($strDate));
        $strMonthCut = ["", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."];
        $strMonthThai = $strMonthCut[$strMonth];

        if ($inline) {
            return "$strDay $strMonthThai $strYear $strHour:$strMinute:$strSeconds";
        } else {
            return [
                'date' => "$strDay $strMonthThai $strYear",
                'time' => "$strHour:$strMinute:$strSeconds"
            ];
        }
    }

    /**
     * @param array $street
     *
     * @return void
     */
    private function prepareStreet(&$street)
    {
        $street = implode(" ", explode("\n", $street));
    }

    /**
     * @param string $text
     * @param string $key
     * @param string $iv
     *
     * @return string
     */
    private function specm_encode($text, $key, $iv)
    {
        return base64_encode(openssl_encrypt($text, "aes-256-cbc", $key, 0, $iv));
    }
}
