<?php declare(strict_types=1);

namespace Emarref\EventStore;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use function GuzzleHttp\Psr7\stream_for;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Client
{
    public const HEADER_CONTENT_TYPE  = 'Content-Type';
    public const HEADER_ACCEPT        = 'Accept';
    public const CONTENT_TYPE_EVENTS  = 'application/vnd.eventstore.events+json';
    public const CONTENT_TYPE_ATOM    = 'application/vnd.eventstore.atom+json';
    public const DEFAULT_CONTENT_TYPE = self::CONTENT_TYPE_ATOM;

    /**
     * @var ClientInterface
     */
    private $http;

    /**
     * @param ClientInterface $http
     */
    public function __construct(ClientInterface $http)
    {
        $this->http = $http;
    }

    public function get(string $uri, array $headers = []): ?array
    {
        $response = $this->exec(new Request('get', $uri, $headers));

        return $this->decode($response);
    }

    public function post(string $uri, array $data, array $headers = []): ?array
    {
        $response = $this->exec(new Request('post', $uri, $headers, stream_for(\json_encode($data))));

        return $this->decode($response);
    }

    /**
     * @param string $uri
     * @param array  $headers
     *
     * @return array|null
     */
    public function delete(string $uri, array $headers = []): ?array
    {
        $response = $this->exec(new Request('delete', $uri, $headers));

        return $this->decode($response);
    }

    /**
     * @param iterable $requests
     * @param int      $concurrency
     *
     * @return array
     */
    public function pool(iterable $requests, int $concurrency = 5): array
    {
        $responses = [];

        $pool = new Pool($this->http, $requests, [
            'concurrency' => $concurrency,
            'fulfilled'   => function ($response, $index) use (&$responses) {
                $responses[$index] = $response;
            },
            'rejected'    => function ($reason, $index) {
                throw $reason;
            },
        ]);

        $pool->promise()->wait();

        \ksort($responses);

        return \array_map([$this, 'decode'], $responses);
    }

    /**
     * @param ResponseInterface $response
     *
     * @return array|null
     */
    private function decode(ResponseInterface $response): ?array
    {
        $content = $response->getBody()->getContents();

        if (null === $content) {
            return null;
        }

        return \json_decode($content, true);
    }

    /**
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     */
    private function exec(RequestInterface $request): ResponseInterface
    {
        if (!$request->hasHeader(self::HEADER_CONTENT_TYPE)) {
            $request = $request->withHeader(self::HEADER_CONTENT_TYPE, self::DEFAULT_CONTENT_TYPE);
        }

        if (!$request->hasHeader(self::HEADER_ACCEPT)) {
            $request = $request->withHeader(self::HEADER_ACCEPT, self::DEFAULT_CONTENT_TYPE);
        }

        return $this->http->send($request);
    }
}
