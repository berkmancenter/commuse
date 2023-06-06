<?php

use GuzzleHttp\Client;

class DrupalNewsProvider implements NewsProviderInterface
{
    protected $baseUrl;
    protected $client;

    public function __construct()
    {
        // Drupal JSON:API base URL
        $this->baseUrl = 'https://example.com/jsonapi/';

        // Create a GuzzleHttp client
        $this->client = new Client();
    }

    public function getContentItems()
    {
        $url = $this->baseUrl . 'node/article';

        try {
            // Send a GET request to Drupal JSON:API
            $response = $this->client->get($url);

            // Get the response body
            $body = $response->getBody();

            // Convert the JSON response to an array
            $contentItems = json_decode($body, true);

            // Return the list of content items
            return $contentItems['data'];
        } catch (Exception $e) {
            // Handle any exceptions or error responses
            log_message('error', 'Error retrieving content items from Drupal JSON:API: ' . $e->getMessage());
            return [];
        }
    }
}
