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
 * @method static Services\FinancialService financial()
 * @method static Services\ClientAppService clientApp()
 */
class Telco extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'Telco';
    }

}
