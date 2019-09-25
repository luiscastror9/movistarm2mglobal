<?php

namespace BionConnection\MovistarM2Mglobal\Facades;

use Illuminate\Support\Facades\Facade;

class MovistarM2Mglobal extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    
    protected static function getFacadeAccessor()
    {
        return 'MovistarM2Mglobal';
    }
}
