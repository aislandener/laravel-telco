<?php

namespace Aislandener\Telco\Facades;

use Aislandener\Telco\Services as Services;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Services\AddressService address()
 * @method static Services\CommercialService commercial()
 * @method static Services\ClientService client()
 * @method static Services\OmnichannelService omnichannel()
 * @method static Services\TelephonyService telephony()
 * @method static Services\FinancialService financial()
 * @method static Services\ClientAppService clientApp()
 * @method static Services\CardService card()
 */
class Telco extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Telco';
    }
}
