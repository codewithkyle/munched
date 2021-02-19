<?php

namespace App\Facades;

use Log;

use Cloudflare\API\Auth\APIKey;
use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Endpoints\Zones;

class Cloudflare
{
    public static function Flush()
    {
        $key = new APIKey(getenv("CLOUDFLARE_EMAIL_ADDRESS"), getenv("CLOUDFLARE_API_KEY"));
        $adapter = new Guzzle($key);
        $zones = new Zones($adapter);
        foreach ($zones->listZones()->result as $zone) {
            $zones->cachePurgeEverything($zone->id);
        }
    }
}
