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

use DateTime;
use Gitlab\Api\Groups;

class GroupsTest extends TestCase
{
    /**
     * @test
     */
    public function shouldGetAllGroups(): void
    {
        $expectedArray = [
            ['id' => 1, 'name' => 'A group'],
            ['id' => 2, 'name' => 'Another group'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups', ['page' => 1, 'per_page' => 10])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->all(['page' => 1, 'per_page' => 10]));
    }

    /**
     * @test
     */
    public function shouldGetAllGroupsWithBooleanParam(): void
    {
        $expectedArray = [
            ['id' => 1, 'name' => 'A group'],
            ['id' => 2, 'name' => 'Another group'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups', ['all_available' => 'false'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->all(['all_available' => false]));
    }

    /**
     * @test
     */
    public function shouldGetAllTopLevelGroupsWithoutSubgroups(): void
    {
        $expectedArray = [
            ['id' => 1, 'name' => 'A group'],
            ['id' => 2, 'name' => 'Another group'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups', ['top_level_only' => 'true'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->all(['top_level_only' => true]));
    }

    /**
     * @test
     */
    public function shouldGetAllGroupProjectsWithBooleanParam(): void
    {
        $expectedArray = [
            ['id' => 1, 'name' => 'A group'],
            ['id' => 2, 'name' => 'Another group'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/projects', ['archived' => 'false'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->projects(1, ['archived' => false]));
    }

    /**
     * @test
     */
    public function shouldNotNeedPaginationWhenGettingGroups(): void
    {
        $expectedArray = [
            ['id' => 1, 'name' => 'A group'],
            ['id' => 2, 'name' => 'Another group'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups', [])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->all());
    }

    /**
     * @test
     */
    public function shouldShowGroup(): void
    {
        $expectedArray = ['id' => 1, 'name' => 'A group'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->show(1));
    }

    /**
     * @test
     */
    public function shouldCreateGroup(): void
    {
        $expectedArray = ['id' => 1, 'name' => 'A new group'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with('groups', ['name' => 'A new group', 'path' => 'a-new-group', 'visibility' => 'private'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->create('A new group', 'a-new-group'));
    }

    /**
     * @test
     */
    public function shouldCreateGroupWithDescriptionAndVisibility(): void
    {
        $expectedArray = ['id' => 1, 'name' => 'A new group', 'visibility_level' => 2];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with('groups', ['name' => 'A new group', 'path' => 'a-new-group', 'description' => 'Description', 'visibility' => 'public'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->create('A new group', 'a-new-group', 'Description', 'public'));
    }

    /**
     * @test
     */
    public function shouldCreateGroupWithDescriptionVisibilityAndParentId(): void
    {
        $expectedArray = ['id' => 1, 'name' => 'A new group', 'visibility_level' => 2, 'parent_id' => 666];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with('groups', ['name' => 'A new group', 'path' => 'a-new-group', 'description' => 'Description', 'visibility' => 'public', 'parent_id' => 666])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->create('A new group', 'a-new-group', 'Description', 'public', null, null, 666));
    }

    /**
     * @test
     */
    public function shouldUpdateGroup(): void
    {
        $expectedArray = ['id' => 3, 'name' => 'Group name', 'path' => 'group-path'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('put')
            ->with('groups/3', ['name' => 'Group name', 'path' => 'group-path'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->update(3, ['name' => 'Group name', 'path' => 'group-path']));
    }

    /**
     * @test
     */
    public function shouldTransferProjectToGroup(): void
    {
        $expectedBool = true;

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with('groups/1/projects/2')
            ->will($this->returnValue($expectedBool))
        ;

        $this->assertEquals($expectedBool, $api->transfer(1, 2));
    }

    /**
     * @test
     */
    public function shouldGetAllMembers(): void
    {
        $expectedArray = [
            ['id' => 1, 'name' => 'Matt'],
            ['id' => 2, 'name' => 'Bob'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/members/all')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->allMembers(1));
    }

    /**
     * @test
     */
    public function shouldGetAllMember(): void
    {
        $expectedArray = ['id' => 2, 'name' => 'Bob'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/members/all/2')
            ->will($this->returnValue($expectedArray));

        $this->assertEquals($expectedArray, $api->allMember(1, 2));
    }

    /**
     * @test
     */
    public function shouldGetMembers(): void
    {
        $expectedArray = [
            ['id' => 1, 'name' => 'Matt'],
            ['id' => 2, 'name' => 'Bob'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/members')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->members(1));
    }

    /**
     * @test
     */
    public function shouldAddMember(): void
    {
        $tomorrow = (new DateTime('tomorrow'));
        $expectedArray = ['id' => 1, 'name' => 'Matt', 'expires_at' => $tomorrow];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with('groups/1/members', [
                'user_id' => 2, 'access_level' => 10, 'expires_at' => $tomorrow->format('Y-m-d'),
            ])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->addMember(1, 2, 10, ['expires_at' => $tomorrow]));
    }

    /**
     * @test
     */
    public function shouldSaveMember(): void
    {
        $expectedArray = ['id' => 1, 'name' => 'Matt'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('put')
            ->with('groups/1/members/2', ['access_level' => 4])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->saveMember(1, 2, 4));
    }

    /**
     * @test
     */
    public function shouldRemoveMember(): void
    {
        $expectedBool = true;

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('delete')
            ->with('groups/1/members/2')
            ->will($this->returnValue($expectedBool))
        ;

        $this->assertEquals($expectedBool, $api->removeMember(1, 2));
    }

    /**
     * @test
     */
    public function shouldRemoveGroup(): void
    {
        $expectedBool = true;

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('delete')
            ->with('groups/1')
            ->will($this->returnValue($expectedBool))
        ;

        $this->assertEquals($expectedBool, $api->remove(1));
    }

    /**
     * @test
     */
    public function shouldGetAllSubgroups(): void
    {
        $expectedArray = [
            ['id' => 101, 'name' => 'A subgroup'],
            ['id' => 1 - 2, 'name' => 'Another subggroup'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/subgroups', ['page' => 1, 'per_page' => 10])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->subgroups(1, ['page' => 1, 'per_page' => 10]));
    }

    /**
     * @test
     */
    public function shouldGetAllIssues(): void
    {
        $expectedArray = [
            ['id' => 101, 'name' => 'An issue'],
            ['id' => 102, 'name' => 'Another issue'],
            ['id' => 103, 'name' => 'A third issue'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/issues', ['page' => 1, 'per_page' => 10])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->issues(1, ['page' => 1, 'per_page' => 10]));
    }

    /**
     * @test
     */
    public function shouldGetLabels(): void
    {
        $expectedArray = [
            ['id' => 987, 'name' => 'bug', 'color' => '#000000'],
            ['id' => 123, 'name' => 'feature', 'color' => '#ff0000'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/labels')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->labels(1));
    }

    /**
     * @test
     */
    public function shouldAddLabel(): void
    {
        $expectedArray = ['name' => 'bug', 'color' => '#000000'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with('groups/1/labels', ['name' => 'wont-fix', 'color' => '#ffffff'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->addLabel(1, ['name' => 'wont-fix', 'color' => '#ffffff']));
    }

    /**
     * @test
     */
    public function shouldUpdateLabel(): void
    {
        $expectedArray = ['name' => 'bug', 'color' => '#00ffff'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('put')
            ->with('groups/1/labels/123', ['new_name' => 'big-bug', 'color' => '#00ffff'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->updateLabel(1, 123, ['new_name' => 'big-bug', 'color' => '#00ffff']));
    }

    /**
     * @test
     */
    public function shouldRemoveLabel(): void
    {
        $expectedBool = true;

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('delete')
            ->with('groups/1/labels/456', [])
            ->will($this->returnValue($expectedBool))
        ;

        $this->assertEquals($expectedBool, $api->removeLabel(1, 456));
    }

    public function shouldGetVariables(): void
    {
        $expectedArray = [
            ['key' => 'ftp_username', 'value' => 'ftp'],
            ['key' => 'ftp_password', 'value' => 'somepassword'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/variables')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->variables(1));
    }

    /**
     * @test
     */
    public function shouldGetVariable(): void
    {
        $expectedArray = ['key' => 'ftp_username', 'value' => 'ftp'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/variables/ftp_username')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->variable(1, 'ftp_username'));
    }

    public function shouldAddVariable(): void
    {
        $expectedKey = 'ftp_port';
        $expectedValue = '21';

        $expectedArray = [
            'key' => $expectedKey,
            'value' => $expectedValue,
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with('groups/1/variables', $expectedArray)
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->addVariable(1, $expectedKey, $expectedValue));
    }

    /**
     * @test
     */
    public function shouldAddVariableWithProtected(): void
    {
        $expectedArray = [
            'key' => 'DEPLOY_SERVER',
            'value' => 'stage.example.com',
            'protected' => true,
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with('groups/1/variables', $expectedArray)
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->addVariable(1, 'DEPLOY_SERVER', 'stage.example.com', true));
    }

    /**
     * @test
     */
    public function shouldUpdateVariable(): void
    {
        $expectedKey = 'ftp_port';
        $expectedValue = '22';

        $expectedArray = [
            'key' => 'ftp_port',
            'value' => '22',
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('put')
            ->with('groups/1/variables/'.$expectedKey, ['value' => $expectedValue])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->updateVariable(1, $expectedKey, $expectedValue));
    }

    /**
     * @test
     */
    public function shouldUpdateVariableWithProtected(): void
    {
        $expectedArray = [
            'key' => 'DEPLOY_SERVER',
            'value' => 'stage.example.com',
            'protected' => true,
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('put')
            ->with('groups/1/variables/DEPLOY_SERVER', ['value' => 'stage.example.com', 'protected' => true])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->updateVariable(1, 'DEPLOY_SERVER', 'stage.example.com', true));
    }

    /**
     * @test
     */
    public function shouldRemoveVariable(): void
    {
        $expectedBool = true;

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('delete')
            ->with('groups/1/variables/ftp_password')
            ->will($this->returnValue($expectedBool))
        ;

        $this->assertEquals($expectedBool, $api->removeVariable(1, 'ftp_password'));
    }

    protected function getApiClass()
    {
        return Groups::class;
    }

    /**
     * @test
     */
    public function shouldGetAllGroupProjectsWithIssuesEnabled(): void
    {
        $expectedArray = [
            ['id' => 1, 'name' => 'A group', 'issues_enabled' => true],
            ['id' => 2, 'name' => 'Another group', 'issues_enabled' => true],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/projects', ['with_issues_enabled' => 'true'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->projects(1, ['with_issues_enabled' => true]));
    }

    /**
     * @test
     */
    public function shouldGetAllGroupProjectsWithMergeRequestsEnabled(): void
    {
        $expectedArray = [
            ['id' => 1, 'name' => 'A group', 'merge_requests_enabled' => true],
            ['id' => 2, 'name' => 'Another group', 'merge_requests_enabled' => true],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/projects', ['with_merge_requests_enabled' => 'true'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->projects(1, ['with_merge_requests_enabled' => true]));
    }

    /**
     * @test
     */
    public function shouldGetAllGroupProjectsSharedToGroup(): void
    {
        $expectedArray = [
            ['id' => 1, 'name' => 'A project', 'shared_with_groups' => [1]],
            ['id' => 2, 'name' => 'Another project', 'shared_with_groups' => [1]],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/projects', ['with_shared' => 'true'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->projects(1, ['with_shared' => true]));
    }

    /**
     * @test
     */
    public function shouldGetAllGroupProjectsIncludingSubsgroups(): void
    {
        $expectedArray = [
            ['id' => 1, 'name' => 'A project'],
            ['id' => 2, 'name' => 'Another project', 'shared_with_groups' => [1]],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/projects', ['include_subgroups' => 'true'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->projects(1, ['include_subgroups' => true]));
    }

    /**
     * @test
     */
    public function shouldGetAllGroupProjectsIncludingCustomAttributes(): void
    {
        $expectedArray = [
            ['id' => 1, 'name' => 'A project', 'custom_Attr' => true],
            ['id' => 2, 'name' => 'Another project', 'custom_Attr' => true],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/projects', ['with_custom_attributes' => 'true'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->projects(1, ['with_custom_attributes' => true]));
    }

    /**
     * @test
     */
    public function shouldGetIterations(): void
    {
        $expectedArray = [
            [
                'id' => 5,
                'iid' => 2,
                'sequence' => 1,
                'group_id' => 123,
                'title' => '2022: Sprint 1',
                'description' => '',
                'state' => 3,
                'created_at' => '2021-09-29T21:24:43.913Z',
                'updated_at' => '2022-03-29T19:09:08.368Z',
                'start_date' => '2022-01-10',
                'due_date' => '2022-01-23',
                'web_url' => 'https://example.com/groups/example/-/iterations/34',
            ],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/iterations')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->iterations(1));
    }

    /**
     * @test
     */
    public function shouldGetPackages(): void
    {
        $expectedArray = [
            'id' => 2,
            'name' => '@foo/bar',
            'version' => '1.0.3',
            'package_type' => 'npm',
            '_links' => [
                'web_path' => '/namespace1/project1/-/packages/1',
                'delete_api_path' => '/namespace1/project1/-/packages/1',
            ],
            'created_at' => '2019-11-27T03:37:38.711Z',
            'pipelines' => [
                'id' => 123,
                'status' => 'pending',
                'ref' => 'new-pipeline',
                'sha' => 'a91957a858320c0e17f3a0eca7cfacbff50ea29a',
                'web_url' => 'https://example.com/foo/bar/pipelines/47',
                'created_at' => '2016-08-11T11:28:34.085Z',
                'updated_at' => '2016-08-11T11:32:35.169Z',
                'user' => [
                    'name' => 'Administrator',
                    'avatar_url' => 'https://www.gravatar.com/avatar/e64c7d89f26bd1972efa854d13d7dd61?s=80&d=identicon',
                ],
            ],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/packages')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->packages(1));
    }

    /**
     * @test
     */
    public function shouldGetGroupMergeRequests(): void
    {
        $expectedArray = [
            ['id' => 1, 'title' => 'A merge request'],
            ['id' => 2, 'title' => 'Another merge request'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/merge_requests')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->mergeRequests(1, []));
    }

    /**
     * @test
     */
    public function shouldGetDeployTokens(): void
    {
        $expectedArray = [
            [
                'id' => 1,
                'name' => 'MyToken',
                'username' => 'gitlab+deploy-token-1',
                'expires_at' => '2020-02-14T00:00:00.000Z',
                'revoked' => false,
                'expired' => false,
                'scopes' => [
                    'read_repository',
                    'read_registry',
                ],
            ],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/deploy_tokens')
            ->will($this->returnValue($expectedArray));

        $this->assertEquals($expectedArray, $api->deployTokens(1));
    }

    /**
     * @test
     */
    public function shouldGetActiveDeployTokens(): void
    {
        $expectedArray = [
            [
                'id' => 1,
                'name' => 'MyToken',
                'username' => 'gitlab+deploy-token-1',
                'expires_at' => '2020-02-14T00:00:00.000Z',
                'revoked' => false,
                'expired' => true,
                'scopes' => [
                    'read_repository',
                    'read_registry',
                ],
            ],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/deploy_tokens', ['active' => true])
            ->will($this->returnValue([]));

        $this->assertEquals([], $api->deployTokens(1, true));
    }

    /**
     * @test
     */
    public function shouldGetInactiveDeployTokens(): void
    {
        $expectedArray = [
            [
                'id' => 1,
                'name' => 'MyToken',
                'username' => 'gitlab+deploy-token-1',
                'expires_at' => '2020-02-14T00:00:00.000Z',
                'revoked' => false,
                'expired' => true,
                'scopes' => [
                    'read_repository',
                    'read_registry',
                ],
            ],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/deploy_tokens', ['active' => false])
            ->will($this->returnValue([]));

        $this->assertEquals([], $api->deployTokens(1, false));
    }

    /**
     * @test
     */
    public function shouldCreateDeployToken(): void
    {
        $expectedArray = [
            'id' => 1,
            'name' => 'My Deploy Token',
            'username' => 'custom-user',
            'token' => 'jMRvtPNxrn3crTAGukpZ',
            'expires_at' => '2021-01-01T00:00:00.000Z',
            'revoked' => false,
            'expired' => false,
            'scopes' => [
                'read_repository',
                'read_registry',
            ],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with(
                'groups/1/deploy_tokens',
                [
                    'name' => 'My Deploy Token',
                    'scopes' => [
                        'read_repository',
                        'read_registry',
                    ],
                    'expires_at' => (new DateTime('2021-01-01'))->format('c'),
                ]
            )
            ->will($this->returnValue($expectedArray));

        $this->assertEquals($expectedArray, $api->createDeployToken(1, [
            'name' => 'My Deploy Token',
            'scopes' => [
                'read_repository',
                'read_registry',
            ],
            'expires_at' => new DateTime('2021-01-01'),
        ]));
    }

    /**
     * @test
     */
    public function shouldDeleteDeployToken(): void
    {
        $expectedBool = true;

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('delete')
            ->with('groups/1/deploy_tokens/2')
            ->will($this->returnValue($expectedBool));

        $this->assertEquals($expectedBool, $api->deleteDeployToken(1, 2));
    }

    /**
     * @test
     */
    public function shouldSearchGroups(): void
    {
        $expectedArray = [
            ['id' => 6, 'name' => 'Project 6 bla'],
            ['id' => 7, 'name' => 'Project 7 bla'],
            ['id' => 8, 'name' => 'Project 8 bla'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/123/search', [
                'scope' => 'projects',
                'confidential' => 'false',
                'search' => 'bla',
                'order_by' => 'created_at',
                'sort' => 'desc',
            ])
            ->will($this->returnValue($expectedArray));

        $this->assertEquals($expectedArray, $api->search(123, [
            'scope' => 'projects',
            'confidential' => false,
            'search' => 'bla',
            'order_by' => 'created_at',
            'sort' => 'desc',
        ]));
    }

    /**
     * @test
     */
    public function shouldGetGroupRegistryRepositories(): void
    {
        $expectedArray = [
            ['id' => 1, 'name' => 'A registry'],
            ['id' => 2, 'name' => 'Another registry'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/registry/repositories')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->registryRepositories(1));
    }
}
