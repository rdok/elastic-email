<?php

namespace ElasticEmail\Email;

use ElasticEmail\Client;
use ElasticEmail\Response;

/**
 * @see http://api.elasticemail.com/public/help#Email_Send
 */
class Send extends Response
{
    const URI = 'email/send';

    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function handle(array $params = [], $muiltipartOption = false)
    {
        $options = $this->transform($params, $muiltipartOption);

        $this->response = $this->client->request('POST', self::URI, $options);

        return $this;
    }

    protected function transform(array $params, $multipartOption)
    {
        if (! $multipartOption) {
            return ['form_params' => $params];
        }

        $multipart = [];

        foreach ($params as $key => $value) {
            $multipart[] = [
                'name' => $key,
                'contents' => $value
            ];
        }

        return ['multipart' => $multipart];
    }
}
