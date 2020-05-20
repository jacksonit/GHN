# Laravel GHN
API GHN

-----
**Install with composer**.

Install (Laravel)
-----------------
Install via composer
```
composer require jacksonit/ghn
php artisan vendor:publish --provider="Jacksonit\GHN\GHNServiceProvider"
```

Get shipping Fee

```
Use GHNCharge;
$data = [
    'from_district_id'  => '',
    'to_district_id'    => '',
    'weight'            => '',
    'height'            => '',
    'length'            => '',
    'width'             => '',
]
GHNCharge::shippingFee($data);
```