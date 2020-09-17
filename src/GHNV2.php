<?php

namespace Jacksonit\GHN;

use Validator;
use GuzzleHttp\Client;


class GHNV2
{
    public $url             = '';
    public $token           = '';
    public $client_hub_id   = '';
    public $shop_id         = '';

    public $service_id = [
        53319 => 1,
        53320 => 2,
        53330 => 3,
        53321 => 4
    ];

    /**
     * Create new
     *
     * @return void
     */
    public function __construct()
    {
        $this->url              = config('ghn.url');
        $this->token            = config('ghn.token');
        $this->client_hub_id    = config('ghn.client_hub_id');
        $this->shop_id          = config('ghn.shop_id');
    }

    public function service($data)
    {
        try
        {
            $validator = Validator::make($data, [
                'from_district'  => 'required',
                'to_district'    => 'required'
            ]);

            if ($validator->fails()){
                throw new \Exception($validator->errors()->first());
            }

            $client = new Client([
                'headers' => [ 'Content-Type' => 'application/json', 'token' => $this->token ]
            ]);
            $response = $client->post(str_replace('/v2', '', $this->url) . '/pack-service/all', [
                'body' => json_encode([
                    'from_district'  => (int) $data['from_district'],
                    'to_district'    => (int) $data['to_district']
                ])
            ]);

            $records = json_decode($response->getBody()->getContents());
          
            if(empty($records) || $records->code != 200 || $records->message != 'Success') throw new \Exception('GHN Error');

            return ['result'=> 'OK', 'records' => $records->data];
        } catch (\Exception $e) {
            return ['result'=> 'NG', 'message' => $e->getMessage()];
        }
    }

    /**
     *
     * @param array $input
     * @return Response
     */
    public function shippingFee($data)
    {
        try
        {
            $validator = Validator::make($data, [
                'from_district_id'  => 'required',
                'from_ward_code'    => 'required',
                'to_district_id'    => 'required',
                'to_ward_code'      => 'required',
                'weight'            => 'required',
                'height'            => 'required',
                'length'            => 'required',
                'width'             => 'required',
                'coupon'            => 'nullable|string',
            ]);

            if ($validator->fails()){
                throw new \Exception($validator->errors()->first());
            }

            $client = new Client([
                'headers' => [ 'Content-Type' => 'application/json', 'token' => $this->token ]
            ]);
            $response = $client->post($this->url . '/shipping-order/fee', [
                'body' => json_encode([
                    'shop_id'           => $this->shop_id,
                    'service_id'        => null,
                    'service_type_id'   => 2,
                    'from_district_id'  => (int) $data['from_district_id'],
                    'from_ward_code'    => (int) $data['from_ward_code'],
                    'to_district_id'    => (int) $data['to_district_id'],
                    'to_ward_code'      => (int) $data['to_ward_code'],
                    'weight'            => (int) $data['weight'],
                    'height'            => (int) $data['height'],
                    'length'            => (int) $data['length'],
                    'width'             => (int) $data['width'],
                    'coupon'            => (string) $data['coupon']
                ])
            ]);

            $records = json_decode($response->getBody()->getContents());

            if(empty($records) || $records->code != 200 || $records->message != 'Success') throw new \Exception('GHN Error');

            return ['result'=> 'OK', 'records' => ['total' => $records->data->total, 'service_fee' => $records->data->service_fee, 'coupon_value' => $value->data->coupon_value]];

            return ['result'=> 'NG', 'message' => 'Không tìm thấy giá vận chuyển trên GHN'];
        } catch (\Exception $e) {
            return ['result'=> 'NG', 'message' => $e->getMessage()];
        }
    }

    public function createOrder($data)
    {
        try
        {
            $validator = Validator::make($data, [
                'to_name'               => 'required|string',
                'to_phone'              => 'required|string',
                'to_address'            => 'required|string',
                'to_ward_code'          => 'nullable|string',
                'to_district_id'        => 'required',
                'return_phone'          => 'required|string',
                'return_address'        => 'required|string',
                'return_district_id'    => 'required',
                'return_ward_code'      => 'nullable|string',
                'client_order_code'     => 'required',
                'cod_amount'            => 'required',
                'weight'                => 'required',
                'height'                => 'required',
                'length'                => 'required',
                'width'                 => 'required',
                'coupon'                => 'nullable|string',
                'items'                 => 'required|array'
            ]);
                
            if ($validator->fails()){
                throw new \Exception($validator->errors()->first());
            }

            $client = new Client([
                'headers' => [ 'Content-Type' => 'application/json', 'token' => $this->token ]
            ]);

            $data = [
                'shop_id'               => $this->shop_id,

                "from_name"             => (string) $data['from_name'],
                "from_phone"            => (string) $data['from_phone'],
                "from_address"          => (string) $data['from_address'],
                "from_ward_code"        => (string) $data['from_ward_code'],
                "from_district_id"      => (int) $data['from_district_id'],

                "to_name"               => (string) $data['to_name'],
                "to_phone"              => (string) $data['to_phone'],
                "to_address"            => (string) $data['to_address'],
                "to_ward_code"          => (string) $data['to_ward_code'],
                "to_district_id"        => (int) $data['to_district_id'],

                "return_phone"          => (string) $data['return_phone'],
                "return_address"        => (string) $data['return_address'],
                "return_district_id"    => (int) $data['return_district_id'],
                "return_ward_code"      => (string) $data['return_ward_code'],
                "client_order_code"     => (string) $data['client_order_code'],
                "cod_amount"            => (int) $data['cod_amount'],
                "content"               => "",
                "weight"                => (int) $data['weight'],
                "length"                => (int) $data['length'],
                "width"                 => (int) $data['width'],
                "height"                => (int) $data['height'],
                "insurance_value"       => 0,
                "coupon"                => (string) $data['coupon'],
                "service_type_id"       => 2,
                "payment_type_id"       => 1,
                "note"                  => "Vui lòng gọi điện trước khi giao hàng",
                "required_note"         => "KHONGCHOXEMHANG",
                "items"                 => (array) $data['items']
            ];
            
            $response = $client->post($this->url . '/shipping-order/create', ['body' => json_encode($data)]);

            $data = json_decode($response->getBody(), true);
            
            if(empty($data['code']) || $data['code'] != 200 || $data['message'] != 'Success') throw new \Exception("Error", 1);
            
            return $data['data'];
        } catch (\Exception $e) {
            return ['result'=> 'NG', 'message' => $e->getMessage()];
        }
    }

    public function cancelOrder($data)
    {
        try
        {
            $validator = Validator::make($data, [
                'order_code'  => 'required'
            ]);

            if ($validator->fails()){
                throw new \Exception($validator->errors()->first());
            }

            $client = new Client([
                'headers' => [ 'Content-Type' => 'application/json', 'token' => $this->token ]
            ]);

            $data = [
                'shop_id'         => $this->shop_id,
                "order_codes"     => (string) $data['order_code']
            ];

            $response = $client->post($this->url . '/switch-status/cancel', ['body' => json_encode($data)]);
            $data = json_decode($response->getBody(), true);

            if($data['code'] != 200) throw new \Exception($data['message'], 1);

            return ['result'=> 'OK', 'message' => 'Success'];
        } catch (\Exception $e) {
            return ['result'=> 'NG', 'message' => $e->getMessage()];
        }
    }
}