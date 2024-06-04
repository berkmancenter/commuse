<?php

namespace App\Libraries;

/**
 * Cache Library
 *
 * This library provides methods for caching functionality.
 */
class Cache
{
  /**
   * Default cache expiration time in seconds (365 days).
   *
   * @var int
   */
  public static $defaultCacheExpiration = 86400 * 365;

  /**
   * Checks if caching is enabled.
   *
   * @return bool
   */
  public static function isCacheEnabled()
  {
    // Check if cache is disabled in the environment variable
    return !(isset($_ENV['cache.disabled']) && $_ENV['cache.disabled'] === 'true');
  }
}
