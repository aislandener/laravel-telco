<?php

namespace Aislandener\Telco\Facades;

use Illuminate\Support\Facades\Facade;
use Aislandener\Telco\Services as Services;

/**
 * @method static Services\AddressService address()
 * @method static Services\CommercialService commercial()
 * @method static Services\ClientService client()
 * @method static Services\OmnichannelService omnichannel()
 * @method static Services\TelephonyService telephony()
 */
class Telco extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'Telco';
    }

}