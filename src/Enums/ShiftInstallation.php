<?php

namespace Aislandener\Telco\Enums;

enum ShiftInstallation: string
{
    case Morning = 'morning';

    case Afternoon = 'afternoon';

    public function apiInfo(): string
    {
        return self::getApiInfo($this);
    }

    private static function getApiInfo(ShiftInstallation $shiftInstallation): string
    {
        return match($shiftInstallation){
            self::Morning => 'MANHA',
            self::Afternoon => 'TARDE',

        };
    }

}
