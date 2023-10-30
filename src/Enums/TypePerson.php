<?php

namespace Aislandener\Telco\Enums;

use App\Enums\TargetAudience;

enum TypePerson: string
{
    case Personal = 'personal';
    case Business = 'business';

    public function apiName(): string
    {
        return self::getApiName($this);
    }

    public static function getApiName(TypePerson $value): string
    {
        return match ($value){
            self::Personal => 'PessoaFisica',
            self::Business => 'PessoaJuridica'
        };
    }
}