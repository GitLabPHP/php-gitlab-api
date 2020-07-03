<?php

namespace Gitlab\HttpClient;

use Http\Client\Common\HttpMethodsClient;
use Http\Client\Common\Plugin;
use Http\Client\Common\Plugin\Cache\Generator\HeaderCacheKeyGenerator;
use Http\Client\Common\Plugin\CachePlugin;
use Http\Client\Common\PluginClient;
use Http\Client\Common\PluginClientFactory;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Discovery\StreamFactoryDiscovery;
use Http\Message\MessageFactory;
use Http\Message\RequestFactory;
use Http\Message\StreamFactory;
use Psr\Cache\CacheItemPoolInterface;

/**
 * A builder that builds the API client.
 *
 * This will allow you to fluently add and remove plugins.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 * @author Graham Campbell <graham@alt-three.com>
 */
class Builder
{
    /**
     * The object that sends HTTP messages.
     *
     * @var HttpClient
     */
    private $httpClient;

    /**
     * A HTTP client with all our plugins.
     *
     * @var HttpMethodsClient
     */
    private $pluginClient;

    /**
     * The HTTP request factory.
     *
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * The HTTP stream factory.
     *
     * @var StreamFactory
     */
    private $streamFactory;

    /**
     * True if we should create a new Plugin client at next request.
     *
     * @var bool
     */
    private $httpClientModified = true;

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
     * Create a new http client builder instance.
     *
     * @param HttpClient|null      $httpClient
     * @param RequestFactory|null  $requestFactory
     * @param StreamFactory|null   $streamFactory
     */
    public function __construct(
        HttpClient $httpClient = null,
        RequestFactory $requestFactory = null,
        StreamFactory $streamFactory = null
    ) {
        $this->httpClient = $httpClient ?: HttpClientDiscovery::find();
        $this->requestFactory = $requestFactory ?: MessageFactoryDiscovery::find();
        $this->streamFactory = $streamFactory ?: StreamFactoryDiscovery::find();
    }

    /**
     * @return HttpMethodsClient
     */
    public function getHttpClient()
    {
        if ($this->httpClientModified) {
            $this->httpClientModified = false;

            $plugins = $this->plugins;
            if ($this->cachePlugin !== null) {
                $plugins[] = $this->cachePlugin;
            }

            $this->pluginClient = new HttpMethodsClient(
                (new PluginClientFactory())->createClient($this->httpClient, $plugins),
                $this->requestFactory
            );
        }

        return $this->pluginClient;
    }

    /**
     * Add a new plugin to the end of the plugin chain.
     *
     * @param Plugin $plugin
     *
     * @return void
     */
    public function addPlugin(Plugin $plugin)
    {
        $this->plugins[] = $plugin;
        $this->httpClientModified = true;
    }

    /**
     * Remove a plugin by its fully qualified class name (FQCN).
     *
     * @param string $fqcn
     *
     * @return void
     */
    public function removePlugin($fqcn)
    {
        foreach ($this->plugins as $idx => $plugin) {
            if ($plugin instanceof $fqcn) {
                unset($this->plugins[$idx]);
                $this->httpClientModified = true;
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
    public function addCache(CacheItemPoolInterface $cachePool, array $config = [])
    {
        if (!isset($config['cache_key_generator'])) {
            $config['cache_key_generator'] = new HeaderCacheKeyGenerator(['Authorization', 'Cookie', 'Accept', 'Content-type']);
        }

        $this->cachePlugin = CachePlugin::clientCache($cachePool, $this->streamFactory, $config);
        $this->httpClientModified = true;
    }

    /**
     * Remove the cache plugin.
     *
     * @return void
     */
    public function removeCache()
    {
        $this->cachePlugin = null;
        $this->httpClientModified = true;
    }
}
