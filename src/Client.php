<?php
/**
 * @author  Rizart Dokollari
 * @since   12/24/17
 */

namespace ElasticEmail;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;


/** HTTP client: sets correct base URI & api key and other middlewares. */
class Client extends \GuzzleHttp\Client
{
    static $baseUri = 'https://api.elasticemail.com/v2/';

    public function __construct(
        string $apiKey,
        array $middlewares = [],
        $handler = null
    )
    {
        if (empty($apiKey)) {
            throw new ElasticEmailException('ElasticEmail API key is missing.');
        }

        parent::__construct([
            'base_uri' => self::$baseUri,
            'handler' => $this->handler($apiKey, $middlewares, $handler),
        ]);
    }

    public function handler($apikey, array $middlewares = [], $handler = null)
    {
        $stack = HandlerStack::create($handler);

        $stack->push(
            Middleware::mapRequest(
                function (RequestInterface $request) use ($apikey) {
                    return $request->withUri(
                        Uri::withQueryValue(
                            $request->getUri(), 'apikey', $apikey
                        )
                    );
                }
            )
        );

        $stack->push(Middleware::mapResponse(
            function (ResponseInterface $response) {

                try {
                    $body = json_decode((string)$response->getBody(), true);
                } catch (\Exception $error) {
                    throw new ElasticEmailException('Unable to decode JSON response.');
                }

                if (!$body['success']) {
                    throw new ElasticEmailException($body['error']);
                }

                return $response;
            }
        ));

        foreach ($middlewares as $middleware) {
            $stack->push($middleware);
        }

        return $stack;
    }
}
