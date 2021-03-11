<?php

namespace Shippop\Ecommerce\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class ShippopApi extends AbstractHelper
{
    protected $config;
    protected $curl;
    protected $urlBuilder;

    public function __construct(
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\UrlInterface $urlBuilder,
        Config $config
    ) {
        $this->curl = $curl;
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param string $route
     * @param string $shippop_server
     *
     * @return array
     */
    public function environment($route = "", $shippop_server = "")
    {
        $is_sandbox = false;
        if (strtoupper($shippop_server) === "TH") {
            $server = [
                'dev' => 'https://mkpservice.shippop.dev',
                'prd' => 'https://mkpservice.shippop.com',
            ];
        } else {
            $server = [
                'dev' => 'https://mkpservice.mhoopay.com',
                'prd' => 'https://mkpservice.shippop.my',
            ];
        }

        if (!empty($is_sandbox) && $is_sandbox) {
            $endpoint = $server['dev'];
        } else {
            $endpoint = $server['prd'];
        }

        if (empty($route)) {
            return $endpoint;
        } else {
            return $endpoint . "/" . $route . "/";
        }
    }

    /**
     * @return Curl
     */
    public function getCurlClient()
    {
        return $this->curl;
    }

    /**
     * @param mixed $route
     * @param mixed $postData
     * @param string $shippop_server
     *
     * @return array
     */
    private function post($route, $postData, $shippop_server = "")
    {
        if ($shippop_server == "") {
            $shippop_server = $this->config->getShippopConfig("auth", "shippop_server");
        }
        
        if (empty($shippop_server)) {
            return false;
        }
        $endpoint = $this->environment($route, $shippop_server);
        if ($endpoint === false) {
            return [
                'status' => false,
                'message' => __("Can't connect. Please try again later")
            ];
        }
        return $this->cpost($endpoint, $postData);
    }

    /**
     * @param mixed $postData
     * @param mixed $shippop_server
     *
     * @return array
     */
    public function authBearer($postData, $shippop_server)
    {
        return $this->post("auth/login", $postData, $shippop_server);
    }

    /**
     * @param mixed $postData
     * @param mixed $shippop_server
     *
     * @return array
     */
    public function authRegister($postData, $shippop_server)
    {
        return $this->post("register/wordpress", $postData, $shippop_server);
    }

    /**
     * @return array
     */
    public function member()
    {
        return $this->post("member", []);
    }

    /**
     * @param mixed $postData
     *
     * @return array
     */
    public function billingUpdate($postData)
    {
        return $this->post("billing/update", $postData);
    }

    /**
     * @param mixed $data
     *
     * @return array
     */
    public function pricelist($data)
    {
        $postData = [
            'data' => $data
        ];
        return $this->post("pricelist", $postData);
    }

    /**
     * @param mixed $data
     *
     * @return array
     */
    public function booking($data)
    {
        $postData = [
            'data' => $data,
            'url' => [
                'success' => $this->urlBuilder->getUrl("shippop/ecommerce/choosecourier&success"),
                'fail' => $this->urlBuilder->getUrl("shippop/ecommerce/choosecourier&fail")
            ]
        ];
        return $this->post("booking", $postData);
    }

    /**
     * @param mixed $purchase_id
     *
     * @return array
     */
    public function confirm($purchase_id)
    {
        $postData = [
            'purchase_id' => $purchase_id
        ];
        return $this->post("confirm", $postData);
    }

    /**
     * @param mixed $tracking_code
     * @param mixed $size
     * @param mixed $type
     *
     * @return array
     */
    public function labelPrinting($tracking_code, $size, $type)
    {
        $postData = [
            'tracking_code' => implode(",", $tracking_code),
            'size' => $size,
            'type' => $type
        ];
        return $this->post("label_tracking_code", $postData);
    }

    /**
     * @param mixed $tracking_code
     *
     * @return array
     */
    public function getTrackingOrder($tracking_code)
    {
        $postData = [
            'tracking_code' => $tracking_code,
        ];
        return $this->post("tracking", $postData);
    }

    /**
     * @param mixed $tracking_code
     *
     * @return array
     */
    public function purchaseCancel($tracking_code)
    {
        $postData = [
            'tracking_code' => $tracking_code
        ];
        return $this->post("cancel", $postData);
    }

    /**
     * @param mixed $start_date
     * @param mixed $end_date
     *
     * @return array
     */
    public function reportDelivered($start_date, $end_date)
    {
        $postData = [
            'start_date' => $start_date,
            'end_date' => $end_date
        ];
        return $this->post("report-delivered", $postData);
    }

    /**
     * @param mixed $start_date
     * @param mixed $end_date
     * @param mixed $filter_date
     *
     * @return array
     */
    public function reportCOD($start_date, $end_date, $filter_date)
    {
        $postData = [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'filter_date' => $filter_date
        ];
        return $this->post("report-cod", $postData);
    }

    /**
     * @param mixed $text
     *
     * @return array
     */
    public function prepareAddress($text)
    {
        $postData = [
            "inputText" => $text
        ];

        $address = $this->cpost("https://www.shippop.com/address/collection/", $postData, 'application/x-www-form-urlencoded');
        if ( $address['status'] ) {
            return $address['address'];
        }

        return $address;
    }

    /**
     * @param mixed $endpoint
     * @param mixed $postData
     *
     * @return array
     */
    private function cpost($endpoint, $postData, $conTentType = "application/json")
    {
        $curl = curl_init();
        $headers = [];
        $headers[] = "Content-Type: " . $conTentType;
        $shippop_bearer_key = $this->config->getShippopConfig("auth", "shippop_bearer_key");
        if (!empty($shippop_bearer_key)) {
            $headers[] = "Authorization: Bearer " . $shippop_bearer_key;
        }

        $_postData = ( $conTentType == "application/json" ) ? json_encode($postData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : http_build_query( $postData );
        curl_setopt_array($curl, [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $_postData,
            CURLOPT_HTTPHEADER => $headers
        ]);

        $response = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (curl_errno($curl)) {
            return ['status' => false, 'message' => curl_error($curl)];
        } else {
            if ($http_status == 200) {
                try {
                    $body = json_decode($response, true);
                    if (empty($body["status"])) {
                        $body["status"] = false;
                    }
                    if (empty($body["message"])) {
                        $body["message"] = "-";
                    }
                } catch (\Exception $e) {
                    $this->_utility->specm_writing_log($e->getMessage(), $e);
                    return ['status' => false, 'message' => $e->getMessage()];
                }

                return $body;
            } else {
                return ['status' => false, 'message' => $response];
            }
        }
        curl_close($curl);

        return ['status' => false, 'message' => $response];
    }
}
