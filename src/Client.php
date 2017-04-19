<?php
namespace Yuanben;

use GuzzleHttp\Client as HttpClient;

use Yuanben\Contracts\Operable;
use Yuanben\Exceptions\InvalidInstanceException;
use Yuanben\Exceptions\PropertyNotExistException;

class Client
{
    protected $config;
    protected $apiBase = 'https://openapi.yuanben.io/';
    protected $httpClient;
    protected $baseVersion = 'v1';

    public function __construct(Config $config, $httpClient = null)
    {
        $this->config = $config;

        if (!$httpClient) {
            $this->httpClient = new HttpClient([
                'http_errors' => false
            ]);
        }
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setConfig($config)
    {
        if (is_array($config) && isset($config['token'])) {
            $this->config = new Config($config['token']);
        } elseif ($config instanceof Config) {
            $this->config = $config;
        } else {
            throw new InvalidInstanceException('The $config argument must be an instance of Yuanben\Config.');
        }

        return $this;
    }

    public function setHttpClient($client)
    {
        $this->httpClient = $client;
    }

    public function getHttpClient()
    {
        return $this->httpClient;
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->{$property};
        }

        throw new PropertyNotExistException("The property {$property} not exist on Client.");
    }

    public function post(Operable $resources)
    {
        $field = $resources->getField();
        $path = $this->getPath($field);
        $data = $resources->toArray();

        if (! $resources instanceof Collection) {
            $data = [$data];
        }

        return $this->httpClient->post($path, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->config->getToken()
            ],
            'json' => [$field => $data]
        ]);

    }

    public function getPath($field)
    {
        return $this->apiBase . $this->baseVersion . '/' . $this->config->getPrefix() . $field;
    }
}
