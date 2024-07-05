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

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Registry extends AbstractApi
{
    /**
     * @see https://docs.gitlab.com/ee/api/container_registry.html#get-details-of-a-single-repository
     *
     * @param int|string $repository_id The ID of the registry repository accessible by the authenticated user.
     * @param array $parameters {
     *      @var bool $tags
     *      @var bool $tags_count
     *      @var bool $size
     * }
     * @return mixed
     */
    public function repositories($repository_id, array $parameters = [])
    {
        $resolver = $this->createOptionsResolver();
        $booleanNormalizer = function (Options $resolver, $value): string {
            return $value ? 'true' : 'false';
        };

        $resolver->setDefined('tags')
            ->setAllowedTypes('tags', 'bool')
            ->setNormalizer('tags', $booleanNormalizer);
        $resolver->setDefined('tags_count')
            ->setAllowedTypes('tags_count', 'bool')
            ->setNormalizer('tags_count', $booleanNormalizer);
        $resolver->setDefined('size')
            ->setAllowedTypes('size', 'bool')
            ->setNormalizer('size', $booleanNormalizer);

        return $this->get('registry/repositories/'.self::encodePath($repository_id), $resolver->resolve($parameters));
    }

    /**
     * @see https://docs.gitlab.com/ee/api/container_registry.html#list-registry-repository-tags
     *
     * @param int|string $project_id
     * @param int $repository_id
     * @return mixed
     */
    public function repositoryTags($project_id, int $repository_id)
    {
        return $this->get(
            $this->getProjectPath($project_id, 'registry/repositories/'.self::encodePath($repository_id).'/tags')
        );
    }


    /**
     * @see https://docs.gitlab.com/ee/api/container_registry.html#get-details-of-a-registry-repository-tag
     *
     * @param int|string $project_id
     * @param int $repository_id
     * @param string $tag_name
     * @return mixed
     */
    public function repositoryTag($project_id, int $repository_id, string $tag_name)
    {
        return $this->get(
            $this->getProjectPath(
                $project_id,
                'registry/repositories/'.self::encodePath($repository_id).'/tags/'.self::encodePath($tag_name)
            )
        );
    }


    /**
     * @see https://docs.gitlab.com/ee/api/container_registry.html#delete-a-registry-repository-tag
     *
     * @param int|string $project_id
     * @param int $repository_id
     * @param string $tag_name
     * @return mixed
     */
    public function removeRepositoryTag($project_id, int $repository_id, string $tag_name)
    {
        return $this->delete(
            $this->getProjectPath(
                $project_id,
                'registry/repositories/'.self::encodePath($repository_id).'/tags/'.self::encodePath($tag_name)
            )
        );
    }

    /**
     * @see https://docs.gitlab.com/ee/api/container_registry.html#delete-registry-repository-tags-in-bulk
     *
     * @param int|string $project_id
     * @param int $repository_id
     * @param array $parameters {
     *      @var string $name_regex_delete
     *      @var string $name_regex_keep
     *      @var int $keep_n
     *      @var string $older_than
     * }
     * @return mixed
     */
    public function removeRepositoryTags($project_id, int $repository_id, array $parameters = [])
    {
        $resolver = $this->createOptionsResolver();
        $resolver->setRequired('name_regex_delete')
            ->setAllowedTypes('name_regex_delete', 'string');
        $resolver->setDefined('name_regex_keep')
            ->setAllowedTypes('name_regex_keep', 'string');
        $resolver->setDefined('keep_n')
            ->setAllowedTypes('keep_n', 'int');
        $resolver->setDefined('older_than')
            ->setAllowedTypes('older_than', 'string');


        return $this->delete(
            $this->getProjectPath(
                $project_id,
                'registry/repositories/'.self::encodePath($repository_id).'/tags'
            ),
            $resolver->resolve($parameters)
        );
    }
}