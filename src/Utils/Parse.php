<?php

namespace Fiks\MureDiscord\Utils;

trait Parse
{
    public function parse(string $commandFromList, array $params)
    {
        $command = explode(' ', $commandFromList)[0];

        if($command)

        preg_match_all('/\{(.+?)\}/m', $commandFromList, $paramFromCommand, 2, 0);

        print_r($paramFromCommand);
    }
}