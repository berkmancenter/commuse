<?php

namespace Config;

/**
 * --------------------------------------------------------------------------
 * Cross-Origin Resource Sharing (CORS) Configuration
 * --------------------------------------------------------------------------
 *
 * Here you may configure your settings for cross-origin resource sharing
 * or "CORS". This determines what cross-origin operations may execute
 * in web browsers. You are free to adjust these settings as needed.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
 */
class Cors extends \Fluent\Cors\Config\Cors
{
    /**
     * --------------------------------------------------------------------------
     * Allowed HTTP headers
     * --------------------------------------------------------------------------
     *
     * Indicates which HTTP headers are allowed.
     *
     * @var array
     */
    public $allowedHeaders = [];

    /**
     * --------------------------------------------------------------------------
     * Allowed HTTP methods
     * --------------------------------------------------------------------------
     *
     * Indicates which HTTP methods are allowed.
     *
     * @var array
     */
    public $allowedMethods = [];

    /**
     * --------------------------------------------------------------------------
     * Allowed request origins
     * --------------------------------------------------------------------------
     *
     * Indicates which origins are allowed to perform requests.
     * Patterns also accepted, for example *.foo.com
     *
     * @var array
     */
    public $allowedOrigins = [];

    public function __construct() {
        parent::__construct();

        $this->allowedHeaders = (explode(',', $_ENV['cors.allowedHeaders'] ?? ''));
        $this->allowedMethods = (explode(',', $_ENV['cors.allowedMethods'] ?? ''));
        $this->allowedOrigins = (explode(',', $_ENV['cors.allowedOrigins'] ?? ''));
    }
}
