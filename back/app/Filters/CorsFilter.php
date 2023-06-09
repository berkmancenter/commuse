<?php

namespace App\Filters;

use Fluent\Cors\Config\Services;

class CorsFilter extends \Fluent\Cors\Filters\CorsFilter
{
    /**
     * @var \Config\Cors $cors
     */
    protected $cors;

    /**
     * Constructor.
     *
     * @param array $options
     * @return void
     */
    public function __construct()
    {
        $config = config('Cors');
        $this->cors = Services::cors($config);
    }
}
