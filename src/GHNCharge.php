<?php

namespace Jacksonit\GHN;

use Validator;
use GuzzleHttp\Client;


class GHNCharge
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
    public function shippingFee($data)
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

            $response = $client->post($this->url, [
                'body' => json_encode([
                    'token'             => $this->token,
                    'FromDistrictID'    => $data['from_district_id'],
                    'ToDistrictID'      => $data['to_district_id'],
                    'Weight'            => $data['weight'],
                    'Height'            => $data['height'],
                    'Length'            => $data['length'],
                    'Width'             => $data['width']
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
                'shipping_order_costs'      => 'required',
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
                "FromDistrictID"        => $data['from_district_id'],
                "ToDistrictID"          => $data['to_district_id'],
                "Note"                  => $data['note'],
                "SealCode"              => "tem niêm phong",
                "ExternalCode"          => $data['external_code'],
                "ClientContactName"     => $data['client_contact_name'],
                "ClientContactPhone"    => $data['client_contact_phone'],
                "ClientAddress"         => $data['client_address'],
                "CustomerName"          => $data['customer_name'],
                "CustomerPhone"         => $data['customer_phone'],
                "ShippingAddress"       => $data['shipping_address'],
                "CoDAmount"             => $data['cod_amount'],
                "NoteCode"              =>"CHOXEMHANGKHONGTHU",
                "InsuranceFee"          => 0,
                "ClientHubID"           => $this->client_hub_id,
                "ShippingOrderCosts"    => $data['shipping_order_costs'],
                "ServiceID"             => $data['service_id'],
                "Content"               => "",
                "CouponCode"            => "",
                "Weight"                => $data['weight'],
                "Length"                => $data['length'],
                "Width"                 => $data['width'],
                "Height"                => $data['height'],
                "CheckMainBankAccount"  => false,
                "ExternalReturnCode"    => "",
                "IsCreditCreate"        => true
            ];

            $response = $client->post($this->url, ['body' => json_encode($data)]);

            $data = json_decode($response->getBody(), true);
            return $data;
        } catch (\Exception $e) {
            return ['result'=> 'NG', 'message' => $e->getMessage()];
        }
    }
}