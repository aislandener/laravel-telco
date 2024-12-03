<?php

namespace Aislandener\Telco\Enums;

enum TypeBilling: string
{
    case Digital = 'DIGITAL';

    case Slip = 'CARNE';

    case Debit = 'DEBITO';

    case PrePaid = 'PREPAGO';

    case Ticket = 'BOLETO';

}
