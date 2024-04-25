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

class Repositories extends AbstractApi
{
    /**
     * @var string
     */
    public const TYPE_BRANCH = 'branch';

    /**
     * @var string
     */
    public const TYPE_TAG = 'tag';

    /**
     * @param int|string $project_id
     * @param array      $parameters {
     *
     *     @var string $search
     * }
     *
     * @return mixed
     */
    public function branches($project_id, array $parameters = [])
    {
        $resolver = $this->createOptionsResolver();
        $resolver->setDefined('search')
            ->setAllowedTypes('search', 'string');

        return $this->get($this->getProjectPath($project_id, 'repository/branches'), $resolver->resolve($parameters));
    }

    /**
     * @param int|string $project_id
     * @param string     $branch
     *
     * @return mixed
     */
    public function branch($project_id, string $branch)
    {
        return $this->get($this->getProjectPath($project_id, 'repository/branches/'.self::encodePath($branch)));
    }

    /**
     * @param int|string $project_id
     * @param string     $branch
     * @param string     $ref
     *
     * @return mixed
     */
    public function createBranch($project_id, string $branch, string $ref)
    {
        return $this->post($this->getProjectPath($project_id, 'repository/branches'), [
            'branch' => $branch,
            'ref' => $ref,
        ]);
    }

    /**
     * @param int|string $project_id
     * @param string     $branch
     *
     * @return mixed
     */
    public function deleteBranch($project_id, string $branch)
    {
        return $this->delete($this->getProjectPath($project_id, 'repository/branches/'.self::encodePath($branch)));
    }

    /**
     * @param int|string $project_id
     * @param string     $branch
     * @param bool       $devPush
     * @param bool       $devMerge
     *
     * @return mixed
     */
    public function protectBranch($project_id, string $branch, bool $devPush = false, bool $devMerge = false)
    {
        return $this->put($this->getProjectPath($project_id, 'repository/branches/'.self::encodePath($branch).'/protect'), [
            'developers_can_push' => $devPush,
            'developers_can_merge' => $devMerge,
        ]);
    }

    /**
     * @param int|string $project_id
     * @param string     $branch
     *
     * @return mixed
     */
    public function unprotectBranch($project_id, string $branch)
    {
        return $this->put($this->getProjectPath($project_id, 'repository/branches/'.self::encodePath($branch).'/unprotect'));
    }

    /**
     * @param int|string $project_id
     * @param array      $parameters
     *
     * @return mixed
     */
    public function tags($project_id, array $parameters = [])
    {
        $resolver = $this->createOptionsResolver();
        $resolver->setDefined('search')
            ->setAllowedTypes('search', 'string');

        return $this->get($this->getProjectPath($project_id, 'repository/tags'), $resolver->resolve($parameters));
    }

    /**
     * @param int|string  $project_id
     * @param string      $name
     * @param string      $ref
     * @param string|null $message
     *
     * @return mixed
     */
    public function createTag($project_id, string $name, string $ref, ?string $message = null)
    {
        return $this->post($this->getProjectPath($project_id, 'repository/tags'), [
            'tag_name' => $name,
            'ref' => $ref,
            'message' => $message,
        ]);
    }

    /**
     * @param int|string  $project_id
     * @param string      $tag_name
     * @param string      $description
     * @param string|null $name
     *
     * @return mixed
     */
    public function createRelease($project_id, string $tag_name, string $description, ?string $name = null)
    {
        return $this->post($this->getProjectPath($project_id, 'releases'), \array_filter([
            'id' => $project_id,
            'tag_name' => $tag_name,
            'description' => $description,
            'name' => $name,
        ], fn ($v) => null !== $v));
    }

    /**
     * @param int|string  $project_id
     * @param string      $tag_name
     * @param string      $description
     * @param string|null $name
     *
     * @return mixed
     */
    public function updateRelease($project_id, string $tag_name, string $description, ?string $name = null)
    {
        return $this->put($this->getProjectPath($project_id, 'releases/'.self::encodePath($tag_name)), \array_filter([
            'id' => $project_id,
            'tag_name' => $tag_name,
            'description' => $description,
            'name' => $name,
        ], fn ($v) => null !== $v));
    }

    /**
     * @param int|string $project_id
     *
     * @return mixed
     */
    public function releases($project_id)
    {
        $resolver = $this->createOptionsResolver();

        return $this->get($this->getProjectPath($project_id, 'releases'));
    }

    /**
     * @see https://docs.gitlab.com/ee/api/commits.html#list-repository-commits
     *
     * @param int|string $project_id
     * @param array      $parameters {
     *
     *     @var string             $ref_name the name of a repository branch or tag or if not given the default branch
     *     @var \DateTimeInterface $since    only commits after or on this date will be returned
     *     @var \DateTimeInterface $until    Only commits before or on this date will be returned.
     * }
     *
     * @return mixed
     */
    public function commits($project_id, array $parameters = [])
    {
        $resolver = $this->createOptionsResolver();
        $datetimeNormalizer = function (Options $options, \DateTimeInterface $value): string {
            return $value->format('c');
        };
        $booleanNormalizer = function (Options $resolver, $value): string {
            return $value ? 'true' : 'false';
        };

        $resolver->setDefined('path');
        $resolver->setDefined('ref_name');
        $resolver->setDefined('author');
        $resolver->setDefined('since')
            ->setAllowedTypes('since', \DateTimeInterface::class)
            ->setNormalizer('since', $datetimeNormalizer)
        ;
        $resolver->setDefined('until')
            ->setAllowedTypes('until', \DateTimeInterface::class)
            ->setNormalizer('until', $datetimeNormalizer)
        ;
        $resolver->setDefined('all')
            ->setAllowedTypes('all', 'bool')
            ->setNormalizer('all', $booleanNormalizer)
        ;
        $resolver->setDefined('with_stats')
            ->setAllowedTypes('with_stats', 'bool')
            ->setNormalizer('with_stats', $booleanNormalizer)
        ;
        $resolver->setDefined('first_parent')
            ->setAllowedTypes('first_parent', 'bool')
            ->setNormalizer('first_parent', $booleanNormalizer)
        ;
        $resolver->setDefined('order')
            ->setAllowedValues('order', ['default', 'topo'])
        ;

        return $this->get($this->getProjectPath($project_id, 'repository/commits'), $resolver->resolve($parameters));
    }

    /**
     * @param int|string $project_id
     * @param string     $sha
     *
     * @return mixed
     */
    public function commit($project_id, string $sha)
    {
        return $this->get($this->getProjectPath($project_id, 'repository/commits/'.self::encodePath($sha)));
    }

    /**
     * @param int|string $project_id
     * @param string     $sha
     * @param array      $parameters
     *
     * @return mixed
     */
    public function commitRefs($project_id, string $sha, array $parameters = [])
    {
        $resolver = $this->createOptionsResolver();

        return $this->get(
            $this->getProjectPath($project_id, 'repository/commits/'.self::encodePath($sha).'/refs'),
            $resolver->resolve($parameters)
        );
    }

    /**
     * @param int|string $project_id
     * @param array      $parameters {
     *
     *     @var string $branch         Name of the branch to commit into. To create a new branch, also provide start_branch.
     *     @var string $commit_message commit message
     *     @var string $start_branch   name of the branch to start the new commit from
     *     @var array $actions {
     *         @var string $action        he action to perform, create, delete, move, update
     *         @var string $file_path     full path to the file
     *         @var string $previous_path original full path to the file being moved
     *         @var string $content       File content, required for all except delete. Optional for move.
     *         @var string $encoding      text or base64. text is default.
     *     }
     *     @var string $author_email   specify the commit author's email address
     *     @var string $author_name    Specify the commit author's name.
     * }
     *
     * @return mixed
     */
    public function createCommit($project_id, array $parameters = [])
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined('branch')
            ->setRequired('branch')
        ;
        $resolver->setDefined('commit_message')
            ->setRequired('commit_message')
        ;
        $resolver->setDefined('start_branch');
        $resolver->setDefined('actions')
            ->setRequired('actions')
            ->setAllowedTypes('actions', 'array')
            ->setAllowedValues('actions', function (array $actions) {
                return 0 < \count($actions);
            })
            ->setNormalizer('actions', function (Options $resolver, array $actions) {
                $actionsOptionsResolver = new OptionsResolver();
                $actionsOptionsResolver->setDefined('action')
                    ->setRequired('action')
                    ->setAllowedValues('action', ['create', 'delete', 'move', 'update', 'chmod'])
                ;
                $actionsOptionsResolver->setDefined('file_path')
                    ->setRequired('file_path')
                ;
                $actionsOptionsResolver->setDefined('previous_path');
                $actionsOptionsResolver->setDefined('content');
                $actionsOptionsResolver->setDefined('encoding')
                    ->setAllowedValues('encoding', ['text', 'base64'])
                ;
                $actionsOptionsResolver->setDefined('execute_filemode')
                    ->setAllowedValues('execute_filemode', [true, false])
                ;

                return \array_map(function ($action) use ($actionsOptionsResolver) {
                    return $actionsOptionsResolver->resolve($action);
                }, $actions);
            })
        ;
        $resolver->setDefined('author_email');
        $resolver->setDefined('author_name');

        return $this->post($this->getProjectPath($project_id, 'repository/commits'), $resolver->resolve($parameters));
    }

    /**
     * @param int|string $project_id
     * @param string     $branch
     * @param string     $sha
     *
     * @return mixed
     */
    public function revertCommit($project_id, string $branch, string $sha)
    {
        return $this->post($this->getProjectPath($project_id, 'repository/commits/'.self::encodePath($sha).'/revert'), [
            'branch' => $branch,
        ]);
    }

    /**
     * @param int|string $project_id
     * @param string     $sha
     * @param array      $parameters
     *
     * @return mixed
     */
    public function commitComments($project_id, string $sha, array $parameters = [])
    {
        $resolver = $this->createOptionsResolver();

        return $this->get(
            $this->getProjectPath($project_id, 'repository/commits/'.self::encodePath($sha).'/comments'),
            $resolver->resolve($parameters)
        );
    }

    /**
     * @param int|string $project_id
     * @param string     $sha
     * @param string     $note
     * @param array      $params
     *
     * @return mixed
     */
    public function createCommitComment($project_id, string $sha, string $note, array $params = [])
    {
        $params['note'] = $note;

        return $this->post($this->getProjectPath($project_id, 'repository/commits/'.self::encodePath($sha).'/comments'), $params);
    }

    /**
     * @param int|string $project_id
     * @param string     $sha
     * @param array      $params
     *
     * @return mixed
     */
    public function getCommitBuildStatus($project_id, string $sha, array $params = [])
    {
        return $this->get($this->getProjectPath($project_id, 'repository/commits/'.self::encodePath($sha).'/statuses'), $params);
    }

    /**
     * @param int|string $project_id
     * @param string     $sha
     * @param string     $state
     * @param array      $params
     *
     * @return mixed
     */
    public function postCommitBuildStatus($project_id, string $sha, string $state, array $params = [])
    {
        $params['state'] = $state;

        return $this->post($this->getProjectPath($project_id, 'statuses/'.self::encodePath($sha)), $params);
    }

    /**
     * @param int|string  $project_id
     * @param string      $fromShaOrMaster
     * @param string      $toShaOrMaster
     * @param bool        $straight
     * @param string|null $fromProjectId
     *
     * @return mixed
     */
    public function compare($project_id, string $fromShaOrMaster, string $toShaOrMaster, bool $straight = false, string $fromProjectId = null)
    {
        $params = [
            'from' => $fromShaOrMaster,
            'to' => $toShaOrMaster,
            'straight' => $straight ? 'true' : 'false',
        ];

        if (null !== $fromProjectId) {
            $params['from_project_id'] = self::encodePath($fromProjectId);
        }

        return $this->get($this->getProjectPath($project_id, 'repository/compare'), $params);
    }

    /**
     * @param int|string $project_id
     * @param string     $sha
     *
     * @return mixed
     */
    public function diff($project_id, string $sha)
    {
        return $this->get($this->getProjectPath($project_id, 'repository/commits/'.self::encodePath($sha).'/diff'));
    }

    /**
     * @param int|string $project_id
     * @param array      $params
     *
     * @return mixed
     */
    public function tree($project_id, array $params = [])
    {
        return $this->get($this->getProjectPath($project_id, 'repository/tree'), $params);
    }

    /**
     * @param int|string $project_id
     *
     * @return mixed
     */
    public function contributors($project_id)
    {
        return $this->get($this->getProjectPath($project_id, 'repository/contributors'));
    }

    /**
     * @param int|string $project_id
     * @param array      $params
     * @param string     $format     Options: "tar.gz", "zip", "tar.bz2" and "tar"
     *
     * @return mixed
     */
    public function archive($project_id, array $params = [], string $format = 'tar.gz')
    {
        return $this->get($this->getProjectPath($project_id, 'repository/archive.'.$format), $params);
    }

    /**
     * @param int|string $project_id
     * @param array      $refs
     *
     * @return mixed
     */
    public function mergeBase($project_id, array $refs)
    {
        return $this->get($this->getProjectPath($project_id, 'repository/merge_base'), ['refs' => $refs]);
    }

    /**
     * @param int|string $project_id
     * @param string     $sha
     * @param array      $params
     *
     * @return mixed
     */
    public function cherryPick($project_id, string $sha, array $params = [])
    {
        $resolver = $this->createOptionsResolver();
        $booleanNormalizer = function (Options $resolver, $value): string {
            return $value ? 'true' : 'false';
        };

        $resolver->setDefined('branch')
            ->setRequired('branch');

        $resolver->setDefined('dry_run')
            ->setAllowedTypes('dry_run', 'bool')
            ->setNormalizer('dry_run', $booleanNormalizer);

        return $this->post($this->getProjectPath($project_id, 'repository/commits/'.self::encodePath($sha).'/cherry_pick'), $params);
    }

    /**
     * @return OptionsResolver
     */
    protected function createOptionsResolver(): OptionsResolver
    {
        $allowedTypeValues = [
            self::TYPE_BRANCH,
            self::TYPE_TAG,
        ];

        $resolver = parent::createOptionsResolver();
        $resolver->setDefined('type')
            ->setAllowedTypes('type', 'string')
            ->setAllowedValues('type', $allowedTypeValues);

        return $resolver;
    }
}
