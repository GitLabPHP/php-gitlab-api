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

namespace Gitlab;

use Gitlab\Api\DeployKeys;
use Gitlab\Api\Deployments;
use Gitlab\Api\Environments;
use Gitlab\Api\Events;
use Gitlab\Api\Groups;
use Gitlab\Api\GroupsBoards;
use Gitlab\Api\GroupsEpics;
use Gitlab\Api\GroupsMilestones;
use Gitlab\Api\IssueBoards;
use Gitlab\Api\IssueLinks;
use Gitlab\Api\Issues;
use Gitlab\Api\IssuesStatistics;
use Gitlab\Api\Jobs;
use Gitlab\Api\Keys;
use Gitlab\Api\MergeRequests;
use Gitlab\Api\Milestones;
use Gitlab\Api\ProjectNamespaces;
use Gitlab\Api\Projects;
use Gitlab\Api\Repositories;
use Gitlab\Api\RepositoryFiles;
use Gitlab\Api\ResourceIterationEvents;
use Gitlab\Api\ResourceLabelEvents;
use Gitlab\Api\ResourceMilestoneEvents;
use Gitlab\Api\ResourceStateEvents;
use Gitlab\Api\ResourceWeightEvents;
use Gitlab\Api\Schedules;
use Gitlab\Api\Search;
use Gitlab\Api\Snippets;
use Gitlab\Api\SystemHooks;
use Gitlab\Api\Tags;
use Gitlab\Api\Users;
use Gitlab\Api\Version;
use Gitlab\Api\Wiki;
use Gitlab\HttpClient\Builder;
use Gitlab\HttpClient\Plugin\Authentication;
use Gitlab\HttpClient\Plugin\ExceptionThrower;
use Gitlab\HttpClient\Plugin\History;
use Http\Client\Common\HttpMethodsClientInterface;
use Http\Client\Common\Plugin\AddHostPlugin;
use Http\Client\Common\Plugin\HeaderDefaultsPlugin;
use Http\Client\Common\Plugin\HistoryPlugin;
use Http\Client\Common\Plugin\RedirectPlugin;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Simple API wrapper for Gitlab.
 *
 * @author Matt Humphrey <matt@m4tt.co>
 */
class Client
{
    /**
     * The private token authentication method.
     *
     * @var string
     */
    public const AUTH_HTTP_TOKEN = 'http_token';

    /**
     * The job token authentication method.
     *
     * @var string
     */
    public const AUTH_HTTP_JOB_TOKEN = 'http_job_token';

    /**
     * The OAuth 2 token authentication method.
     *
     * @var string
     */
    public const AUTH_OAUTH_TOKEN = 'oauth_token';

    /**
     * The default base URL.
     *
     * @var string
     */
    private const BASE_URL = 'https://gitlab.com';

    /**
     * The default user agent header.
     *
     * @var string
     */
    private const USER_AGENT = 'gitlab-php-api-client/11.15';

    /**
     * The HTTP client builder.
     *
     * @var Builder
     */
    private $httpClientBuilder;

    /**
     * The response history plugin.
     *
     * @var History
     */
    private $responseHistory;

    /**
     * Instantiate a new Gitlab client.
     *
     * @param Builder|null $httpClientBuilder
     *
     * @return void
     */
    public function __construct(Builder $httpClientBuilder = null)
    {
        $this->httpClientBuilder = $builder = $httpClientBuilder ?? new Builder();
        $this->responseHistory = new History();

        $builder->addPlugin(new ExceptionThrower());
        $builder->addPlugin(new HistoryPlugin($this->responseHistory));
        $builder->addPlugin(new HeaderDefaultsPlugin([
            'User-Agent' => self::USER_AGENT,
        ]));
        $builder->addPlugin(new RedirectPlugin());

        $this->setUrl(self::BASE_URL);
    }

    /**
     * Create a Gitlab\Client using an HTTP client.
     *
     * @param ClientInterface $httpClient
     *
     * @return Client
     */
    public static function createWithHttpClient(ClientInterface $httpClient): self
    {
        $builder = new Builder($httpClient);

        return new self($builder);
    }

    /**
     * @return DeployKeys
     */
    public function deployKeys(): DeployKeys
    {
        return new DeployKeys($this);
    }

    /**
     * @return Deployments
     */
    public function deployments(): Deployments
    {
        return new Deployments($this);
    }

    /**
     * @return Environments
     */
    public function environments(): Environments
    {
        return new Environments($this);
    }

    /**
     * @return Events
     */
    public function events(): Events
    {
        return new Events($this);
    }

    /**
     * @return Groups
     */
    public function groups(): Groups
    {
        return new Groups($this);
    }

    /**
     * @return GroupsBoards
     */
    public function groupsBoards(): GroupsBoards
    {
        return new GroupsBoards($this);
    }

    /**
     * @return GroupsEpics
     */
    public function groupsEpics(): GroupsEpics
    {
        return new GroupsEpics($this);
    }

    /**
     * @return GroupsMilestones
     */
    public function groupsMilestones(): GroupsMilestones
    {
        return new GroupsMilestones($this);
    }

    /**
     * @return IssueBoards
     */
    public function issueBoards(): IssueBoards
    {
        return new IssueBoards($this);
    }

    /**
     * @return IssueLinks
     */
    public function issueLinks(): IssueLinks
    {
        return new IssueLinks($this);
    }

    /**
     * @return Issues
     */
    public function issues(): Issues
    {
        return new Issues($this);
    }

    /**
     * @return ResourceIterationEvents
     */
    public function resourceIterationEvents(): ResourceIterationEvents
    {
        return new ResourceIterationEvents($this);
    }

    /**
     * @return ResourceLabelEvents
     */
    public function resourceLabelEvents(): ResourceLabelEvents
    {
        return new ResourceLabelEvents($this);
    }

    /**
     * @return ResourceMilestoneEvents
     */
    public function resourceMilestoneEvents(): ResourceMilestoneEvents
    {
        return new ResourceMilestoneEvents($this);
    }

    /**
     * @return ResourceStateEvents
     */
    public function resourceStateEvents(): ResourceStateEvents
    {
        return new ResourceStateEvents($this);
    }

    /**
     * @return ResourceWeightEvents
     */
    public function resourceWeightEvents(): ResourceWeightEvents
    {
        return new ResourceWeightEvents($this);
    }

    /**
     * @return IssuesStatistics
     */
    public function issuesStatistics(): IssuesStatistics
    {
        return new IssuesStatistics($this);
    }

    /**
     * @return Jobs
     */
    public function jobs(): Jobs
    {
        return new Jobs($this);
    }

    /**
     * @return Keys
     */
    public function keys(): Keys
    {
        return new Keys($this);
    }

    /**
     * @return MergeRequests
     */
    public function mergeRequests(): MergeRequests
    {
        return new MergeRequests($this);
    }

    /**
     * @return Milestones
     */
    public function milestones(): Milestones
    {
        return new Milestones($this);
    }

    /**
     * @return ProjectNamespaces
     */
    public function namespaces(): ProjectNamespaces
    {
        return new ProjectNamespaces($this);
    }

    /**
     * @return Projects
     */
    public function projects(): Projects
    {
        return new Projects($this);
    }

    /**
     * @return Repositories
     */
    public function repositories(): Repositories
    {
        return new Repositories($this);
    }

    /**
     * @return RepositoryFiles
     */
    public function repositoryFiles(): RepositoryFiles
    {
        return new RepositoryFiles($this);
    }

    /**
     * @return Search
     */
    public function search(): Search
    {
        return new Search($this);
    }

    /**
     * @return Schedules
     */
    public function schedules(): Schedules
    {
        return new Schedules($this);
    }

    /**
     * @return Snippets
     */
    public function snippets(): Snippets
    {
        return new Snippets($this);
    }

    /**
     * @return SystemHooks
     */
    public function systemHooks(): SystemHooks
    {
        return new SystemHooks($this);
    }

    /**
     * @return Tags
     */
    public function tags(): Tags
    {
        return new Tags($this);
    }

    /**
     * @return Topics
     */
    public function topics(): Topics
    {
        return new Topics($this);
    }

    /**
     * @return Users
     */
    public function users(): Users
    {
        return new Users($this);
    }

    /**
     * @return Version
     */
    public function version(): Version
    {
        return new Version($this);
    }

    /**
     * @return Wiki
     */
    public function wiki(): Wiki
    {
        return new Wiki($this);
    }

    /**
     * Authenticate a user for all next requests.
     *
     * @param string      $token      Gitlab private token
     * @param string      $authMethod One of the AUTH_* class constants
     * @param string|null $sudo
     *
     * @return void
     */
    public function authenticate(string $token, string $authMethod, string $sudo = null): void
    {
        $this->getHttpClientBuilder()->removePlugin(Authentication::class);
        $this->getHttpClientBuilder()->addPlugin(new Authentication($authMethod, $token, $sudo));
    }

    /**
     * @param string $url
     *
     * @return void
     */
    public function setUrl(string $url): void
    {
        $uri = $this->getHttpClientBuilder()->getUriFactory()->createUri($url);

        $this->getHttpClientBuilder()->removePlugin(AddHostPlugin::class);
        $this->getHttpClientBuilder()->addPlugin(new AddHostPlugin($uri));
    }

    /**
     * Get the last response.
     *
     * @return ResponseInterface|null
     */
    public function getLastResponse(): ?ResponseInterface
    {
        return $this->responseHistory->getLastResponse();
    }

    /**
     * Get the HTTP client.
     *
     * @return HttpMethodsClientInterface
     */
    public function getHttpClient(): HttpMethodsClientInterface
    {
        return $this->getHttpClientBuilder()->getHttpClient();
    }

    /**
     * Get the stream factory.
     *
     * @return StreamFactoryInterface
     */
    public function getStreamFactory(): StreamFactoryInterface
    {
        return $this->getHttpClientBuilder()->getStreamFactory();
    }

    /**
     * Get the HTTP client builder.
     *
     * @return Builder
     */
    protected function getHttpClientBuilder(): Builder
    {
        return $this->httpClientBuilder;
    }
}
