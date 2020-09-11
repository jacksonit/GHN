<?php

namespace Jacksonit\GHN\Facades;

use Illuminate\Support\Facades\Facade;

class GHNV1 extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'GHNV1';
    }
}