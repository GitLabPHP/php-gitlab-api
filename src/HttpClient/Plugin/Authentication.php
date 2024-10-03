<?php

declare(strict_types=1);

/*
 * This file is part of the Gitlab API library.
 *
 * (c) Matt Humphrey <matth@windsor-telecom.co.uk>
 * (c) Graham Campbell <hello@gjcampbell.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gitlab\HttpClient\Plugin;

use Gitlab\Client;
use Gitlab\Exception\RuntimeException;
use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Add authentication to the request.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 * @author Fabien Bourigault <bourigaultfabien@gmail.com>
 *
 * @internal
 */
final class Authentication implements Plugin
{
    /**
     * @var array<string,string>
     */
    private $headers;

    /**
     * @param string      $method
     * @param string      $token
     * @param string|null $sudo
     *
     * @return void
     */
    public function __construct(string $method, string $token, ?string $sudo = null)
    {
        $this->headers = self::buildHeaders($method, $token, $sudo);
    }

    /**
     * Handle the request and return the response coming from the next callable.
     *
     * @param RequestInterface $request
     * @param callable         $next
     * @param callable         $first
     *
     * @return Promise<ResponseInterface>
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        foreach ($this->headers as $header => $value) {
            $request = $request->withHeader($header, $value);
        }

        return $next($request);
    }

    /**
     * Build the headers to be attached to the request.
     *
     * @param string      $method
     * @param string      $token
     * @param string|null $sudo
     *
     * @throws RuntimeException
     *
     * @return array<string,string>
     */
    private static function buildHeaders(string $method, string $token, ?string $sudo = null): array
    {
        $headers = [];

        switch ($method) {
            case Client::AUTH_HTTP_TOKEN:
                $headers['PRIVATE-TOKEN'] = $token;

                break;
            case Client::AUTH_HTTP_JOB_TOKEN:
                $headers['JOB-TOKEN'] = $token;

                break;
            case Client::AUTH_OAUTH_TOKEN:
                $headers['Authorization'] = \sprintf('Bearer %s', $token);

                break;
            default:
                throw new RuntimeException(\sprintf('Authentication method "%s" not implemented.', $method));
        }

        if (null !== $sudo) {
            $headers['SUDO'] = $sudo;
        }

        return $headers;
    }
}
