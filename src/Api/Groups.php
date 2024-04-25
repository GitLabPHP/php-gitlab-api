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

use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Groups extends AbstractApi
{
    /**
     * @var string
     */
    public const STATE_ALL = 'all';

    /**
     * @var string
     */
    public const STATE_MERGED = 'merged';

    /**
     * @var string
     */
    public const STATE_OPENED = 'opened';

    /**
     * @var string
     */
    public const STATE_CLOSED = 'closed';

    /**
     * @var string
     */
    public const STATE_LOCKED = 'locked';

    /**
     * @param array $parameters {
     *
     *     @var int[]  $skip_groups      skip the group IDs passes
     *     @var bool   $all_available    show all the groups you have access to
     *     @var string $search           return list of authorized groups matching the search criteria
     *     @var string $order_by         Order groups by name or path (default is name)
     *     @var string $sort             Order groups in asc or desc order (default is asc)
     *     @var bool   $statistics       include group statistics (admins only)
     *     @var bool   $owned            limit by groups owned by the current user
     *     @var int    $min_access_level limit by groups in which the current user has at least this access level
     *     @var bool   $top_level_only   limit to top level groups, excluding all subgroups
     * }
     *
     * @return mixed
     */
    public function all(array $parameters = [])
    {
        $resolver = $this->getGroupSearchResolver();

        return $this->get('groups', $resolver->resolve($parameters));
    }

    /**
     * @param int|string $id
     *
     * @return mixed
     */
    public function show($id)
    {
        return $this->get('groups/'.self::encodePath($id));
    }

    /**
     * @param string $name
     * @param string $path
     * @param string $description
     * @param string $visibility
     * @param bool   $lfs_enabled
     * @param bool   $request_access_enabled
     * @param int    $parent_id
     * @param int    $shared_runners_minutes_limit
     *
     * @return mixed
     */
    public function create(string $name, string $path, string $description = null, string $visibility = 'private', bool $lfs_enabled = null, bool $request_access_enabled = null, int $parent_id = null, int $shared_runners_minutes_limit = null)
    {
        $params = [
            'name' => $name,
            'path' => $path,
            'description' => $description,
            'visibility' => $visibility,
            'lfs_enabled' => $lfs_enabled,
            'request_access_enabled' => $request_access_enabled,
            'parent_id' => $parent_id,
            'shared_runners_minutes_limit' => $shared_runners_minutes_limit,
        ];

        return $this->post('groups', \array_filter($params, function ($value) {
            return null !== $value && (!\is_string($value) || '' !== $value);
        }));
    }

    /**
     * @param int|string $id
     * @param array      $params
     *
     * @return mixed
     */
    public function update($id, array $params)
    {
        return $this->put('groups/'.self::encodePath($id), $params);
    }

    /**
     * @param int|string $group_id
     *
     * @return mixed
     */
    public function remove($group_id)
    {
        return $this->delete('groups/'.self::encodePath($group_id));
    }

    /**
     * @param int|string $group_id
     * @param int|string $project_id
     *
     * @return mixed
     */
    public function transfer($group_id, $project_id)
    {
        return $this->post('groups/'.self::encodePath($group_id).'/projects/'.self::encodePath($project_id));
    }

    /**
     * @param int|string $group_id
     * @param array      $parameters
     *
     * @return mixed
     */
    public function allMembers($group_id, array $parameters = [])
    {
        $resolver = $this->createOptionsResolver();
        $resolver->setDefined('query');
        $resolver->setDefined('user_ids')
            ->setAllowedTypes('user_ids', 'array')
            ->setAllowedValues('user_ids', function (array $value) {
                return \count($value) === \count(\array_filter($value, 'is_int'));
            })
        ;

        return $this->get('groups/'.self::encodePath($group_id).'/members/all', $resolver->resolve($parameters));
    }

    /**
     * @param int|string $group_id
     * @param array      $parameters {
     *
     *     @var string $query A query string to search for members.
     * }
     *
     * @return mixed
     */
    public function members($group_id, array $parameters = [])
    {
        $resolver = $this->createOptionsResolver();
        $resolver->setDefined('query');
        $resolver->setDefined('user_ids')
            ->setAllowedTypes('user_ids', 'array')
            ->setAllowedValues('user_ids', function (array $value) {
                return \count($value) === \count(\array_filter($value, 'is_int'));
            })
        ;

        return $this->get('groups/'.self::encodePath($group_id).'/members', $resolver->resolve($parameters));
    }

    /**
     * @param int|string $group_id
     * @param int        $user_id
     *
     * @return mixed
     */
    public function member($group_id, int $user_id)
    {
        return $this->get('groups/'.self::encodePath($group_id).'/members/'.self::encodePath($user_id));
    }

    /**
     * @param int|string $group_id
     * @param int        $user_id
     *
     * @return mixed
     */
    public function allMember($group_id, int $user_id)
    {
        return $this->get('groups/'.self::encodePath($group_id).'/members/all/'.self::encodePath($user_id));
    }

    /**
     * @param int|string $group_id
     * @param int        $user_id
     * @param int        $access_level
     * @param array      $parameters
     *
     * @return mixed
     */
    public function addMember($group_id, int $user_id, int $access_level, array $parameters = [])
    {
        $dateNormalizer = function (OptionsResolver $optionsResolver, \DateTimeInterface $date): string {
            return $date->format('Y-m-d');
        };

        $resolver = $this->createOptionsResolver()
            ->setDefined('expires_at')
            ->setAllowedTypes('expires_at', \DateTimeInterface::class)
            ->setNormalizer('expires_at', $dateNormalizer)
        ;

        $parameters = \array_merge([
            'user_id' => $user_id,
            'access_level' => $access_level,
        ], $resolver->resolve($parameters));

        return $this->post('groups/'.self::encodePath($group_id).'/members', $parameters);
    }

    /**
     * @param int|string $group_id
     * @param int        $user_id
     * @param int        $access_level
     *
     * @return mixed
     */
    public function saveMember($group_id, int $user_id, int $access_level)
    {
        return $this->put('groups/'.self::encodePath($group_id).'/members/'.self::encodePath($user_id), [
            'access_level' => $access_level,
        ]);
    }

    /**
     * @param int|string $group_id
     * @param array      $parameters {
     *
     *     @var int    $group_access the access level to grant the group
     *     @var string $expires_at   share expiration date in ISO 8601 format: 2016-09-26
     * }
     *
     * @return mixed
     */
    public function addShare($group_id, array $parameters = [])
    {
        $resolver = $this->createOptionsResolver();

        $datetimeNormalizer = function (OptionsResolver $optionsResolver, \DateTimeInterface $value) {
            return $value->format('Y-m-d');
        };

        $resolver->setRequired('group_id')
            ->setAllowedTypes('group_id', 'int');

        $resolver->setRequired('group_access')
            ->setAllowedTypes('group_access', 'int')
            ->setAllowedValues('group_access', [0, 10, 20, 30, 40, 50]);

        $resolver->setDefined('expires_at')
            ->setAllowedTypes('expires_at', \DateTimeInterface::class)
            ->setNormalizer('expires_at', $datetimeNormalizer)
        ;

        return $this->post('groups/'.self::encodePath($group_id).'/share', $resolver->resolve($parameters));
    }

    /**
     * @param int|string $group_id
     * @param int        $user_id
     *
     * @return mixed
     */
    public function removeMember($group_id, int $user_id)
    {
        return $this->delete('groups/'.self::encodePath($group_id).'/members/'.self::encodePath($user_id));
    }

    /**
     * @param int|string $id
     * @param array      $parameters {
     *
     *     @var bool   $archived                    limit by archived status
     *     @var string $visibility                  limit by visibility public, internal, or private
     *     @var string $order_by                    Return projects ordered by id, name, path, created_at, updated_at, or last_activity_at fields.
     *                                              Default is created_at.
     *     @var string $sort                        Return projects sorted in asc or desc order (default is desc)
     *     @var string $search                      return list of authorized projects matching the search criteria
     *     @var bool   $simple                      return only the ID, URL, name, and path of each project
     *     @var bool   $owned                       limit by projects owned by the current user
     *     @var bool   $starred                     limit by projects starred by the current user
     *     @var bool   $with_issues_enabled         Limit by projects with issues feature enabled (default is false)
     *     @var bool   $with_merge_requests_enabled Limit by projects with merge requests feature enabled (default is false)
     *     @var bool   $with_shared                 Include projects shared to this group (default is true)
     *     @var bool   $include_subgroups           Include projects in subgroups of this group (default is false)
     *     @var bool   $with_custom_attributes      Include custom attributes in response (admins only)
     *     @var \DateTimeInterface $last_activity_after Limit by projects with last_activity after specified time
     * }
     *
     * @return mixed
     */
    public function projects($id, array $parameters = [])
    {
        $resolver = $this->createOptionsResolver();
        $booleanNormalizer = function (Options $resolver, $value): string {
            return $value ? 'true' : 'false';
        };
        $datetimeNormalizer = function (Options $resolver, \DateTimeInterface $value): string {
            return $value->format('c');
        };

        $resolver->setDefined('archived')
            ->setAllowedTypes('archived', 'bool')
            ->setNormalizer('archived', $booleanNormalizer)
        ;
        $resolver->setDefined('visibility')
            ->setAllowedValues('visibility', ['public', 'internal', 'private'])
        ;
        $resolver->setDefined('order_by')
            ->setAllowedValues('order_by', ['id', 'name', 'path', 'created_at', 'updated_at', 'last_activity_at'])
        ;
        $resolver->setDefined('sort')
            ->setAllowedValues('sort', ['asc', 'desc'])
        ;
        $resolver->setDefined('search');
        $resolver->setDefined('simple')
            ->setAllowedTypes('simple', 'bool')
            ->setNormalizer('simple', $booleanNormalizer)
        ;
        $resolver->setDefined('owned')
            ->setAllowedTypes('owned', 'bool')
            ->setNormalizer('owned', $booleanNormalizer)
        ;
        $resolver->setDefined('starred')
            ->setAllowedTypes('starred', 'bool')
            ->setNormalizer('starred', $booleanNormalizer)
        ;
        $resolver->setDefined('with_issues_enabled')
            ->setAllowedTypes('with_issues_enabled', 'bool')
            ->setNormalizer('with_issues_enabled', $booleanNormalizer)
        ;
        $resolver->setDefined('with_merge_requests_enabled')
            ->setAllowedTypes('with_merge_requests_enabled', 'bool')
            ->setNormalizer('with_merge_requests_enabled', $booleanNormalizer)
        ;
        $resolver->setDefined('with_shared')
            ->setAllowedTypes('with_shared', 'bool')
            ->setNormalizer('with_shared', $booleanNormalizer)
        ;
        $resolver->setDefined('include_subgroups')
            ->setAllowedTypes('include_subgroups', 'bool')
            ->setNormalizer('include_subgroups', $booleanNormalizer)
        ;
        $resolver->setDefined('with_custom_attributes')
            ->setAllowedTypes('with_custom_attributes', 'bool')
            ->setNormalizer('with_custom_attributes', $booleanNormalizer)
        ;
        $resolver->setDefined('last_activity_after')
            ->setAllowedTypes('last_activity_after', \DateTimeInterface::class)
            ->setNormalizer('last_activity_after', $datetimeNormalizer)
        ;

        return $this->get('groups/'.self::encodePath($id).'/projects', $resolver->resolve($parameters));
    }

    /**
     * @param int|string $group_id
     * @param array      $parameters {
     *
     *     @var int[]  $skip_groups   skip the group IDs passes
     *     @var bool   $all_available show all the groups you have access to
     *     @var string $search        return list of authorized groups matching the search criteria
     *     @var string $order_by      Order groups by name or path (default is name)
     *     @var string $sort          Order groups in asc or desc order (default is asc)
     *     @var bool   $statistics    include group statistics (admins only)
     *     @var bool   $owned         Limit by groups owned by the current user.
     * }
     *
     * @return mixed
     */
    public function subgroups($group_id, array $parameters = [])
    {
        $resolver = $this->getSubgroupSearchResolver();

        return $this->get('groups/'.self::encodePath($group_id).'/subgroups', $resolver->resolve($parameters));
    }

    /**
     * @param int|string $group_id
     * @param array      $parameters {
     *
     *     @var string   $assignee_id              Return issues assigned to the given user id. Mutually exclusive with assignee_username.
     *                                             None returns unassigned issues. Any returns issues with an assignee.
     *     @var string   $assignee_username        Return issues assigned to the given username. Similar to assignee_id and mutually exclusive with assignee_id.
     *                                             In GitLab CE, the assignee_username array should only contain a single value. Otherwise, an invalid parameter error is returned.
     *     @var int      $author_id                Return issues created by the given user id. Mutually exclusive with author_username.
     *                                             Combine with scope=all or scope=assigned_to_me.
     *     @var string   $author_username          Return issues created by the given username. Similar to author_id and mutually exclusive with author_id.
     *     @var bool     $confidential             Filter confidential or public issues
     *     @var \DateTimeInterface $created_after  Return issues created after the given time (inclusive)
     *     @var \DateTimeInterface $created_before Return issues created before the given time (inclusive)
     *     @var int      $iteration_id             Return issues assigned to the given iteration ID. None returns issues that do not belong to an iteration. Any returns issues that belong to an iteration. Mutually exclusive with iteration_title.
     *     @var string   $iteration_title          Return issues assigned to the iteration with the given title. Similar to iteration_id and mutually exclusive with iteration_id.
     *     @var string   $labels                   Comma-separated list of label names, issues must have all labels to be returned. None lists all issues with no labels. Any lists all issues with at least one label. No+Label (Deprecated) lists all issues with no labels. Predefined names are case-insensitive.
     *     @var string   $milestone                The milestone title. None lists all issues with no milestone. Any lists all issues that have an assigned milestone.
     *     @var string   $my_reaction_emoji        Return issues reacted by the authenticated user by the given emoji. None returns issues not given a reaction. Any returns issues given at least one reaction.
     *     @var bool     $non_archived             Return issues from non archived projects. Default is true.
     *     @var string   $not                      Return issues that do not match the parameters supplied. Accepts: labels, milestone, author_id, author_username, assignee_id, assignee_username, my_reaction_emoji, search, in
     *     @var string   $order_by                 Return issues ordered by created_at, updated_at, priority, due_date, relative_position, label_priority, milestone_due, popularity, weight fields. Default is created_at
     *     @var string   $scope                    Return issues for the given scope: created_by_me, assigned_to_me or all. Defaults to all.
     *     @var string   $search                   Search group issues against their title and description
     *     @var string   $sort                     Return issues sorted in asc or desc order. Default is desc
     *     @var string   $state                    Return all issues or just those that are opened or closed
     *     @var \DateTimeInterface $updated_after  Return issues updated on or after the given time. Expected in ISO 8601 format (2019-03-15T08:00:00Z)
     *     @var \DateTimeInterface $updated_before Return issues updated on or before the given time. Expected in ISO 8601 format (2019-03-15T08:00:00Z)
     *     @var int      $weight                   Return issues with the specified weight. None returns issues with no weight assigned. Any returns issues with a weight assigned.
     *     @var bool     $with_labels_details      If true, the response returns more details for each label in labels field: :name, :color, :description, :description_html, :text_color. Default is false.
     * }
     *
     * @return mixed
     */
    public function issues($group_id, array $parameters = [])
    {
        $resolver = $this->createOptionsResolver();
        $booleanNormalizer = function (Options $resolver, $value): string {
            return $value ? 'true' : 'false';
        };
        $datetimeNormalizer = function (Options $resolver, \DateTimeInterface $value): string {
            return $value->format('c');
        };

        $resolver->setDefined('assignee_id');
        $resolver->setDefined('assignee_username')
            ->setAllowedTypes('assignee_username', 'string');

        $resolver->setDefined('author_id');
        $resolver->setDefined('author_username')
            ->setAllowedTypes('author_username', 'string');

        $resolver->setDefined('confidential')
            ->setAllowedTypes('confidential', 'bool')
            ->setNormalizer('confidential', $booleanNormalizer);

        $resolver->setDefined('created_after')
            ->setAllowedTypes('created_after', \DateTimeInterface::class)
            ->setNormalizer('created_after', $datetimeNormalizer);
        $resolver->setDefined('created_before')
            ->setAllowedTypes('created_before', \DateTimeInterface::class)
            ->setNormalizer('created_before', $datetimeNormalizer);

        $resolver->setDefined('updated_after')
            ->setAllowedTypes('updated_after', \DateTimeInterface::class)
            ->setNormalizer('updated_after', $datetimeNormalizer);
        $resolver->setDefined('updated_before')
            ->setAllowedTypes('updated_before', \DateTimeInterface::class)
            ->setNormalizer('updated_before', $datetimeNormalizer);

        $resolver->setDefined('iteration_id');
        $resolver->setDefined('iteration_title')
            ->setAllowedTypes('iteration_title', 'string');

        $resolver->setDefined('labels')
            ->setAllowedTypes('labels', 'string');

        $resolver->setDefined('milestone')
            ->setAllowedTypes('milestone', 'string');

        $resolver->setDefined('my_reaction_emoji')
            ->setAllowedTypes('my_reaction_emoji', 'string');

        $resolver->setDefined('non_archived')
            ->setAllowedTypes('non_archived', 'bool')
            ->setNormalizer('non_archived', $booleanNormalizer);

        $resolver->setDefined('not')
            ->setAllowedTypes('not', 'string');

        $resolver->setDefined('order_by')
            ->setAllowedValues('order_by', ['created_at', 'updated_at']);
        $resolver->setDefined('sort')
            ->setAllowedValues('sort', ['asc', 'desc']);

        $resolver->setDefined('scope')
            ->setAllowedTypes('scope', 'string');

        $resolver->setDefined('search')
            ->setAllowedTypes('search', 'string');

        $resolver->setDefined('state')
            ->setAllowedValues('state', [self::STATE_ALL, self::STATE_OPENED, self::STATE_CLOSED]);

        $resolver->setDefined('weight');

        $resolver->setDefined('with_labels_details')
            ->setAllowedTypes('with_labels_details', 'bool')
            ->setNormalizer('with_labels_details', $booleanNormalizer);

        return $this->get('groups/'.self::encodePath($group_id).'/issues', $resolver->resolve($parameters));
    }

    /**
     * @param int|string $group_id
     * @param array      $parameters {
     *
     *     @var bool     $with_counts               Whether or not to include issue and merge request counts. Defaults to false.
     *     @var bool     $include_ancestor_groups   Include ancestor groups. Defaults to true.
     *     @var bool     $include_descendant_groups Include descendant groups. Defaults to false.
     *     @var bool     $only_group_labels         Toggle to include only group labels or also project labels. Defaults to true.
     *     @var string   $search                    Keyword to filter labels by.
     * }
     *
     * @return mixed
     */
    public function labels($group_id, array $parameters = [])
    {
        $resolver = $this->createOptionsResolver();

        $resolver->setDefined('with_counts')
            ->setAllowedTypes('with_counts', 'bool');

        $resolver->setDefined('include_ancestor_groups')
            ->setAllowedTypes('include_ancestor_groups', 'bool');

        $resolver->setDefined('include_descendant_groups')
            ->setAllowedTypes('include_descendant_groups', 'bool');

        $resolver->setDefined('only_group_labels')
            ->setAllowedTypes('only_group_labels', 'bool');

        $resolver->setDefined('search')
            ->setAllowedTypes('search', 'string');

        return $this->get('groups/'.self::encodePath($group_id).'/labels', $resolver->resolve($parameters));
    }

    /**
     * @param int|string $group_id
     * @param array      $params
     *
     * @return mixed
     */
    public function addLabel($group_id, array $params)
    {
        return $this->post('groups/'.self::encodePath($group_id).'/labels', $params);
    }

    /**
     * @param int|string $group_id
     * @param int        $label_id
     * @param array      $params
     *
     * @return mixed
     */
    public function updateLabel($group_id, int $label_id, array $params)
    {
        return $this->put('groups/'.self::encodePath($group_id).'/labels/'.self::encodePath($label_id), $params);
    }

    /**
     * @param int|string $group_id
     * @param int        $label_id
     *
     * @return mixed
     */
    public function removeLabel($group_id, int $label_id)
    {
        return $this->delete('groups/'.self::encodePath($group_id).'/labels/'.self::encodePath($label_id));
    }

    /**
     * @param int|string $group_id
     * @param array      $parameters
     *
     * @return mixed
     */
    public function variables($group_id, array $parameters = [])
    {
        $resolver = $this->createOptionsResolver();

        return $this->get('groups/'.self::encodePath($group_id).'/variables', $resolver->resolve($parameters));
    }

    /**
     * @param int|string $group_id
     * @param string     $key
     *
     * @return mixed
     */
    public function variable($group_id, string $key)
    {
        return $this->get('groups/'.self::encodePath($group_id).'/variables/'.self::encodePath($key));
    }

    /**
     * @param int|string $group_id
     * @param string     $key
     * @param string     $value
     * @param bool|null  $protected
     * @param array      $parameters {
     *
     *      @var string $masked         true or false
     *      @var string $variable_type  env_var (default) or file
     * }
     *
     * @return mixed
     */
    public function addVariable($group_id, string $key, string $value, ?bool $protected = null, array $parameters = [])
    {
        $payload = [
            'key' => $key,
            'value' => $value,
        ];

        if ($protected) {
            $payload['protected'] = $protected;
        }

        if (isset($parameters['masked'])) {
            $payload['masked'] = $parameters['masked'];
        }

        if (isset($parameters['variable_type'])) {
            $payload['variable_type'] = $parameters['variable_type'];
        }

        return $this->post('groups/'.self::encodePath($group_id).'/variables', $payload);
    }

    /**
     * @param int|string $group_id
     * @param string     $key
     * @param string     $value
     * @param bool|null  $protected
     *
     * @return mixed
     */
    public function updateVariable($group_id, string $key, string $value, ?bool $protected = null)
    {
        $payload = [
            'value' => $value,
        ];

        if ($protected) {
            $payload['protected'] = $protected;
        }

        return $this->put('groups/'.self::encodePath($group_id).'/variables/'.self::encodePath($key), $payload);
    }

    /**
     * @param int|string $group_id
     * @param string     $key
     *
     * @return mixed
     */
    public function removeVariable($group_id, string $key)
    {
        return $this->delete('groups/'.self::encodePath($group_id).'/variables/'.self::encodePath($key));
    }

    /**
     * @param int|string $group_id
     * @param array      $parameters {
     *
     *     @var int[]              $iids           return the request having the given iid
     *     @var string             $state          return all merge requests or just those that are opened, closed, or
     *                                             merged
     *     @var string             $scope          Return merge requests for the given scope: created-by-me,
     *                                             assigned-to-me or all (default is created-by-me)
     *     @var string             $order_by       return requests ordered by created_at or updated_at fields (default is created_at)
     *     @var string             $sort           return requests sorted in asc or desc order (default is desc)
     *     @var string             $milestone      return merge requests for a specific milestone
     *     @var string             $view           if simple, returns the iid, URL, title, description, and basic state of merge request
     *     @var string             $labels         return merge requests matching a comma separated list of labels
     *     @var \DateTimeInterface $created_after  return merge requests created after the given time (inclusive)
     *     @var \DateTimeInterface $created_before return merge requests created before the given time (inclusive)
     * }
     *
     * @return mixed
     */
    public function mergeRequests($group_id, array $parameters = [])
    {
        $resolver = $this->createOptionsResolver();
        $datetimeNormalizer = function (Options $resolver, \DateTimeInterface $value): string {
            return $value->format('c');
        };
        $resolver->setDefined('state')
            ->setAllowedValues('state', [self::STATE_ALL, self::STATE_MERGED, self::STATE_OPENED, self::STATE_CLOSED])
        ;
        $resolver->setDefined('order_by')
            ->setAllowedValues('order_by', ['created_at', 'updated_at'])
        ;
        $resolver->setDefined('sort')
            ->setAllowedValues('sort', ['asc', 'desc'])
        ;
        $resolver->setDefined('milestone');
        $resolver->setDefined('view')
            ->setAllowedValues('view', ['simple'])
        ;
        $resolver->setDefined('labels');
        $resolver->setDefined('with_labels_details')
            ->setAllowedTypes('with_labels_details', 'bool')
        ;

        $resolver->setDefined('created_after')
            ->setAllowedTypes('created_after', \DateTimeInterface::class)
            ->setNormalizer('created_after', $datetimeNormalizer)
        ;
        $resolver->setDefined('created_before')
            ->setAllowedTypes('created_before', \DateTimeInterface::class)
            ->setNormalizer('created_before', $datetimeNormalizer)
        ;

        $resolver->setDefined('updated_after')
            ->setAllowedTypes('updated_after', \DateTimeInterface::class)
            ->setNormalizer('updated_after', $datetimeNormalizer)
        ;
        $resolver->setDefined('updated_before')
            ->setAllowedTypes('updated_before', \DateTimeInterface::class)
            ->setNormalizer('updated_before', $datetimeNormalizer)
        ;

        $resolver->setDefined('scope')
            ->setAllowedValues('scope', ['created_by_me', 'assigned_to_me', 'all'])
        ;
        $resolver->setDefined('author_id')
            ->setAllowedTypes('author_id', 'integer');
        $resolver->setDefined('author_username');

        $resolver->setDefined('assignee_id')
            ->setAllowedTypes('assignee_id', 'integer');

        $resolver->setDefined('approver_ids')
            ->setAllowedTypes('approver_ids', 'array')
            ->setAllowedValues('approver_ids', function (array $value) {
                return \count($value) === \count(\array_filter($value, 'is_int'));
            })
        ;
        $resolver->setDefined('non_archived')
            ->setAllowedTypes('non_archived', 'bool')
        ;
        $resolver->setDefined('reviewer_id')
            ->setAllowedTypes('reviewer_id', 'integer');
        $resolver->setDefined('reviewer_username');
        $resolver->setDefined('my_reaction_emoji');

        $resolver->setDefined('search');
        $resolver->setDefined('source_branch');
        $resolver->setDefined('target_branch');
        $resolver->setDefined('with_merge_status_recheck')
            ->setAllowedTypes('with_merge_status_recheck', 'bool')
        ;
        $resolver->setDefined('approved_by_ids')
            ->setAllowedTypes('approved_by_ids', 'array')
            ->setAllowedValues('approved_by_ids', function (array $value) {
                return \count($value) === \count(\array_filter($value, 'is_int'));
            })
        ;

        return $this->get('groups/'.self::encodePath($group_id).'/merge_requests', $resolver->resolve($parameters));
    }

    /**
     * @param int|string $group_id
     * @param array      $parameters {
     *
     *     @var string $state               Return opened, upcoming, current (previously started), closed, or all iterations.
     *                                      Filtering by started state is deprecated starting with 14.1, please use current instead.
     *     @var string $search              return only iterations with a title matching the provided string
     *     @var bool   $include_ancestors   Include iterations from parent group and its ancestors. Defaults to true.
     * }
     *
     * @return mixed
     */
    public function iterations($group_id, array $parameters = [])
    {
        $resolver = $this->createOptionsResolver();
        $booleanNormalizer = function (Options $resolver, $value): string {
            return $value ? 'true' : 'false';
        };

        $resolver->setDefined('state')
            ->setAllowedValues('state', ['opened', 'upcoming', 'current', 'current (previously started)', 'closed', 'all'])
        ;
        $resolver->setDefined('include_ancestors')
            ->setAllowedTypes('include_ancestors', 'bool')
            ->setNormalizer('include_ancestors', $booleanNormalizer)
            ->setDefault('include_ancestors', true)
        ;

        return $this->get('groups/'.self::encodePath($group_id).'/iterations', $resolver->resolve($parameters));
    }

    /**
     * @param int|string $group_id
     * @param array      $parameters {
     *
     *     @var bool   $exclude_subgroups   if the parameter is included as true, packages from projects from subgroups
     *                                      are not listed. default is false.
     *     @var string $order_by            the field to use as order. one of created_at (default), name, version, type,
     *                                      or project_path.
     *     @var string $sort                the direction of the order, either asc (default) for ascending order
     *                                      or desc for descending order
     *     @var string $package_type        filter the returned packages by type. one of conan, maven, npm, pypi,
     *                                      composer, nuget, or golang.
     *     @var string $package_name        filter the project packages with a fuzzy search by name
     *     @var bool   $include_versionless when set to true, versionless packages are included in the response
     *     @var string $status              filter the returned packages by status. one of default (default),
     *                                      hidden, or processing.
     * }
     *
     * @return mixed
     */
    public function packages($group_id, array $parameters = [])
    {
        $resolver = $this->createOptionsResolver();
        $booleanNormalizer = function (Options $resolver, $value): string {
            return $value ? 'true' : 'false';
        };

        $resolver->setDefined('exclude_subgroups')
            ->setAllowedTypes('exclude_subgroups', 'bool')
            ->setNormalizer('exclude_subgroups', $booleanNormalizer)
        ;
        $resolver->setDefined('order_by')
            ->setAllowedValues('order_by', ['created_at', 'name', 'version', 'type'])
        ;
        $resolver->setDefined('sort')
            ->setAllowedValues('sort', ['asc', 'desc'])
        ;
        $resolver->setDefined('package_type')
            ->setAllowedValues('package_type', ['conan', 'maven', 'npm', 'pypi', 'composer', 'nuget', 'golang'])
        ;
        $resolver->setDefined('package_name');
        $resolver->setDefined('include_versionless')
            ->setAllowedTypes('include_versionless', 'bool')
            ->setNormalizer('include_versionless', $booleanNormalizer)
        ;
        $resolver->setDefined('status')
            ->setAllowedValues('status', ['default', 'hidden', 'processing'])
        ;

        return $this->get('groups/'.self::encodePath($group_id).'/packages', $resolver->resolve($parameters));
    }

    /**
     * @return OptionsResolver
     */
    private function getGroupSearchResolver()
    {
        $resolver = $this->getSubgroupSearchResolver();
        $booleanNormalizer = function (Options $resolver, $value): string {
            return $value ? 'true' : 'false';
        };

        $resolver->setDefined('top_level_only')
            ->setAllowedTypes('top_level_only', 'bool')
            ->setNormalizer('top_level_only', $booleanNormalizer)
        ;

        return $resolver;
    }

    /**
     * @return OptionsResolver
     */
    private function getSubgroupSearchResolver()
    {
        $resolver = $this->createOptionsResolver();
        $booleanNormalizer = function (Options $resolver, $value): string {
            return $value ? 'true' : 'false';
        };

        $resolver->setDefined('skip_groups')
            ->setAllowedTypes('skip_groups', 'array')
            ->setAllowedValues('skip_groups', function (array $value) {
                return \count($value) === \count(\array_filter($value, 'is_int'));
            })
        ;
        $resolver->setDefined('all_available')
            ->setAllowedTypes('all_available', 'bool')
            ->setNormalizer('all_available', $booleanNormalizer)
        ;
        $resolver->setDefined('search');
        $resolver->setDefined('order_by')
            ->setAllowedValues('order_by', ['name', 'path'])
        ;
        $resolver->setDefined('sort')
            ->setAllowedValues('sort', ['asc', 'desc'])
        ;
        $resolver->setDefined('statistics')
            ->setAllowedTypes('statistics', 'bool')
            ->setNormalizer('statistics', $booleanNormalizer)
        ;
        $resolver->setDefined('owned')
            ->setAllowedTypes('owned', 'bool')
            ->setNormalizer('owned', $booleanNormalizer)
        ;
        $resolver->setDefined('min_access_level')
            ->setAllowedValues('min_access_level', [null, 10, 20, 30, 40, 50])
        ;

        return $resolver;
    }

    /**
     * @param int|string $group_id
     * @param bool|null  $active
     *
     * @return mixed
     */
    public function deployTokens($group_id, bool $active = null)
    {
        return $this->get('groups/'.self::encodePath($group_id).'/deploy_tokens', (null !== $active) ? ['active' => $active] : []);
    }

    /**
     * @param int|string $group_id
     * @param array      $parameters {
     *
     *     @var string $name                    the name of the deploy token
     *     @var \DateTimeInterface $expires_at  expiration date for the deploy token, does not expire if no value is provided
     *     @var string $username                the username for the deploy token
     *     @var array  $scopes                  the scopes, one or many of: read_repository, read_registry, write_registry, read_package_registry, write_package_registry
     * }
     *
     * @return mixed
     */
    public function createDeployToken($group_id, array $parameters = [])
    {
        $resolver = $this->createOptionsResolver();
        $datetimeNormalizer = function (Options $resolver, \DateTimeInterface $value): string {
            return $value->format('c');
        };

        $resolver->define('name')
            ->required()
        ;

        $resolver->define('scopes')
            ->required()
            ->allowedTypes('array')
            ->allowedValues(function ($scopes) {
                $allowed = ['read_repository', 'read_registry', 'write_registry', 'read_package_registry', 'write_package_registry'];
                foreach ($scopes as $scope) {
                    if (!\in_array($scope, $allowed, true)) {
                        return false;
                    }
                }

                return true;
            })
        ;
        $resolver->setDefined('username')
            ->setAllowedTypes('username', 'string')
        ;

        $resolver->setDefined('expires_at')
            ->setAllowedTypes('expires_at', \DateTimeInterface::class)
            ->setNormalizer('expires_at', $datetimeNormalizer)
        ;

        return $this->post('groups/'.self::encodePath($group_id).'/deploy_tokens', $resolver->resolve($parameters));
    }

    /**
     * @param int|string $group_id
     * @param int        $token_id
     *
     * @return mixed
     */
    public function deleteDeployToken($group_id, int $token_id)
    {
        return $this->delete('groups/'.self::encodePath($group_id).'/deploy_tokens/'.self::encodePath($token_id));
    }

    /**
     * @param int|string $id
     * @param array $parameters {
     *
     *     @var string $scope        The scope to search in
     *     @var string $search       The search query
     *     @var string $state        Filter by state. Issues and merge requests are supported; it is ignored for other scopes.
     *     @var bool   $confidential Filter by confidentiality. Issues scope is supported; it is ignored for other scopes.
     *     @var string $order_by     Allowed values are created_at only. If this is not set, the results are either sorted by created_at in descending order for basic search, or by the most relevant documents when using advanced search.
     *     @var string $sort         Return projects sorted in asc or desc order (default is desc)
     * }
     *
     * @throws UndefinedOptionsException If an option name is undefined
     * @throws InvalidOptionsException   If an option doesn't fulfill the specified validation rules
     *
     * @return mixed
     */
    public function search($id, array $parameters = [])
    {
        $resolver = $this->createOptionsResolver();
        $booleanNormalizer = function (Options $resolver, $value): string {
            return $value ? 'true' : 'false';
        };
        $resolver->setDefined('confidential')
            ->setAllowedTypes('confidential', 'bool')
            ->setNormalizer('confidential', $booleanNormalizer);
        $scope = [
            'issues',
            'merge_requests',
            'milestones',
            'projects',
            'users',
            'blobs',
            'commits',
            'notes',
            'wiki_blobs',
        ];
        $resolver->setRequired('scope')
            ->setAllowedValues('scope', $scope);
        $resolver->setRequired('search');
        $resolver->setDefined('order_by')
            ->setAllowedValues('order_by', ['created_at']);
        $resolver->setDefined('sort')
            ->setAllowedValues('sort', ['asc', 'desc']);
        $resolver->setDefined('state')
            ->setAllowedValues('state', ['opened', 'closed']);

        return $this->get('groups/'.self::encodePath($id).'/search', $resolver->resolve($parameters));
    }
}
