<?php

namespace Aislandener\Telco\Contracts;

use Illuminate\Support\Collection;

interface TelcoParams
{
    public function commitContractToClient(): array;

    public function commitPromoExists(array $data): array;

    public function getTechnologyId(): string;

    public function getInfoServer(int $technologyId = 4): Collection;
}
