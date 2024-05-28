<?php

namespace Cloudstudio\Ollama\Traits;

use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;

trait MakesHttpRequests
{
    protected null|string|array $clientAuth = null;

    /**
     * Sends an HTTP request to the API and returns the response.
     *
     * @param string $urlSuffix
     * @param array $data
     * @param string $method (optional)
     * @return array
     */
    protected function sendRequest(string $urlSuffix, array $data, string $method = 'post')
    {
        if($this instanceof ModelService){
            $url = $this->baseUrl . $urlSuffix;
        }else{
            $url = $this->modelService->baseUrl . $urlSuffix;
        }

        if (!empty($data['stream']) && $data['stream'] === true) {
            $client = new Client();
            $response = $client->request($method, $url, [
                'auth'    => $this->clientAuth,
                'json'    => $data,
                'stream'  => true,
                'timeout' => config('ollama-laravel.connection.timeout'),
            ]);

            return $response;
        } else {
            if (is_array($this->clientAuth)) {
                $response = Http::timeout(config('ollama-laravel.connection.timeout'))
                    ->withBasicAuth(...$this->clientAuth)
                    ->$method($url, $data);
            } elseif ($this->clientAuth) {
                $response = Http::timeout(config('ollama-laravel.connection.timeout'))
                    ->withHeader('Authorization', 'Bearer ' . $this->clientAuth)
                    ->$method($url, $data);
            } else {
                $response = Http::timeout(config('ollama-laravel.connection.timeout'))
                    ->$method($url, $data);
            }

            return $response->json();
        }
    }

    public function setClientAuth(string|array $auth)
    {
        $this->clientAuth = $auth;
        return $this;
    }

    public function getClientAuth()
    {
        return $this->clientAuth;
    }
}
