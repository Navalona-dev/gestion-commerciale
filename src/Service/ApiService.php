<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ApiService
{
    const CONTENT_TYPE_FORM_DATA = "multipart/form-data";
    const CONTENT_TYPE_JSON = "application/json";

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;

    }

    public function getHeaders () {
       $headers = [
            'Content-Type' => $this::CONTENT_TYPE_JSON
       ];
    }

    public function getOptions ($baseUri = "", $headers = [], $token = null,$body = null, $isAuthBearer = true, $isAuthBasic = false) 
    {
        $options = [];
        $options['base_uri'] = $baseUri;
        $options['headers'] = $headers;
        if ($isAuthBearer) {
            $options['auth_bearer'] = $token;
        }
        if ($isAuthBasic) {
            $options['auth_basic'] = $authBasic;
        }
        return $options;
    }

    public function post ($url, $data, $token = null, $isAuthBearer = true,$isAuthBasic = false  ) 
    {
        $options = $this->getOptions($url, $this->getHeaders(),$token, $data, $isAuthBearer, $isAuthBasic);
        $options['body'] = $body;
        return $this->client->request('POST',$url, $options);
    }

    public function get ($url, $token = null, $isAuthBearer = true,$isAuthBasic = false  ) 
    {
        $options = $this->getOptions($url, $this->getHeaders(),$token, null, $isAuthBearer, $isAuthBasic);
        return $this->client->request('GET',$url, $options);
    }

    public function put ($url, $data, $token = null, $isAuthBearer = true,$isAuthBasic = false  ) 
    {
        $options = $this->getOptions($url, $this->getHeaders(),$token, $data, $isAuthBearer, $isAuthBasic);
        $options['body'] = $body;
        return $this->client->request('PUT',$url, $options);
    }

}
