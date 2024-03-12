<?php

namespace App\Commands\MaiordomoAPICommands;

use App\Commands\MaiordomoAPICommands\MaiordomoAPICommand;

class GetReservation extends MaiordomoAPICommand
{
    public function __construct()
    {
        $this->apiPath = "/reserve";
        $this->httpMethod = "get";
    }

}
