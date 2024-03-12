<?php

namespace App\Libraries;

class Cache {
  public static $defaultCacheExpiration = 86400*365;

  public static function isCacheEnabled() {
    // Check if cache is disabled in the environment variable
    return !(isset($_ENV['cache.disabled']) && $_ENV['cache.disabled'] === 'true');
  }
}
