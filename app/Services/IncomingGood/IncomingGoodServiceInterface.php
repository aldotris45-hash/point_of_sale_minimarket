<?php

namespace App\Services\IncomingGood;

use App\Models\IncomingGood;

interface IncomingGoodServiceInterface
{
    public function create(array $data): IncomingGood;

    public function updateDate(IncomingGood $incomingGood, string $newDate): IncomingGood;

    public function delete(IncomingGood $incomingGood): void;
}
