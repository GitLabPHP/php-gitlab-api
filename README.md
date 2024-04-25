# GitLab PHP API Client

We present a modern [GitLab API v4](https://docs.gitlab.com/ce/api/) client for PHP.

![Banner](https://user-images.githubusercontent.com/2829600/86969006-fc2e3b00-c164-11ea-80b7-8750160a65c4.png)

<p align="center">
<a href="https://github.com/GitLabPHP/Client/actions?query=workflow%3ATests"><img src="https://img.shields.io/github/actions/workflow/status/GitLabPHP/Client/tests.yml?label=Tests&style=flat-square" alt="Build Status"></img></a>
<a href="https://github.styleci.io/repos/6816335"><img src="https://github.styleci.io/repos/6816335/shield" alt="StyleCI Status"></img></a>
<a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-brightgreen?style=flat-square" alt="Software License"></img></a>
<a href="https://packagist.org/packages/m4tthumphrey/php-gitlab-api"><img src="https://img.shields.io/packagist/dt/m4tthumphrey/php-gitlab-api?style=flat-square" alt="Packagist Downloads"></img></a>
<a href="https://github.com/GitLabPHP/Client/releases"><img src="https://img.shields.io/github/release/GitLabPHP/Client?style=flat-square" alt="Latest Version"></img></a>
</p>

This is strongly based on [php-github-api](https://github.com/KnpLabs/php-github-api) by [KnpLabs](https://github.com/KnpLabs). With this in mind, we now have **very similar** clients for:

* [Bitbucket](https://bitbucket.org/) - [bitbucket/client](https://packagist.org/packages/bitbucket/client) by [Graham Campbell](https://github.com/GrahamCampbell).
* [GitHub](https://github.com/) - [knplabs/github-api](https://packagist.org/packages/knplabs/github-api) by [KnpLabs](https://github.com/KnpLabs/php-github-api).
* [GitLab](https://gitlab.com/) - [m4tthumphrey/php-gitlab-api](https://packagist.org/packages/m4tthumphrey/php-gitlab-api) which is this package!

Check out the [change log](CHANGELOG.md), [releases](https://github.com/GitLabPHP/Client/releases), [security policy](https://github.com/GitLabPHP/Client/security/policy), [license](LICENSE), [code of conduct](.github/CODE_OF_CONDUCT.md), and [contribution guidelines](.github/CONTRIBUTING.md).


## Installation

This version supports [PHP](https://php.net) 7.4-8.3. To get started, simply require the project using [Composer](https://getcomposer.org). You will also need to install packages that "provide" [`psr/http-client-implementation`](https://packagist.org/providers/psr/http-client-implementation) and [`psr/http-factory-implementation`](https://packagist.org/providers/psr/http-factory-implementation).

### Standard Installation

```bash
$ composer require "m4tthumphrey/php-gitlab-api:^11.14" \
  "guzzlehttp/guzzle:^7.8" "http-interop/http-factory-guzzle:^1.2"
```

### Framework Integration

#### Laravel:

```bash
$ composer require "graham-campbell/gitlab:^7.5"
```

#### Symfony:

```bash
$ composer require "zeichen32/gitlabapibundle:^6.1"
```

We are decoupled from any HTTP messaging client by using [PSR-7](https://www.php-fig.org/psr/psr-7/), [PSR-17](https://www.php-fig.org/psr/psr-17/), [PSR-18](https://www.php-fig.org/psr/psr-18/), and [HTTPlug](https://httplug.io/). You can visit [HTTPlug for library users](https://docs.php-http.org/en/latest/httplug/users.html) to get more information about installing HTTPlug related packages. The framework integration [graham-campbell/gitlab](https://github.com/GrahamCampbell/Laravel-GitLab) is by [Graham Campbell](https://github.com/GrahamCampbell) and [zeichen32/gitlabapibundle](https://github.com/Zeichen32/GitLabApiBundle) is by [Jens Averkamp](https://github.com/Zeichen32).

## General API Usage

```php
// Token authentication
$client = new Gitlab\Client();
$client->authenticate('your_http_token', Gitlab\Client::AUTH_HTTP_TOKEN);

// OAuth2 authentication
$client = new Gitlab\Client();
$client->authenticate('your_oauth_token', Gitlab\Client::AUTH_OAUTH_TOKEN);

// An example API call
$project = $client->projects()->create('My Project', [
    'description' => 'This is a project',
    'issues_enabled' => false,
]);
```

### Self-Hosted GitLab

```php
$client = new Gitlab\Client();
$client->setUrl('https://git.yourdomain.com');
$client->authenticate('your_http_token', Gitlab\Client::AUTH_HTTP_TOKEN);
```

### Example with Pager

```php
$pager = new Gitlab\ResultPager($client);
$issues = $pager->fetchAll($client->issues(), 'all', [null, ['state' => 'closed']]);
```

### HTTP Client Builder

By providing a `Gitlab\HttpClient\Builder` to the `Gitlab\Client` constructor, you can customize the HTTP client. For example, to customize the user agent:

```php
$plugin = new Http\Client\Common\Plugin\HeaderSetPlugin([
    'User-Agent' => 'Foobar',
]);

$builder = new Gitlab\HttpClient\Builder();
$builder->addPlugin($plugin);

$client = new Gitlab\Client($builder);
```

One can read more about HTTPlug plugins [here](https://docs.php-http.org/en/latest/plugins/introduction.html#how-it-works). Take a look around the [API methods](https://github.com/GitLabPHP/Client/tree/11.2/src/Api), and please feel free to report any bugs, noting our [code of conduct](.github/CODE_OF_CONDUCT.md).


## Contributing

We will gladly receive issue reports and review and accept pull requests, in accordance with our [code of conduct](.github/CODE_OF_CONDUCT.md) and [contribution guidelines](.github/CONTRIBUTING.md)!

```
$ make install
$ make test
```


## Security

If you discover a security vulnerability within this package, please send an email to Graham Campbell at hello@gjcampbell.co.uk. All security vulnerabilities will be promptly addressed. You may view our full security policy [here](https://github.com/GitLabPHP/Client/security/policy).


## License

GitLab PHP API Client is licensed under [The MIT License (MIT)](LICENSE).
