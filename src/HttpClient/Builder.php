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

namespace Gitlab\HttpClient;

use Http\Client\Common\HttpMethodsClient;
use Http\Client\Common\HttpMethodsClientInterface;
use Http\Client\Common\Plugin;
use Http\Client\Common\Plugin\Cache\Generator\HeaderCacheKeyGenerator;
use Http\Client\Common\Plugin\CachePlugin;
use Http\Client\Common\PluginClientFactory;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

/**
 * The HTTP client builder class.
 *
 * This will allow you to fluently add and remove plugins.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 * @author Graham Campbell <hello@gjcampbell.co.uk>
 */
final class Builder
{
    /**
     * The object that sends HTTP messages.
     *
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * The HTTP request factory.
     *
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * The HTTP stream factory.
     *
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    /**
     * The URI factory.
     *
     * @var UriFactoryInterface
     */
    private $uriFactory;

    /**
     * The currently registered plugins.
     *
     * @var Plugin[]
     */
    private $plugins = [];

    /**
     * The cache plugin to use.
     *
     * This plugin is specially treated because it has to be the very last plugin.
     *
     * @var CachePlugin|null
     */
    private $cachePlugin;

    /**
     * A HTTP client with all our plugins.
     *
     * @var HttpMethodsClientInterface|null
     */
    private $pluginClient;

    /**
     * Create a new http client builder instance.
     *
     * @param ClientInterface|null         $httpClient
     * @param RequestFactoryInterface|null $requestFactory
     * @param StreamFactoryInterface|null  $streamFactory
     * @param UriFactoryInterface|null     $uriFactory
     *
     * @return void
     */
    public function __construct(
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        ?UriFactoryInterface $uriFactory = null
    ) {
        $this->httpClient = $httpClient ?? Psr18ClientDiscovery::find();
        $this->requestFactory = $requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
        $this->uriFactory = $uriFactory ?? Psr17FactoryDiscovery::findUriFactory();
    }

    /**
     * @return HttpMethodsClientInterface
     */
    public function getHttpClient(): HttpMethodsClientInterface
    {
        if (null === $this->pluginClient) {
            $plugins = $this->plugins;
            if (null !== $this->cachePlugin) {
                $plugins[] = $this->cachePlugin;
            }

            $this->pluginClient = new HttpMethodsClient(
                (new PluginClientFactory())->createClient($this->httpClient, $plugins),
                $this->requestFactory,
                $this->streamFactory
            );
        }

        return $this->pluginClient;
    }

    /**
     * Get the request factory.
     *
     * @return RequestFactoryInterface
     */
    public function getRequestFactory(): RequestFactoryInterface
    {
        return $this->requestFactory;
    }

    /**
     * Get the stream factory.
     *
     * @return StreamFactoryInterface
     */
    public function getStreamFactory(): StreamFactoryInterface
    {
        return $this->streamFactory;
    }

    /**
     * Get the URI factory.
     *
     * @return UriFactoryInterface
     */
    public function getUriFactory(): UriFactoryInterface
    {
        return $this->uriFactory;
    }

    /**
     * Add a new plugin to the end of the plugin chain.
     *
     * @param Plugin $plugin
     *
     * @return void
     */
    public function addPlugin(Plugin $plugin): void
    {
        $this->plugins[] = $plugin;
        $this->pluginClient = null;
    }

    /**
     * Remove a plugin by its fully qualified class name (FQCN).
     *
     * @param string $fqcn
     *
     * @return void
     */
    public function removePlugin(string $fqcn): void
    {
        foreach ($this->plugins as $idx => $plugin) {
            if ($plugin instanceof $fqcn) {
                unset($this->plugins[$idx]);
                $this->pluginClient = null;
            }
        }
    }

    /**
     * Add a cache plugin to cache responses locally.
     *
     * @param CacheItemPoolInterface $cachePool
     * @param array                  $config
     *
     * @return void
     */
    public function addCache(CacheItemPoolInterface $cachePool, array $config = []): void
    {
        if (!isset($config['cache_key_generator'])) {
            $config['cache_key_generator'] = new HeaderCacheKeyGenerator(['Authorization', 'Cookie', 'Accept', 'Content-type']);
        }

        $this->cachePlugin = CachePlugin::clientCache($cachePool, $this->streamFactory, $config);
        $this->pluginClient = null;
    }

    /**
     * Remove the cache plugin.
     *
     * @return void
     */
    public function removeCache(): void
    {
        $this->cachePlugin = null;
        $this->pluginClient = null;
    }
}
