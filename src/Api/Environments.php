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

namespace Gitlab\Api;

use DateTimeInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Environments extends AbstractApi
{
    /**
     * @param int|string $project_id
     * @param array      $parameters
     *
     * @return mixed
     */
    public function all($project_id, array $parameters = [])
    {
        $resolver = $this->createOptionsResolver();
        $resolver->setDefined('name')
            ->setAllowedTypes('name', 'string');
        $resolver->setDefined('search')
            ->setAllowedTypes('search', 'string');
        $resolver->setDefined('states')
            ->setAllowedTypes('states', 'string')
            ->setAllowedValues('states', ['available', 'stopped']);

        return $this->get($this->getProjectPath($project_id, 'environments'), $resolver->resolve($parameters));
    }

    /**
     * @param int|string $project_id
     * @param array      $parameters {
     *
     *     @var string $name         The name of the environment
     *     @var string $external_url Place to link to for this environment
     *     @var string $tier         The tier of the new environment. Allowed values are production, staging, testing, development, and other.
     * }
     *
     * @return mixed
     */
    public function create($project_id, array $parameters = [])
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined('name')
            ->setRequired('name')
            ->setAllowedTypes('name', 'string');
        $resolver->setDefined('external_url')
            ->setAllowedTypes('external_url', 'string');
        $resolver->setDefined('tier')
            ->setAllowedValues('tier', ['production', 'staging', 'testing', 'development', 'other']);

        return $this->post($this->getProjectPath($project_id, 'environments'), $resolver->resolve($parameters));
    }

    /**
     * @param int|string $project_id
     * @param int        $environment_id
     *
     * @return mixed
     */
    public function remove($project_id, int $environment_id)
    {
        return $this->delete($this->getProjectPath($project_id, 'environments/'.$environment_id));
    }

    /**
     * @param int|string $project_id
     * @param int        $environment_id
     *
     * @return mixed
     */
    public function stop($project_id, int $environment_id)
    {
        return $this->post($this->getProjectPath($project_id, 'environments/'.self::encodePath($environment_id).'/stop'));
    }

    /**
     * @param int|string $project_id
     * @param int        $environment_id
     *
     * @return mixed
     */
    public function show($project_id, int $environment_id)
    {
        return $this->get($this->getProjectPath($project_id, 'environments/'.self::encodePath($environment_id)));
    }

    /**
     * @param int|string $project_id
     * @param array      $parameters {
     *
     *     @var DateTimeInterface $before Stop environments that have been modified or deployed to before the specified date.
     *                                    Expected in ISO 8601 format (2019-03-15T08:00:00Z).
     *                                    Valid inputs are between 10 years ago and 1 week ago
     * }
     *
     * @return mixed
     */
    public function stopStale($project_id, array $parameters = [])
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined('before')
            ->setRequired('before')
            ->setAllowedTypes('before', DateTimeInterface::class)
            ->setNormalizer('before', fn (Options $resolver, DateTimeInterface $value): string => $value->format('c'));

        return $this->post(
            $this->getProjectPath(
                $project_id,
                'environments/stop_stale'
            ),
            $resolver->resolve($parameters),
        );
    }
}
