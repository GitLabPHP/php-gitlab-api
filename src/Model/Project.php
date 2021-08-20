<?php

declare(strict_types=1);

namespace Gitlab\Model;

use Gitlab\Api\Projects;
use Gitlab\Api\Repositories;
use Gitlab\Client;

/**
 * @deprecated since version 10.1 and will be removed in 11.0.
 *
 * @property int|string            $id
 * @property string                $description
 * @property string                $default_branch
 * @property string                $visibility
 * @property string                $ssh_url_to_repo
 * @property string                $http_url_to_repo
 * @property string                $web_url
 * @property string                $readme_url
 * @property string[]              $tag_list
 * @property User|null             $owner
 * @property string                $name
 * @property string                $name_with_namespace
 * @property string                $path
 * @property string                $path_with_namespace
 * @property bool                  $issues_enabled
 * @property int                   $open_issues_count
 * @property bool                  $merge_requests_enabled
 * @property bool                  $jobs_enabled
 * @property bool                  $wiki_enabled
 * @property bool                  $snippets_enabled
 * @property bool                  $resolve_outdated_diff_discussions
 * @property bool                  $container_registry_enabled
 * @property string                $created_at
 * @property string                $last_activity_at
 * @property int                   $creator_id
 * @property ProjectNamespace|null $namespace
 * @property string                $import_status
 * @property bool                  $archived
 * @property string                $avatar_url
 * @property bool                  $shared_runners_enabled
 * @property int                   $forks_count
 * @property int                   $star_count
 * @property string                $runners_token
 * @property bool                  $public_jobs
 * @property Group[]|null          $shared_with_groups
 * @property bool                  $only_allow_merge_if_pipeline_succeeds
 * @property bool                  $only_allow_merge_if_all_discussions_are_resolved
 * @property bool                  $request_access_enabled
 * @property string                $merge_method
 * @property bool                  $approvals_before_merge
 */
final class Project extends AbstractModel
{
    /**
     * @var string[]
     */
    protected static $properties = [
        'id',
        'description',
        'default_branch',
        'visibility',
        'ssh_url_to_repo',
        'http_url_to_repo',
        'web_url',
        'readme_url',
        'tag_list',
        'owner',
        'name',
        'name_with_namespace',
        'path',
        'path_with_namespace',
        'issues_enabled',
        'open_issues_count',
        'merge_requests_enabled',
        'jobs_enabled',
        'wiki_enabled',
        'snippets_enabled',
        'resolve_outdated_diff_discussions',
        'container_registry_enabled',
        'created_at',
        'last_activity_at',
        'creator_id',
        'namespace',
        'import_status',
        'archived',
        'avatar_url',
        'shared_runners_enabled',
        'forks_count',
        'star_count',
        'runners_token',
        'public_jobs',
        'shared_with_groups',
        'only_allow_merge_if_pipeline_succeeds',
        'only_allow_merge_if_all_discussions_are_resolved',
        'request_access_enabled',
        'merge_method',
        'approvals_before_merge',
    ];

    /**
     * @param Client $client
     * @param array  $data
     *
     * @return Project
     */
    public static function fromArray(Client $client, array $data)
    {
        $project = new self($data['id']);
        $project->setClient($client);

        if (isset($data['owner'])) {
            $data['owner'] = User::fromArray($client, $data['owner']);
        }

        if (isset($data['namespace']) && \is_array($data['namespace'])) {
            $data['namespace'] = ProjectNamespace::fromArray($client, $data['namespace']);
        }

        if (isset($data['shared_with_groups'])) {
            $groups = [];
            foreach ($data['shared_with_groups'] as $group) {
                foreach ($group as $keys => $value) {
                    $group[\str_replace('group_', '', $keys)] = $value;
                    unset($group[$keys]);
                }
                $groups[] = Group::fromArray($client, $group);
            }
            $data['shared_with_groups'] = $groups;
        }

        return $project->hydrate($data);
    }

    /**
     * @param Client $client
     * @param string $name
     * @param array  $params
     *
     * @return Project
     */
    public static function create(Client $client, string $name, array $params = [])
    {
        $data = $client->projects()->create($name, $params);

        return self::fromArray($client, $data);
    }

    /**
     * @param int    $user_id
     * @param Client $client
     * @param string $name
     * @param array  $params
     *
     * @return Project
     */
    public static function createForUser(int $user_id, Client $client, string $name, array $params = [])
    {
        $data = $client->projects()->createForUser($user_id, $name, $params);

        return self::fromArray($client, $data);
    }

    /**
     * @param int|string|null $id
     * @param Client|null     $client
     *
     * @return void
     */
    public function __construct($id = null, Client $client = null)
    {
        parent::__construct();
        $this->setClient($client);
        $this->setData('id', $id);
    }

    /**
     * @return Project
     */
    public function show()
    {
        $data = $this->client->projects()->show($this->id);

        return self::fromArray($this->getClient(), $data);
    }

    /**
     * @param array $params
     *
     * @return Project
     */
    public function update(array $params)
    {
        $data = $this->client->projects()->update($this->id, $params);

        return self::fromArray($this->getClient(), $data);
    }

    /**
     * @return Project
     */
    public function archive()
    {
        $data = $this->client->projects()->archive($this->id);

        return self::fromArray($this->getClient(), $data);
    }

    /**
     * @return Project
     */
    public function unarchive()
    {
        $data = $this->client->projects()->unarchive($this->id);

        return self::fromArray($this->getClient(), $data);
    }

    /**
     * @return bool
     */
    public function remove()
    {
        $this->client->projects()->remove($this->id);

        return true;
    }

    /**
     * @param string|null $query
     *
     * @return User[]
     */
    public function members(?string $query = null)
    {
        $data = $this->client->projects()->members($this->id, null === $query ? [] : ['query' => $query]);

        $members = [];
        foreach ($data as $member) {
            $members[] = User::fromArray($this->getClient(), $member);
        }

        return $members;
    }

    /**
     * @param int $user_id
     *
     * @return User
     */
    public function member(int $user_id)
    {
        $data = $this->client->projects()->member($this->id, $user_id);

        return User::fromArray($this->getClient(), $data);
    }

    /**
     * @param int $user_id
     * @param int $access_level
     *
     * @return User
     */
    public function addMember(int $user_id, int $access_level)
    {
        $data = $this->client->projects()->addMember($this->id, $user_id, $access_level);

        return User::fromArray($this->getClient(), $data);
    }

    /**
     * @param int $user_id
     * @param int $access_level
     *
     * @return User
     */
    public function saveMember(int $user_id, int $access_level)
    {
        $data = $this->client->projects()->saveMember($this->id, $user_id, $access_level);

        return User::fromArray($this->getClient(), $data);
    }

    /**
     * @param int $user_id
     *
     * @return bool
     */
    public function removeMember(int $user_id)
    {
        $this->client->projects()->removeMember($this->id, $user_id);

        return true;
    }

    /**
     * @param array $parameters
     *
     * @return ProjectHook[]
     *
     * @see Projects::hooks() for available parameters.
     */
    public function hooks(array $parameters = [])
    {
        $data = $this->client->projects()->hooks($this->id, $parameters);

        $hooks = [];
        foreach ($data as $hook) {
            $hooks[] = ProjectHook::fromArray($this->getClient(), $this, $hook);
        }

        return $hooks;
    }

    /**
     * @param int $id
     *
     * @return ProjectHook
     */
    public function hook(int $id)
    {
        $hook = new ProjectHook($this, $id, $this->getClient());

        return $hook->show();
    }

    /**
     * @param string $url
     * @param array  $events
     *
     * @return ProjectHook
     */
    public function addHook(string $url, array $events = [])
    {
        $data = $this->client->projects()->addHook($this->id, $url, $events);

        return ProjectHook::fromArray($this->getClient(), $this, $data);
    }

    /**
     * @param int   $hook_id
     * @param array $params
     *
     * @return ProjectHook
     */
    public function updateHook(int $hook_id, array $params)
    {
        $hook = new ProjectHook($this, $hook_id, $this->getClient());

        return $hook->update($params);
    }

    /**
     * @param int $hook_id
     *
     * @return bool
     */
    public function removeHook(int $hook_id)
    {
        $hook = new ProjectHook($this, $hook_id, $this->getClient());

        return $hook->delete();
    }

    /**
     * @return Key[]
     */
    public function deployKeys()
    {
        $data = $this->client->projects()->deployKeys($this->id);

        $keys = [];
        foreach ($data as $key) {
            $keys[] = Key::fromArray($this->getClient(), $key);
        }

        return $keys;
    }

    /**
     * @param int $key_id
     *
     * @return Key
     */
    public function deployKey(int $key_id)
    {
        $data = $this->client->projects()->deployKey($this->id, $key_id);

        return Key::fromArray($this->getClient(), $data);
    }

    /**
     * @param string $title
     * @param string $key
     * @param bool   $canPush
     *
     * @return Key
     */
    public function addDeployKey(string $title, string $key, bool $canPush = false)
    {
        $data = $this->client->projects()->addDeployKey($this->id, $title, $key, $canPush);

        return Key::fromArray($this->getClient(), $data);
    }

    /**
     * @param int $key_id
     *
     * @return bool
     */
    public function deleteDeployKey(int $key_id)
    {
        $this->client->projects()->deleteDeployKey($this->id, $key_id);

        return true;
    }

    /**
     * @param int $key_id
     *
     * @return bool
     */
    public function enableDeployKey(int $key_id)
    {
        $this->client->projects()->enableDeployKey($this->id, $key_id);

        return true;
    }

    /**
     * @param string $name
     * @param string $ref
     *
     * @return Branch
     */
    public function createBranch(string $name, string $ref)
    {
        $data = $this->client->repositories()->createBranch($this->id, $name, $ref);

        return Branch::fromArray($this->getClient(), $this, $data);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function deleteBranch(string $name)
    {
        $this->client->repositories()->deleteBranch($this->id, $name);

        return true;
    }

    /**
     * @return Branch[]
     */
    public function branches()
    {
        $data = $this->client->repositories()->branches($this->id);

        $branches = [];
        foreach ($data as $branch) {
            $branches[] = Branch::fromArray($this->getClient(), $this, $branch);
        }

        return $branches;
    }

    /**
     * @param string $branch_name
     *
     * @return Branch
     */
    public function branch(string $branch_name)
    {
        $branch = new Branch($this, $branch_name);
        $branch->setClient($this->getClient());

        return $branch->show();
    }

    /**
     * @param string $branch_name
     * @param bool   $devPush
     * @param bool   $devMerge
     *
     * @return Branch
     */
    public function protectBranch(string $branch_name, bool $devPush = false, bool $devMerge = false)
    {
        $branch = new Branch($this, $branch_name);
        $branch->setClient($this->getClient());

        return $branch->protect($devPush, $devMerge);
    }

    /**
     * @param string $branch_name
     *
     * @return Branch
     */
    public function unprotectBranch(string $branch_name)
    {
        $branch = new Branch($this, $branch_name);
        $branch->setClient($this->getClient());

        return $branch->unprotect();
    }

    /**
     * @return Tag[]
     */
    public function tags()
    {
        $data = $this->client->repositories()->tags($this->id);

        $tags = [];
        foreach ($data as $tag) {
            $tags[] = Tag::fromArray($this->getClient(), $this, $tag);
        }

        return $tags;
    }

    /**
     * @param array $parameters
     *
     * @return Commit[]
     *
     * @see Repositories::commits() for available parameters.
     */
    public function commits(array $parameters = [])
    {
        $data = $this->client->repositories()->commits($this->id, $parameters);

        $commits = [];
        foreach ($data as $commit) {
            $commits[] = Commit::fromArray($this->getClient(), $this, $commit);
        }

        return $commits;
    }

    /**
     * @param string $sha
     *
     * @return Commit
     */
    public function commit(string $sha)
    {
        $data = $this->client->repositories()->commit($this->id, $sha);

        return Commit::fromArray($this->getClient(), $this, $data);
    }

    /**
     * @param string $ref
     * @param array  $parameters
     *
     * @return CommitNote[]
     *
     * @see Repositories::commitComments() for available parameters.
     */
    public function commitComments(string $ref, array $parameters = [])
    {
        $data = $this->client->repositories()->commitComments($this->id, $ref, $parameters);

        $comments = [];
        foreach ($data as $comment) {
            $comments[] = CommitNote::fromArray($this->getClient(), $comment);
        }

        return $comments;
    }

    /**
     * @param string $ref
     * @param string $note
     * @param array  $params
     *
     * @return CommitNote
     */
    public function createCommitComment(string $ref, string $note, array $params = [])
    {
        $data = $this->client->repositories()->createCommitComment($this->id, $ref, $note, $params);

        return CommitNote::fromArray($this->getClient(), $data);
    }

    /**
     * @param string $sha
     *
     * @return string
     */
    public function diff(string $sha)
    {
        return $this->client->repositories()->diff($this->id, $sha);
    }

    /**
     * @param string $from
     * @param string $to
     *
     * @return Comparison
     */
    public function compare(string $from, string $to)
    {
        $data = $this->client->repositories()->compare($this->id, $from, $to);

        return Comparison::fromArray($this->getClient(), $this, $data);
    }

    /**
     * @param array $params
     *
     * @return Node[]
     */
    public function tree(array $params = [])
    {
        $data = $this->client->repositories()->tree($this->id, $params);

        $tree = [];
        foreach ($data as $node) {
            $tree[] = Node::fromArray($this->getClient(), $this, $node);
        }

        return $tree;
    }

    /**
     * @param string $sha
     * @param string $filepath
     *
     * @return string
     */
    public function getRawFile(string $sha, string $filepath)
    {
        return $this->client->repositoryFiles()->getRawFile($this->id, $sha, $filepath);
    }

    /**
     * @param string $sha
     * @param string $filepath
     *
     * @return array
     */
    public function getFile(string $sha, string $filepath)
    {
        return $this->client->repositoryFiles()->getFile($this->id, $filepath, $sha);
    }

    /**
     * @param string      $file_path
     * @param string      $content
     * @param string      $branch_name
     * @param string      $commit_message
     * @param string|null $author_email
     * @param string|null $author_name
     *
     * @return File
     */
    public function createFile(
        string $file_path,
        string $content,
        string $branch_name,
        string $commit_message,
        ?string $author_email = null,
        ?string $author_name = null
    ) {
        $parameters = [
            'file_path' => $file_path,
            'branch' => $branch_name,
            'content' => $content,
            'commit_message' => $commit_message,
        ];

        if (null !== $author_email) {
            $parameters['author_email'] = $author_email;
        }

        if (null !== $author_name) {
            $parameters['author_name'] = $author_name;
        }

        $data = $this->client->repositoryFiles()->createFile($this->id, $parameters);

        return File::fromArray($this->getClient(), $this, $data);
    }

    /**
     * @param string      $file_path
     * @param string      $content
     * @param string      $branch_name
     * @param string      $commit_message
     * @param string|null $author_email
     * @param string|null $author_name
     *
     * @return File
     */
    public function updateFile(
        string $file_path,
        string $content,
        string $branch_name,
        string $commit_message,
        ?string $author_email = null,
        ?string $author_name = null
    ) {
        $parameters = [
            'file_path' => $file_path,
            'branch' => $branch_name,
            'content' => $content,
            'commit_message' => $commit_message,
        ];

        if (null !== $author_email) {
            $parameters['author_email'] = $author_email;
        }

        if (null !== $author_name) {
            $parameters['author_name'] = $author_name;
        }

        $data = $this->client->repositoryFiles()->updateFile($this->id, $parameters);

        return File::fromArray($this->getClient(), $this, $data);
    }

    /**
     * @param string      $file_path
     * @param string      $branch_name
     * @param string      $commit_message
     * @param string|null $author_email
     * @param string|null $author_name
     *
     * @return bool
     */
    public function deleteFile(string $file_path, string $branch_name, string $commit_message, ?string $author_email = null, ?string $author_name = null)
    {
        $parameters = [
            'file_path' => $file_path,
            'branch' => $branch_name,
            'commit_message' => $commit_message,
        ];

        if (null !== $author_email) {
            $parameters['author_email'] = $author_email;
        }

        if (null !== $author_name) {
            $parameters['author_name'] = $author_name;
        }

        $this->client->repositoryFiles()->deleteFile($this->id, $parameters);

        return true;
    }

    /**
     * @param array $parameters
     *
     * @return Event[]
     *
     * @see Projects::events() for available parameters.
     */
    public function events(array $parameters = [])
    {
        $data = $this->client->projects()->events($this->id, $parameters);

        $events = [];
        foreach ($data as $event) {
            $events[] = Event::fromArray($this->getClient(), $this, $event);
        }

        return $events;
    }

    /**
     * @param array $parameters
     *
     * @return MergeRequest[]
     *
     * @see MergeRequests::all() for available parameters.
     */
    public function mergeRequests(array $parameters = [])
    {
        $data = $this->client->mergeRequests()->all($this->id, $parameters);

        $mrs = [];
        foreach ($data as $mr) {
            $mrs[] = MergeRequest::fromArray($this->getClient(), $this, $mr);
        }

        return $mrs;
    }

    /**
     * @param int $id
     *
     * @return MergeRequest
     */
    public function mergeRequest(int $id)
    {
        $mr = new MergeRequest($this, $id, $this->getClient());

        return $mr->show();
    }

    /**
     * @param string $source
     * @param string $target
     * @param string $title
     * @param array  $parameters {
     *
     *     @var int    $assignee
     *     @var string $description
     * }
     *
     * @return MergeRequest
     */
    public function createMergeRequest(string $source, string $target, string $title, array $parameters = [])
    {
        $parameters['target_project_id'] = $this->id;

        $data = $this->client->mergeRequests()->create(
            $this->id,
            $source,
            $target,
            $title,
            $parameters
        );

        return MergeRequest::fromArray($this->getClient(), $this, $data);
    }

    /**
     * @param int   $id
     * @param array $params
     *
     * @return MergeRequest
     */
    public function updateMergeRequest(int $id, array $params)
    {
        $mr = new MergeRequest($this, $id, $this->getClient());

        return $mr->update($params);
    }

    /**
     * @param int $id
     *
     * @return MergeRequest
     */
    public function closeMergeRequest(int $id)
    {
        $mr = new MergeRequest($this, $id, $this->getClient());

        return $mr->close();
    }

    /**
     * @param int $id
     *
     * @return MergeRequest
     */
    public function openMergeRequest(int $id)
    {
        $mr = new MergeRequest($this, $id, $this->getClient());

        return $mr->reopen();
    }

    /**
     * @param int $id
     *
     * @return MergeRequest
     */
    public function mergeMergeRequest(int $id)
    {
        $mr = new MergeRequest($this, $id, $this->getClient());

        return $mr->merge();
    }

    /**
     * @param array $parameters
     *
     * @return Issue[]
     *
     * @see Issues::all() for available parameters.
     */
    public function issues(array $parameters = [])
    {
        $data = $this->client->issues()->all($this->id, $parameters);

        $issues = [];
        foreach ($data as $issue) {
            $issues[] = Issue::fromArray($this->getClient(), $this, $issue);
        }

        return $issues;
    }

    /**
     * @param string $title
     * @param array  $params
     *
     * @return Issue
     */
    public function createIssue(string $title, array $params = [])
    {
        $params['title'] = $title;
        $data = $this->client->issues()->create($this->id, $params);

        return Issue::fromArray($this->getClient(), $this, $data);
    }

    /**
     * @param int $iid
     *
     * @return Issue
     */
    public function issue(int $iid)
    {
        $issue = new Issue($this, $iid, $this->getClient());

        return $issue->show();
    }

    /**
     * @param int   $iid
     * @param array $params
     *
     * @return Issue
     */
    public function updateIssue(int $iid, array $params)
    {
        $issue = new Issue($this, $iid, $this->getClient());

        return $issue->update($params);
    }

    /**
     * @param int         $iid
     * @param string|null $comment
     *
     * @return Issue
     */
    public function closeIssue(int $iid, ?string $comment = null)
    {
        $issue = new Issue($this, $iid, $this->getClient());

        return $issue->close($comment);
    }

    /**
     * @param int $iid
     *
     * @return Issue
     */
    public function openIssue(int $iid)
    {
        $issue = new Issue($this, $iid, $this->getClient());

        return $issue->open();
    }

    /**
     * @param array $parameters
     *
     * @return Milestone[]
     *
     * @see Milestones::all() for available parameters.
     */
    public function milestones(array $parameters = [])
    {
        $data = $this->client->milestones()->all($this->id, $parameters);

        $milestones = [];
        foreach ($data as $milestone) {
            $milestones[] = Milestone::fromArray($this->getClient(), $this, $milestone);
        }

        return $milestones;
    }

    /**
     * @param string $title
     * @param array  $params
     *
     * @return Milestone
     */
    public function createMilestone(string $title, array $params = [])
    {
        $params['title'] = $title;
        $data = $this->client->milestones()->create($this->id, $params);

        return Milestone::fromArray($this->getClient(), $this, $data);
    }

    /**
     * @param int $id
     *
     * @return Milestone
     */
    public function milestone(int $id)
    {
        $milestone = new Milestone($this, $id, $this->getClient());

        return $milestone->show();
    }

    /**
     * @param int   $id
     * @param array $params
     *
     * @return Milestone
     */
    public function updateMilestone(int $id, array $params)
    {
        $milestone = new Milestone($this, $id, $this->getClient());

        return $milestone->update($params);
    }

    /**
     * @param int $id
     *
     * @return Issue[]
     */
    public function milestoneIssues(int $id)
    {
        $milestone = new Milestone($this, $id, $this->getClient());

        return $milestone->issues();
    }

    /**
     * @return Snippet[]
     */
    public function snippets()
    {
        $data = $this->client->snippets()->all($this->id);

        $snippets = [];
        foreach ($data as $snippet) {
            $snippets[] = Snippet::fromArray($this->getClient(), $this, $snippet);
        }

        return $snippets;
    }

    /**
     * @param string $title
     * @param string $filename
     * @param string $code
     * @param string $visibility
     *
     * @return Snippet
     */
    public function createSnippet(string $title, string $filename, string $code, string $visibility)
    {
        $data = $this->client->snippets()->create($this->id, $title, $filename, $code, $visibility);

        return Snippet::fromArray($this->getClient(), $this, $data);
    }

    /**
     * @param int $id
     *
     * @return Snippet
     */
    public function snippet(int $id)
    {
        $snippet = new Snippet($this, $id, $this->getClient());

        return $snippet->show();
    }

    /**
     * @param int $id
     *
     * @return string
     */
    public function snippetContent(int $id)
    {
        $snippet = new Snippet($this, $id, $this->getClient());

        return $snippet->content();
    }

    /**
     * @param int   $id
     * @param array $params
     *
     * @return Snippet
     */
    public function updateSnippet(int $id, array $params)
    {
        $snippet = new Snippet($this, $id, $this->getClient());

        return $snippet->update($params);
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function removeSnippet(int $id)
    {
        $snippet = new Snippet($this, $id, $this->getClient());

        return $snippet->remove();
    }

    /**
     * @param int $group_id
     *
     * @return Group
     */
    public function transfer(int $group_id)
    {
        $group = new Group($group_id, $this->getClient());

        return $group->transfer($this->id);
    }

    /**
     * @param int $id
     *
     * @return Project
     */
    public function forkTo(int $id)
    {
        $data = $this->client->projects()->createForkRelation($id, $this->id);

        return self::fromArray($this->getClient(), $data);
    }

    /**
     * @param int $id
     *
     * @return Project
     */
    public function forkFrom(int $id)
    {
        return $this->createForkRelation($id);
    }

    /**
     * @param int $id
     *
     * @return Project
     */
    public function createForkRelation(int $id)
    {
        $data = $this->client->projects()->createForkRelation($this->id, $id);

        return self::fromArray($this->getClient(), $data);
    }

    /**
     * @return bool
     */
    public function removeForkRelation()
    {
        $this->client->projects()->removeForkRelation($this->id);

        return true;
    }

    /**
     * @param string $service_name
     * @param array  $params
     *
     * @return bool
     */
    public function setService(string $service_name, array $params = [])
    {
        $this->client->projects()->setService($this->id, $service_name, $params);

        return true;
    }

    /**
     * @param string $service_name
     *
     * @return bool
     */
    public function removeService(string $service_name)
    {
        $this->client->projects()->removeService($this->id, $service_name);

        return true;
    }

    /**
     * @return Label[]
     */
    public function labels()
    {
        $data = $this->client->projects()->labels($this->id);

        $labels = [];
        foreach ($data as $label) {
            $labels[] = Label::fromArray($this->getClient(), $this, $label);
        }

        return $labels;
    }

    /**
     * @param string $name
     * @param string $color
     *
     * @return Label
     */
    public function addLabel(string $name, string $color)
    {
        $data = $this->client->projects()->addLabel($this->id, [
            'name' => $name,
            'color' => $color,
        ]);

        return Label::fromArray($this->getClient(), $this, $data);
    }

    /**
     * @param string $name
     * @param array  $params
     *
     * @return Label
     */
    public function updateLabel(string $name, array $params)
    {
        if (isset($params['name'])) {
            $params['new_name'] = $params['name'];
        }

        $params['name'] = $name;

        $data = $this->client->projects()->updateLabel($this->id, $params);

        return Label::fromArray($this->getClient(), $this, $data);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function removeLabel(string $name)
    {
        $this->client->projects()->removeLabel($this->id, $name);

        return true;
    }

    /**
     * @return array
     */
    public function contributors()
    {
        $data = $this->client->repositories()->contributors($this->id);

        $contributors = [];
        foreach ($data as $contributor) {
            $contributors[] = Contributor::fromArray($this->getClient(), $this, $contributor);
        }

        return $contributors;
    }

    /**
     * @return Trigger[]
     */
    public function triggers()
    {
        $data = $this->client->projects()->triggers($this->id);

        $triggers = [];
        foreach ($data as $triggerData) {
            $triggers[] = Trigger::fromArray($this->client, $this, $triggerData);
        }

        return $triggers;
    }

    /**
     * @param int $id
     *
     * @return Trigger
     */
    public function trigger(int $id)
    {
        $trigger = new Trigger($this, $id, $this->client);

        return $trigger->show();
    }

    /**
     * @param string $description
     *
     * @return Trigger
     */
    public function createTrigger(string $description)
    {
        $data = $this->client->projects()->createTrigger($this->id, $description);

        return Trigger::fromArray($this->client, $this, $data);
    }

    /**
     * @param int $id
     *
     * @return Pipeline
     */
    public function pipeline(int $id)
    {
        $pipeline = new Pipeline($this, $id, $this->client);

        return $pipeline->show();
    }

    /**
     * @param int|string $project_id
     * @param array      $parameters {
     *
     *     @var string $scope       the scope of pipelines, one of: running, pending, finished, branches, tags
     *     @var string $status      the status of pipelines, one of: running, pending, success, failed, canceled, skipped
     *     @var string $ref         the ref of pipelines
     *     @var string $sha         the sha of pipelines
     *     @var bool   $yaml_errors returns pipelines with invalid configurations
     *     @var string $name        the name of the user who triggered pipelines
     *     @var string $username    the username of the user who triggered pipelines
     *     @var string $order_by    order pipelines by id, status, ref, or user_id (default: id)
     *     @var string $order       Sort pipelines in asc or desc order (default: desc).
     * }
     *
     * @return Pipeline[]
     */
    public function pipelines(array $parameters = [])
    {
        $data = $this->client->projects()->pipelines($this->id, $parameters);

        $pipelines = [];
        foreach ($data as $pipelineData) {
            $pipelines[] = Pipeline::fromArray($this->client, $this, $pipelineData);
        }

        return $pipelines;
    }

    /**
     * @param array $scopes
     *
     * @return Job[]
     */
    public function jobs(array $scopes = [])
    {
        $data = $this->client->jobs()->all($this->id, $scopes);

        $jobs = [];
        foreach ($data as $job) {
            $jobs[] = Job::fromArray($this->getClient(), $this, $job);
        }

        return $jobs;
    }

    /**
     * @param int   $pipeline_id
     * @param array $scopes
     *
     * @return Job[]
     */
    public function pipelineJobs(int $pipeline_id, array $scopes = [])
    {
        $data = $this->client->jobs()->pipelineJobs($this->id, $pipeline_id, $scopes);

        $jobs = [];
        foreach ($data as $job) {
            $jobs[] = Job::fromArray($this->getClient(), $this, $job);
        }

        return $jobs;
    }

    /**
     * @param int $job_id
     *
     * @return Job
     */
    public function job(int $job_id)
    {
        $data = $this->client->jobs()->show($this->id, $job_id);

        return Job::fromArray($this->getClient(), $this, $data);
    }

    /**
     * @return Badge[]
     */
    public function badges()
    {
        $data = $this->client->projects()->badges($this->id);

        $badges = [];
        foreach ($data as $badge) {
            $badges[] = Badge::fromArray($this->getClient(), $this, $badge);
        }

        return $badges;
    }

    /**
     * @param array $params
     *
     * @return Badge
     */
    public function addBadge(array $params)
    {
        $data = $this->client->projects()->addBadge($this->id, $params);

        return Badge::fromArray($this->getClient(), $this, $data);
    }

    /**
     * @param int   $badge_id
     * @param array $params
     *
     * @return Badge
     */
    public function updateBadge(int $badge_id, array $params)
    {
        $params['badge_id'] = $badge_id;

        $data = $this->client->projects()->updateBadge($this->id, $badge_id, $params);

        return Badge::fromArray($this->getClient(), $this, $data);
    }

    /**
     * @param int $badge_id
     *
     * @return bool
     */
    public function removeBadge(int $badge_id)
    {
        $this->client->projects()->removeBadge($this->id, $badge_id);

        return true;
    }

    /**
     * @param array $params
     *
     * @return Branch
     */
    public function addProtectedBranch(array $params = [])
    {
        $data = $this->client->projects()->addProtectedBranch($this->id, $params);

        return Branch::fromArray($this->getClient(), $this, $data);
    }
}
