<?php

namespace YllyCertSign\Client\Sign;

use YllyCertSign\Client\AbstractClient;
use YllyCertSign\Exception\NotFoundEnvironnementException;

class SignClient extends AbstractClient implements SignClientInterface
{
    /**
     * @var string
     */
    private $environnement;

    /**
     * @var string
     */
    private $certPath;

    /**
     * @var string
     */
    private $certPassword;

    /**
     * @var null|string
     */
    private $proxy;

    /**
     * @var array
     */
    private $endPoints = [
        'prod' => 'https://sign.certeurope.fr',
        'test' => 'https://sign-sandbox.certeurope.fr'
    ];

    /**
     * @param string $environnement
     * @param string $certPath
     * @param string $certPassword
     * @param string|null $proxy
     * @throws NotFoundEnvironnementException
     */
    public function __construct($environnement, $certPath, $certPassword, $proxy)
    {
        $this->environnement = $environnement;
        if (!isset($this->endPoints[$this->environnement])) {
            throw new NotFoundEnvironnementException('Environnement not found');
        }

        $this->certPath = $certPath;
        $this->certPassword = $certPassword;
        $this->proxy = $proxy;
    }

    /**
     * @return string
     */
    private function getEndpoint()
    {
        return $this->endPoints[$this->environnement];
    }

    /**
     * @param string $url
     * @param string|null $method
     * @return resource
     */
    private function createRequest($url, $method = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->getEndpoint() . $url);
        curl_setopt($curl, CURLOPT_SSLCERT, $this->certPath);
        curl_setopt($curl, CURLOPT_SSLCERTPASSWD, $this->certPassword);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if ($method !== null) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        }

        if ($this->proxy !== null) {
            curl_setopt($curl, CURLOPT_PROXY, explode(':', $this->proxy)[0]);
            curl_setopt($curl, CURLOPT_PROXYPORT, explode(':', $this->proxy)[1]);
        }

        $this->writeLog(self::INFO, sprintf(
            '[%s] %s',
            $method !== null ? $method : 'GET',
            $this->getEndpoint() . $url
        ));

        return $curl;
    }

    /**
     * @param resource $curl
     * @return object
     */
    private function getResponse($curl)
    {
        $response = curl_exec($curl);
        curl_close($curl);

        $this->writeLog(self::INFO, sprintf('Response : %s', $this->sanitizeResponse($response)));

        return $response;
    }

    /**
     * @param object $response
     * @return string
     */
    private function sanitizeResponse($response)
    {
        $responseObject = json_decode($response);

        $documentsTags = ['toSignContent', 'signedContent'];
        foreach ($documentsTags as $documentsTag) {
            if (isset($responseObject->$documentsTag)) {
                $responseObject->$documentsTag = '...';
            }
        }

        return json_encode($responseObject);
    }

    /**
     * @param string $url
     * @return object
     */
    public function get($url)
    {
        $curl = $this->createRequest($url);
        $response = $this->getResponse($curl);

        return json_decode($response);
    }

    /**
     * @param string $url
     * @param array $content
     * @return object
     */
    public function post($url, $content = [])
    {
        $data = json_encode($content);

        $curl = $this->createRequest($url, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Content-Length: ' . strlen($data)]);

        $response = $this->getResponse($curl);

        return json_decode($response);
    }
}
