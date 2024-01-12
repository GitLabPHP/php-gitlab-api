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

namespace Gitlab\Tests\Api;

use Gitlab\Api\Repositories;

class RepositoriesTest extends TestCase
{
    /**
     * @test
     */
    public function shouldGetBranches(): void
    {
        $expectedArray = [
            ['name' => 'master'],
            ['name' => 'develop'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('projects/1/repository/branches', ['search' => '^term'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->branches(1, ['search' => '^term']));
    }

    /**
     * @test
     */
    public function shouldGetBranch(): void
    {
        $expectedArray = ['name' => 'master'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('projects/1/repository/branches/master')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->branch(1, 'master'));
    }

    /**
     * @test
     */
    public function shouldCreateBranch(): void
    {
        $expectedArray = ['name' => 'feature'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with('projects/1/repository/branches', ['branch' => 'feature', 'ref' => 'master'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->createBranch(1, 'feature', 'master'));
    }

    /**
     * @test
     */
    public function shouldDeleteBranch(): void
    {
        $expectedBool = true;

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('delete')
            ->with('projects/1/repository/branches/feature%2FTEST-15')
            ->will($this->returnValue($expectedBool))
        ;

        $this->assertEquals($expectedBool, $api->deleteBranch(1, 'feature/TEST-15'));
    }

    /**
     * @test
     */
    public function shouldProtectBranch(): void
    {
        $expectedArray = ['name' => 'master'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('put')
            ->with('projects/1/repository/branches/master/protect', ['developers_can_push' => false, 'developers_can_merge' => false])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->protectBranch(1, 'master'));
    }

    /**
     * @test
     */
    public function shouldProtectBranchWithPermissions(): void
    {
        $expectedArray = ['name' => 'master'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('put')
            ->with('projects/1/repository/branches/master/protect', ['developers_can_push' => true, 'developers_can_merge' => true])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->protectBranch(1, 'master', true, true));
    }

    /**
     * @test
     */
    public function shouldUnprotectBranch(): void
    {
        $expectedArray = ['name' => 'master'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('put')
            ->with('projects/1/repository/branches/master/unprotect')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->unprotectBranch(1, 'master'));
    }

    /**
     * @test
     */
    public function shouldGetTags(): void
    {
        $expectedArray = [
            ['name' => '1.0'],
            ['name' => '1.1'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('projects/1/repository/tags')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->tags(1, ['search' => '^term']));
    }

    /**
     * @test
     */
    public function shouldCreateTag(): void
    {
        $expectedArray = ['name' => '1.0'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with('projects/1/repository/tags', [
                'tag_name' => '1.0',
                'ref' => 'abcd1234',
                'message' => '1.0 release',
            ])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->createTag(1, '1.0', 'abcd1234', '1.0 release'));
    }

    /**
     * @test
     */
    public function shouldCreateRelease(): void
    {
        $project_id = 1;
        $tagName = 'sometag';
        $description = '1.0 release';

        $expectedArray = ['name' => $tagName];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with('projects/'.$project_id.'/releases', [
                'id' => $project_id,
                'tag_name' => $tagName,
                'description' => $description,
            ])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->createRelease($project_id, $tagName, $description));
    }

    /**
     * @test
     */
    public function shouldUpdateRelease(): void
    {
        $project_id = 1;
        $tagName = 'sometag';
        $description = '1.0 release';

        $expectedArray = ['description' => $tagName];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('put')
            ->with('projects/'.$project_id.'/releases/'.$tagName, [
                'id' => $project_id,
                'tag_name' => $tagName,
                'description' => $description,
            ])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->updateRelease($project_id, $tagName, $description));
    }

    /**
     * @test
     */
    public function shouldGetReleases(): void
    {
        $project_id = 1;

        $expectedArray = [
            [
                'tag_name' => 'v0.2',
                'description' => '## CHANGELOG\r\n\r\n- Escape label and milestone titles to prevent XSS in GFM autocomplete. !2740\r\n- Prevent private snippets from being embeddable.\r\n- Add subresources removal to member destroy service.',
                'name' => 'Awesome app v0.2 beta',
            ],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('projects/1/releases')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->releases($project_id));
    }

    /**
     * @test
     */
    public function shouldGetCommits(): void
    {
        $expectedArray = [
            ['id' => 'abcd1234', 'title' => 'A commit'],
            ['id' => 'efgh5678', 'title' => 'Another commit'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('projects/1/repository/commits', [])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->commits(1));
    }

    /**
     * @test
     */
    public function shouldGetCommitsWithParams(): void
    {
        $expectedArray = [
            ['id' => 'abcd1234', 'title' => 'A commit'],
            ['id' => 'efgh5678', 'title' => 'Another commit'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('projects/1/repository/commits', ['page' => 2, 'per_page' => 25, 'ref_name' => 'master', 'all' => 'true', 'with_stats' => 'true', 'path' => 'file_path/file_name'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->commits(1, ['page' => 2, 'per_page' => 25, 'ref_name' => 'master', 'all' => true, 'with_stats' => true, 'path' => 'file_path/file_name']));
    }

    /**
     * @test
     */
    public function shouldGetCommitsWithTimeParams(): void
    {
        $expectedArray = [
            ['id' => 'abcd1234', 'title' => 'A commit'],
            ['id' => 'efgh5678', 'title' => 'Another commit'],
        ];

        $since = new \DateTime('2018-01-01 00:00:00');
        $until = new \DateTime('2018-01-31 00:00:00');

        $expectedWithArray = [
            'since' => $since->format(\DATE_ATOM),
            'until' => $until->format(\DATE_ATOM),
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('projects/1/repository/commits', $expectedWithArray)
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->commits(1, ['since' => $since, 'until' => $until]));
    }

    /**
     * @test
     */
    public function shouldGetCommit(): void
    {
        $expectedArray = ['id' => 'abcd1234', 'title' => 'A commit'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('projects/1/repository/commits/abcd1234')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->commit(1, 'abcd1234'));
    }

    /**
     * @test
     */
    public function shouldGetCommitRefs(): void
    {
        $expectedArray = [
            ['type' => 'branch', 'name' => 'master'],
            ['type' => 'tag', 'name' => 'v1.1.0'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('projects/1/repository/commits/abcd1234/refs')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->commitRefs(1, 'abcd1234'));
    }

    /**
     * @test
     */
    public function shouldGetCommitMergeRequests(): void
    {
        $expectedArray = [
            ['id' => 1, 'title' => 'A merge request'],
            ['id' => 2, 'title' => 'Another merge request'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('projects/1/repository/commits/abcd1234/merge_requests')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->commitMergeRequests(1, 'abcd1234'));
    }

    /**
     * @test
     *
     * @dataProvider dataGetCommitRefsWithParams
     *
     * @param string $type
     * @param array  $expectedArray
     */
    public function shouldGetCommitRefsWithParams(string $type, array $expectedArray): void
    {
        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('projects/1/repository/commits/abcd1234/refs', ['type' => $type])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->commitRefs(1, 'abcd1234', ['type' => $type]));
    }

    public static function dataGetCommitRefsWithParams(): array
    {
        return [
            'type_tag' => [
                'type' => Repositories::TYPE_TAG,
                'expectedArray' => [['type' => 'tag', 'name' => 'v1.1.0']],
            ],
            'type_branch' => [
                'type' => Repositories::TYPE_BRANCH,
                'expectedArray' => [['type' => 'branch', 'name' => 'master']],
            ],
        ];
    }

    /**
     * @test
     */
    public function shouldCreateCommit(): void
    {
        $expectedArray = ['title' => 'Initial commit.', 'author_name' => 'John Doe', 'author_email' => 'john@example.com'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with('projects/1/repository/commits')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->createCommit(1, [
            'branch' => 'master',
            'commit_message' => 'Initial commit.',
            'actions' => [
                [
                    'action' => 'create',
                    'file_path' => 'README.md',
                    'content' => '# My new project',
                ],
                [
                    'action' => 'create',
                    'file_path' => 'LICENSE',
                    'content' => 'MIT License...',
                ],
            ],
            'author_name' => 'John Doe',
            'author_email' => 'john@example.com',
        ]));
    }

    /**
     * @test
     */
    public function shouldRevertCommit(): void
    {
        $expectedArray = ['title' => 'Initial commit.', 'author_name' => 'John Doe', 'author_email' => 'john@example.com'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with('projects/1/repository/commits/abcd1234/revert')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->revertCommit(1, 'develop', 'abcd1234'));
    }

    /**
     * @test
     */
    public function shouldGetCommitComments(): void
    {
        $expectedArray = [
            ['note' => 'A commit message'],
            ['note' => 'Another commit message'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('projects/1/repository/commits/abcd1234/comments')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->commitComments(1, 'abcd1234'));
    }

    /**
     * @test
     */
    public function shouldCreateCommitComment(): void
    {
        $expectedArray = ['id' => 2, 'title' => 'A new comment'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with('projects/1/repository/commits/abcd1234/comments', ['note' => 'A new comment'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->createCommitComment(1, 'abcd1234', 'A new comment'));
    }

    /**
     * @test
     */
    public function shouldCreateCommitCommentWithParams(): void
    {
        $expectedArray = ['id' => 2, 'title' => 'A new comment'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with('projects/1/repository/commits/abcd1234/comments', [
                'note' => 'A new comment',
                'path' => '/some/file.txt',
                'line' => 123, 'line_type' => 'old',
            ])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->createCommitComment(1, 'abcd1234', 'A new comment', [
            'path' => '/some/file.txt',
            'line' => 123,
            'line_type' => 'old',
        ]));
    }

    /**
     * @test
     */
    public function shouldCompareStraight(): void
    {
        $expectedArray = ['commit' => 'object'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('projects/1/repository/compare', ['from' => 'master', 'to' => 'feature', 'straight' => 'true'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->compare(1, 'master', 'feature', true));
    }

    /**
     * @test
     */
    public function shouldNotCompareStraight(): void
    {
        $expectedArray = ['commit' => 'object'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('projects/1/repository/compare', ['from' => 'master', 'to' => 'feature', 'straight' => 'false'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->compare(1, 'master', 'feature'));
    }

    /**
     * @test
     */
    public function shouldCompareComplexBranchName(): void
    {
        $expectedArray = ['commit' => 'object'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('projects/1/repository/compare', ['from' => 'master', 'to' => 'feature/760.fake-branch', 'straight' => 'true'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->compare(1, 'master', 'feature/760.fake-branch', true));
    }

    /**
     * @test
     */
    public function shouldCompareWithFromProjectId(): void
    {
        $expectedArray = ['commit' => 'object'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('projects/1/repository/compare', ['from' => 'master', 'to' => 'feature', 'straight' => 'true', 'from_project_id' => '123'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->compare(1, 'master', 'feature', true, '123'));
    }

    /**
     * @test
     */
    public function shouldGetDiff(): void
    {
        $expectedArray = [
            ['diff' => '--- ...'],
            ['diff' => '+++ ...'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('projects/1/repository/commits/abcd1234/diff')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->diff(1, 'abcd1234'));
    }

    /**
     * @test
     */
    public function shouldGetTree(): void
    {
        $expectedArray = [
            ['name' => 'file1.txt'],
            ['name' => 'file2.csv'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('projects/1/repository/tree')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->tree(1));
    }

    /**
     * @test
     */
    public function shouldGetTreeWithParams(): void
    {
        $expectedArray = [
            ['name' => 'dir/file1.txt'],
            ['name' => 'dir/file2.csv'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('projects/1/repository/tree', ['path' => 'dir/', 'ref_name' => 'master'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->tree(1, ['path' => 'dir/', 'ref_name' => 'master']));
    }

    /**
     * @test
     */
    public function shouldGetContributors(): void
    {
        $expectedArray = [
            ['name' => 'Matt'],
            ['name' => 'Bob'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('projects/1/repository/contributors')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->contributors(1));
    }

    /**
     * @test
     */
    public function shouldGetMergeBase(): void
    {
        $expectedArray = [
            'id' => 'abcd1234abcd1234abcd1234abcd1234abcd1234',
            'short_id' => 'abcd1234',
            'title' => 'A commit',
            'created_at' => '2018-01-01T00:00:00.000Z',
            'parent_ids' => [
                'efgh5678efgh5678efgh5678efgh5678efgh5678',
            ],
            'message' => 'A commit',
            'author_name' => 'Jane Doe',
            'author_email' => 'jane@example.org',
            'authored_date' => '2018-01-01T00:00:00.000Z',
            'committer_name' => 'Jane Doe',
            'committer_email' => 'jane@example.org',
            'committed_date' => '2018-01-01T00:00:00.000Z',
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('projects/1/repository/merge_base', ['refs' => ['efgh5678efgh5678efgh5678efgh5678efgh5678', '1234567812345678123456781234567812345678']])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->mergeBase(1, ['efgh5678efgh5678efgh5678efgh5678efgh5678', '1234567812345678123456781234567812345678']));
    }

    /**
     * @test
     */
    public function shouldCherryPick(): void
    {
        $expectedArray = [
            'id' => 'abcd1234abcd1234abcd1234abcd1234abcd1234',
            'short_id' => 'abcd1234',
            'title' => 'A commit',
            'author_name' => 'Example User',
            'author_email' => 'jane@example.org',
            'authored_date' => '2018-01-01T00:00:00.000Z',
            'created_at' => '2018-01-01T00:00:00.000Z',
            'committer_name' => 'Jane Doe',
            'committer_email' => 'jane@example.org',
            'committed_date' => '2018-01-01T00:00:00.000Z',
            'message' => 'A commit',
            'parent_ids' => [
                'efgh5678efgh5678efgh5678efgh5678efgh5678',
            ],
            'web_url' => 'https://gitlab.example.com/thedude/gitlab-foss/-/commit/abcd1234abcd1234abcd1234abcd1234abcd1234',
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with('projects/1/repository/commits/123456123456/cherry_pick', ['branch' => 'feature_branch'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->cherryPick(1, '123456123456', ['branch' => 'feature_branch']));
    }

    protected function getApiClass()
    {
        return Repositories::class;
    }
}
