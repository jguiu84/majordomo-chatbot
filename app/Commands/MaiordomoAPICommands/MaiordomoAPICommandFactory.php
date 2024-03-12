<?php

namespace App\Commands\MaiordomoAPICommands;

class MaiordomoAPICommandFactory
{
    public function createCommand(string $commandName) : ?MaiordomoAPICommand
    {
        if($commandName == null) {
            return null;
        }

        return null;
    }

}


