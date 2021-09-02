<?php

declare(strict_types=1);

/*
 * This file is part of the Gitlab API library.
 *
 * (c) Matt Humphrey <matth@windsor-telecom.co.uk>
 * (c) Graham Campbell <graham@alt-three.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gitlab\Tests\Api;

use Gitlab\Api\Users;

class UsersTest extends TestCase
{
    /**
     * @test
     */
    public function shouldGetAllUsers(): void
    {
        $expectedArray = [
            ['id' => 1, 'name' => 'Matt'],
            ['id' => 2, 'name' => 'John'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('users', [])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->all());
    }

    /**
     * @test
     */
    public function shouldGetActiveUsers(): void
    {
        $expectedArray = [
            ['id' => 1, 'name' => 'Matt'],
            ['id' => 2, 'name' => 'John'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('users', ['active' => true])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->all(['active' => true]));
    }

    /**
     * @test
     */
    public function shouldGetUsersWithDateTimeParams(): void
    {
        $expectedArray = [
            ['id' => 1, 'name' => 'Matt'],
            ['id' => 2, 'name' => 'John'],
        ];

        $createdAfter = new \DateTime('2018-01-01 00:00:00');
        $createdBefore = new \DateTime('2018-01-31 00:00:00');

        $expectedWithArray = [
            'created_after' => $createdAfter->format(\DATE_ATOM),
            'created_before' => $createdBefore->format(\DATE_ATOM),
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('users', $expectedWithArray)
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals(
            $expectedArray,
            $api->all(['created_after' => $createdAfter, 'created_before' => $createdBefore])
        );
    }

    /**
     * @test
     */
    public function shouldShowUser(): void
    {
        $expectedArray = ['id' => 1, 'name' => 'Matt'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('users/1')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->show(1));
    }

    protected function getUsersMembershipsData()
    {
        return [
            [
                'source_id' => 1,
                'source_name' => 'Project one',
                'source_type' => 'Project',
                'access_level' => '20',
            ],
            [
                'source_id' => 3,
                'source_name' => 'Group three',
                'source_type' => 'Namespace',
                'access_level' => '20',
            ],
        ];
    }

    protected function getUsersMembershipsRequestMock($path, $expectedArray = [], $expectedParameters = [])
    {
        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with($path, $expectedParameters)
            ->will($this->returnValue($expectedArray))
        ;

        return $api;
    }

    /**
     * @test
     */
    public function shouldShowUsersMemberships(): void
    {
        $expectedArray = $this->getUsersMembershipsData();

        $api = $this->getUsersMembershipsRequestMock('users/1/memberships', $expectedArray);

        $this->assertEquals($expectedArray, $api->usersMemberships(1));
    }

    /**
     * @test
     */
    public function shouldShowUsersMembershipsWithTypeProject(): void
    {
        $expectedArray = [$this->getUsersMembershipsData()[0]];

        $api = $this->getUsersMembershipsRequestMock('users/1/memberships', $expectedArray, ['type' => 'Project']);

        $this->assertEquals($expectedArray, $api->usersMemberships(1, ['type' => 'Project']));
    }

    /**
     * @test
     */
    public function shouldShowUsersMembershipsWithTypeNamespace(): void
    {
        $expectedArray = [$this->getUsersMembershipsData()[1]];

        $api = $this->getUsersMembershipsRequestMock('users/1/memberships', $expectedArray, ['type' => 'Namespace']);

        $this->assertEquals($expectedArray, $api->usersMemberships(1, ['type' => 'Namespace']));
    }

    protected function getUsersProjectsData()
    {
        return [
            ['id' => 1, 'name' => 'matt-project-1'],
            ['id' => 2, 'name' => 'matt-project-2'],
        ];
    }

    protected function getUsersProjectsRequestMock($path, $expectedArray = [], $expectedParameters = [])
    {
        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with($path, $expectedParameters)
            ->will($this->returnValue($expectedArray))
        ;

        return $api;
    }

    /**
     * @test
     */
    public function shouldShowUsersProjects(): void
    {
        $expectedArray = $this->getUsersProjectsData();

        $api = $this->getUsersProjectsRequestMock('users/1/projects', $expectedArray);

        $this->assertEquals($expectedArray, $api->usersProjects(1));
    }

    /**
     * @test
     */
    public function shouldShowUsersProjectsWithLimit(): void
    {
        $expectedArray = [$this->getUsersProjectsData()[0]];

        $api = $this->getUsersProjectsRequestMock('users/1/projects', $expectedArray, ['per_page' => 1]);

        $this->assertEquals($expectedArray, $api->usersProjects(1, ['per_page' => 1]));
    }

    /**
     * @test
     */
    public function shouldGetAllUsersProjectsSortedByName(): void
    {
        $expectedArray = $this->getUsersProjectsData();

        $api = $this->getUsersProjectsRequestMock(
            'users/1/projects',
            $expectedArray,
            ['page' => 1, 'per_page' => 5, 'order_by' => 'name', 'sort' => 'asc']
        );

        $this->assertEquals(
            $expectedArray,
            $api->usersProjects(1, ['page' => 1, 'per_page' => 5, 'order_by' => 'name', 'sort' => 'asc'])
        );
    }

    /**
     * @test
     */
    public function shouldGetNotArchivedUsersProjects(): void
    {
        $expectedArray = $this->getUsersProjectsData();

        $api = $this->getUsersProjectsRequestMock('users/1/projects', $expectedArray, ['archived' => 'false']);

        $this->assertEquals($expectedArray, $api->usersProjects(1, ['archived' => false]));
    }

    /**
     * @test
     */
    public function shouldGetOwnedUsersProjects(): void
    {
        $expectedArray = $this->getUsersProjectsData();

        $api = $this->getUsersProjectsRequestMock('users/1/projects', $expectedArray, ['owned' => 'true']);

        $this->assertEquals($expectedArray, $api->usersProjects(1, ['owned' => true]));
    }

    public function possibleAccessLevels()
    {
        return [
            [10],
            [20],
            [30],
            [40],
            [50],
        ];
    }

    /**
     * @test
     * @dataProvider possibleAccessLevels
     */
    public function shouldGetProjectsWithMinimumAccessLevel($level): void
    {
        $expectedArray = $this->getUsersProjectsData();

        $api = $this->getUsersProjectsRequestMock('users/1/projects', $expectedArray, ['min_access_level' => $level]);

        $this->assertEquals($expectedArray, $api->usersProjects(1, ['min_access_level' => $level]));
    }

    /**
     * @test
     */
    public function shouldSearchUsersProjects(): void
    {
        $expectedArray = $this->getUsersProjectsData();

        $api = $this->getUsersProjectsRequestMock('users/1/projects', $expectedArray, ['search' => 'a project']);
        $this->assertEquals($expectedArray, $api->usersProjects(1, ['search' => 'a project']));
    }

    /**
     * @test
     */
    public function shouldCreateUser(): void
    {
        $expectedArray = ['id' => 3, 'name' => 'Billy'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with('users', ['email' => 'billy@example.com', 'password' => 'password'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->create('billy@example.com', 'password'));
    }

    /**
     * @test
     */
    public function shouldCreateUserWithAdditionalInfo(): void
    {
        $expectedArray = ['id' => 3, 'name' => 'Billy'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with('users', ['email' => 'billy@example.com', 'password' => 'password', 'name' => 'Billy', 'bio' => 'A person'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->create('billy@example.com', 'password', ['name' => 'Billy', 'bio' => 'A person']));
    }

    /**
     * @test
     */
    public function shouldUpdateUser(): void
    {
        $expectedArray = ['id' => 3, 'name' => 'Billy Bob'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('put')
            ->with('users/3', ['name' => 'Billy Bob'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->update(3, ['name' => 'Billy Bob']));

        $expectedArray = ['id' => 4, 'avatar_url' => 'http://localhost:3000/uploads/user/avatar/4/image.jpg'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('put')
            ->with('users/4', [], [], ['avatar' => '/some/image.jpg'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->update(4, [], ['avatar' => '/some/image.jpg']));
    }

    /**
     * @test
     */
    public function shouldRemoveUser(): void
    {
        $expectedBool = true;

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('delete')
            ->with('users/1')
            ->will($this->returnValue($expectedBool))
        ;

        $this->assertEquals($expectedBool, $api->remove(1));
    }

    /**
     * @test
     */
    public function shouldBlockUser(): void
    {
        $expectedBool = true;

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with('users/1/block')
            ->will($this->returnValue($expectedBool))
        ;

        $this->assertEquals($expectedBool, $api->block(1));
    }

    /**
     * @test
     */
    public function shouldUnblockUser(): void
    {
        $expectedBool = true;

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with('users/1/unblock')
            ->will($this->returnValue($expectedBool))
        ;

        $this->assertEquals($expectedBool, $api->unblock(1));
    }

    /**
     * @test
     */
    public function shouldActivateUser(): void
    {
        $expectedBool = true;

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with('users/1/activate')
            ->will($this->returnValue($expectedBool))
        ;

        $this->assertEquals($expectedBool, $api->activate(1));
    }

    /**
     * @test
     */
    public function shouldDeactivateUser(): void
    {
        $expectedBool = true;

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with('users/1/deactivate')
            ->will($this->returnValue($expectedBool))
        ;

        $this->assertEquals($expectedBool, $api->deactivate(1));
    }

    /**
     * @test
     */
    public function shouldShowCurrentUser(): void
    {
        $expectedArray = ['id' => 1, 'name' => 'Matt'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('user')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->me());
    }

    /**
     * @test
     */
    public function shouldGetCurrentUserKeys(): void
    {
        $expectedArray = [
            ['id' => 1, 'title' => 'A key'],
            ['id' => 2, 'name' => 'Another key'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('user/keys')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->keys(1));
    }

    /**
     * @test
     */
    public function shouldGetCurrentUserKey(): void
    {
        $expectedArray = ['id' => 1, 'title' => 'A key'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('user/keys/1')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->key(1));
    }

    /**
     * @test
     */
    public function shouldCreateKeyForCurrentUser(): void
    {
        $expectedArray = ['id' => 3, 'title' => 'A new key'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with('user/keys', ['title' => 'A new key', 'key' => '...'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->createKey('A new key', '...'));
    }

    /**
     * @test
     */
    public function shouldDeleteKeyForCurrentUser(): void
    {
        $expectedBool = true;

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('delete')
            ->with('user/keys/3')
            ->will($this->returnValue($expectedBool))
        ;

        $this->assertEquals($expectedBool, $api->removeKey(3));
    }

    /**
     * @test
     */
    public function shouldGetUserKeys(): void
    {
        $expectedArray = [
            ['id' => 1, 'title' => 'A key'],
            ['id' => 2, 'name' => 'Another key'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('users/1/keys')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->userKeys(1));
    }

    /**
     * @test
     */
    public function shouldGetUserKey(): void
    {
        $expectedArray = ['id' => 2, 'title' => 'Another key'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('users/1/keys/2')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->userKey(1, 2));
    }

    /**
     * @test
     */
    public function shouldCreateKeyForUser(): void
    {
        $expectedArray = ['id' => 3, 'title' => 'A new key'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with('users/1/keys', ['title' => 'A new key', 'key' => '...'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->createKeyForUser(1, 'A new key', '...'));
    }

    /**
     * @test
     */
    public function shouldDeleteKeyForUser(): void
    {
        $expectedBool = true;

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('delete')
            ->with('users/1/keys/3')
            ->will($this->returnValue($expectedBool))
        ;

        $this->assertEquals($expectedBool, $api->removeUserKey(1, 3));
    }

    /**
     * @test
     */
    public function shouldGetUserEmails(): void
    {
        $expectedArray = [
            ['id' => 1, 'email' => 'foo@bar.baz'],
            ['id' => 2, 'email' => 'foo@bar.qux'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('user/emails')
            ->will($this->returnValue($expectedArray));

        $this->assertEquals($expectedArray, $api->emails());
    }

    /**
     * @test
     */
    public function shouldGetSpecificUserEmail(): void
    {
        $expectedArray = ['id' => 1, 'email' => 'foo@bar.baz'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('user/emails/1')
            ->will($this->returnValue($expectedArray));

        $this->assertEquals($expectedArray, $api->email(1));
    }

    /**
     * @test
     */
    public function shouldGetEmailsForUser(): void
    {
        $expectedArray = [
            ['id' => 1, 'email' => 'foo@bar.baz'],
            ['id' => 2, 'email' => 'foo@bar.qux'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('users/1/emails')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->userEmails(1));
    }

    /**
     * @test
     */
    public function shouldCreateEmailForUser(): void
    {
        $expectedArray = ['id' => 3, 'email' => 'foo@bar.example'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with('users/1/emails', ['email' => 'foo@bar.example', 'skip_confirmation' => false])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->createEmailForUser(1, 'foo@bar.example'));
    }

    /**
     * @test
     */
    public function shouldCreateConfirmedEmailForUser(): void
    {
        $expectedArray = ['id' => 4, 'email' => 'foo@baz.example'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with('users/1/emails', ['email' => 'foo@baz.example', 'skip_confirmation' => true])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->createEmailForUser(1, 'foo@baz.example', true));
    }

    /**
     * @test
     */
    public function shouldDeleteEmailForUser(): void
    {
        $expectedBool = true;

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('delete')
            ->with('users/1/emails/3')
            ->will($this->returnValue($expectedBool))
        ;

        $this->assertEquals($expectedBool, $api->removeUserEmail(1, 3));
    }

    /**
     * @test
     */
    public function shouldGetCurrentUserImpersonationTokens(): void
    {
        $expectedArray = [
            ['id' => 1, 'name' => 'A Name', 'revoked' => false],
            ['id' => 2, 'name' => 'A Name', 'revoked' => false],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('users/1/impersonation_tokens')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->userImpersonationTokens(1));
    }

    /**
     * @test
     */
    public function shouldGetUserImpersonationToken(): void
    {
        $expectedArray = ['id' => 2, 'name' => 'name'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('users/1/impersonation_tokens/1')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->userImpersonationToken(1, 1));
    }

    /**
     * @test
     */
    public function shouldCreateImpersonationTokenForUser(): void
    {
        $expectedArray = ['id' => 1, 'name' => 'name'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with('users/1/impersonation_tokens', ['name' => 'name', 'scopes' => ['api'], 'expires_at' => null])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->createImpersonationToken(1, 'name', ['api']));
    }

    /**
     * @test
     */
    public function shouldDeleteImpersonationTokenForUser(): void
    {
        $expectedBool = true;

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('delete')
            ->with('users/1/impersonation_tokens/1')
            ->will($this->returnValue($expectedBool))
        ;

        $this->assertEquals($expectedBool, $api->removeImpersonationToken(1, 1));
    }

    /**
     * @test
     */
    public function shouldGetCurrentUserActiveImpersonationTokens(): void
    {
        $expectedArray = [
            ['id' => 1, 'name' => 'A Name', 'revoked' => true],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('users/1/impersonation_tokens')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->userImpersonationTokens(1, ['state' => 'active']));
    }

    /**
     * @test
     */
    public function shouldGetCurrentUserInactiveImpersonationTokens(): void
    {
        $expectedArray = [
            ['id' => 2, 'name' => 'A Name', 'revoked' => false],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('users/1/impersonation_tokens')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->userImpersonationTokens(1, ['state' => 'inactive']));
    }

    protected function getApiClass()
    {
        return Users::class;
    }

    /**
     * @test
     */
    public function shouldGetEvents(): void
    {
        $expectedArray = [
            ['id' => 1, 'title' => 'An event'],
            ['id' => 2, 'title' => 'Another event'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('users/1/events', [])
            ->will($this->returnValue($expectedArray));

        $this->assertEquals($expectedArray, $api->events(1));
    }

    /**
     * @test
     */
    public function shouldGetEventsWithDateTimeParams(): void
    {
        $expectedArray = [
            ['id' => 1, 'title' => 'An event'],
            ['id' => 2, 'title' => 'Another event'],
        ];

        $after = new \DateTime('2018-01-01 00:00:00');
        $before = new \DateTime('2018-01-31 00:00:00');

        $expectedWithArray = [
            'after' => $after->format('Y-m-d'),
            'before' => $before->format('Y-m-d'),
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('users/1/events', $expectedWithArray)
            ->will($this->returnValue($expectedArray));

        $this->assertEquals($expectedArray, $api->events(1, ['after' => $after, 'before' => $before]));
    }

    /**
     * @test
     */
    public function shouldGetEventsWithPagination(): void
    {
        $expectedArray = [
            ['id' => 1, 'title' => 'An event'],
            ['id' => 2, 'title' => 'Another event'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('users/1/events', [
                'page' => 2,
                'per_page' => 15,
            ])
            ->will($this->returnValue($expectedArray));

        $this->assertEquals($expectedArray, $api->events(1, ['page' => 2, 'per_page' => 15]));
    }
}
