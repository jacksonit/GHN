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

            $response = $client->post($url, [
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
                    if(empty($data['result'])) {
                        return ['result'=> 'OK', 'records' => ['service_fee' => $value->ServiceFee]];
                    } else {
                        if($data['result'] == 'ServiceFee') {
                            return ['result'=> 'OK', 'records' => ['service_fee' => $value->ServiceFee]];
                        } else if($data['result'] == 'ALL') {
                            return ['result'=> 'OK', 'records' => ['service_fee' => $value->ServiceFee, 'service_id' => $value->ServiceID]];
                        } else {
                            return ['result'=> 'OK', 'records' => ['service_id' => $value->ServiceID]];
                        }
                    }
                }
            }

            return ['result'=> 'NG', 'message' => 'Không tìm thấy giá vận chuyển trên GHN'];
        } catch (\Exception $e) {
            return ['result'=> 'NG', 'message' => $e->getMessage()];
        }
    }
}