<?php

namespace App\Commands\MaiordomoAPICommands;

class MaiordomoAPICommandFactory
{
    public function createCommand(string $commandName) : ?MaiordomoAPICommand
    {
        if($commandName == null) {
            return null;
        }

        return match ($commandName) {
            "get_reserve", "GetReservation" => new GetReservation(),
            default => null,
        };
    }

}


