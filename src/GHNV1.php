<?php

namespace Jacksonit\GHN;

use Validator;
use GuzzleHttp\Client;


class GHNV1
{
    public $url             = '';
    public $token           = '';
    public $client_hub_id   = '';

    public $service_id = [
        53320 => 1,
        53321 => 2,
        53322 => 3,
        53324 => 4
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
    }

    /**
     *
     * @param array $input
     * @return Response
     */
    public function ShippingFee($data)
    {
        try
        {
            $validator = Validator::make($data, [
                'from_district_id'  => 'required',
                'to_district_id'    => 'required',
                'weight'            => 'required',
                'height'            => 'required',
                'length'            => 'required',
                'width'             => 'required'
            ]);

            if ($validator->fails()){
                throw new \Exception($validator->errors()->first());
            }

            $client = new Client([
                'headers' => [ 'Content-Type' => 'application/json' ]
            ]);

            $response = $client->post($this->url . '/FindAvailableServices', [
                'body' => json_encode([
                    'token'             => $this->token,
                    'FromDistrictID'    => (int) $data['from_district_id'],
                    'ToDistrictID'      => (int) $data['to_district_id'],
                    'Weight'            => (int) $data['weight'],
                    'Height'            => (int) $data['height'],
                    'Length'            => (int) $data['length'],
                    'Width'             => (int) $data['width']
                ])
            ]);

            $records_ghn = json_decode($response->getBody()->getContents());

            if(empty($records_ghn) || $records_ghn->msg != 'Success') throw new \Exception('GHN Error');

            foreach ($records_ghn->data as $value) {
                if ($value->Name == 'Chuẩn') {
                    return ['result'=> 'OK', 'records' => ['service_fee' => $value->ServiceFee, 'service_id' => $value->ServiceID]];
                }
            }

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
                'from_district_id'  => 'required',
                'to_district_id'    => 'required',
                'weight'            => 'required',
                'height'            => 'required',
                'length'            => 'required',
                'width'             => 'required',
                'note'              => 'required',
                'external_code'             => 'required',
                'client_contact_name'       => 'required',
                'client_contact_phone'      => 'required',
                'client_address'            => 'required',
                'customer_name'             => 'required',
                'customer_phone'            => 'required',
                'shipping_address'          => 'required',
                'cod_amount'                => 'required',
                'shipping_order_costs'      => 'required|array',
                'service_id'                => 'required',
            ]);

            if ($validator->fails()){
                throw new \Exception($validator->errors()->first());
            }

            $client = new Client([
                'headers' => [ 'Content-Type' => 'application/json' ]
            ]);

            $data = [
                'token'                 => $this->token,
                "PaymentTypeID"         => 1,
                "FromDistrictID"        => (int) $data['from_district_id'],
                "ToDistrictID"          => (int) $data['to_district_id'],
                "Note"                  => $data['note'],
                "SealCode"              => "tem niêm phong",
                "ExternalCode"          => (string) $data['external_code'],
                "ClientContactName"     => (string) $data['client_contact_name'],
                "ClientContactPhone"    => (string) $data['client_contact_phone'],
                "ClientAddress"         => (string) $data['client_address'],
                "CustomerName"          => (string) $data['customer_name'],
                "CustomerPhone"         => (string) $data['customer_phone'],
                "ShippingAddress"       => (string) $data['shipping_address'],
                "CoDAmount"             => $data['cod_amount'],
                "NoteCode"              =>"CHOXEMHANGKHONGTHU",
                "InsuranceFee"          => 0,
                "ClientHubID"           => (int) $this->client_hub_id,
                "ShippingOrderCosts"    => $data['shipping_order_costs'],
                "ServiceID"             => (int) $data['service_id'],
                "Content"               => "",
                "CouponCode"            => "",
                "Weight"                => (int) $data['weight'],
                "Length"                => (int) $data['length'],
                "Width"                 => (int) $data['width'],
                "Height"                => (int) $data['height'],
                "CheckMainBankAccount"  => false,
                "ExternalReturnCode"    => "",
                "IsCreditCreate"        => true
            ];
            $response = $client->post($this->url . '/CreateOrder', ['body' => json_encode($data)]);

            $data = json_decode($response->getBody(), true);
            if(empty($data['msg']) || $data['msg'] != 'Success') throw new \Exception("Error", 1);

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
                'headers' => [ 'Content-Type' => 'application/json' ]
            ]);

            $data = [
                'token'         => $this->token,
                "OrderCode"     => (string) $data['order_code']
            ];
            $response = $client->post($this->url . '/CancelOrder', ['body' => json_encode($data)]);
            $data = json_decode($response->getBody(), true);
            if(!isset($data['code'])) throw new \Exception("Error", 1);

            if($data['code'] == 0) throw new \Exception($data['msg'], 1);

            return ['result'=> 'OK', 'message' => 'Success'];
        } catch (\Exception $e) {
            return ['result'=> 'NG', 'message' => $e->getMessage()];
        }
    }
}