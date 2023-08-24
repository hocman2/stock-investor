<?php
namespace App\Service;

use \DateInterval;

class UpdateInterval
{
    static function getUpdateInterval()
    {
        // We recreate a new DateInterval at each call because it's an environment variable and
        // we want to be able to update it dynamically without redeploying
        return new DateInterval($_ENV["UPDATE_INTERVAL"]);
    }
}

?>