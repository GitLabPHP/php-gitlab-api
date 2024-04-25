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

use Gitlab\Api\GroupsMilestones;

class GroupsMilestonesTest extends TestCase
{
    /**
     * @test
     */
    public function shouldGetAllMilestones(): void
    {
        $expectedArray = [
            ['id' => 1, 'title' => 'A milestone'],
            ['id' => 2, 'title' => 'Another milestone'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/milestones')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->all(1));
    }

    /**
     * @test
     */
    public function shouldGetAllMilestonesWithParameterOneIidsValue(): void
    {
        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/milestones', ['iids' => [456]])
        ;

        $api->all(1, ['iids' => [456]]);
    }

    /**
     * @test
     */
    public function shouldGetAllMilestonesWithParameterTwoIidsValues(): void
    {
        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/milestones', ['iids' => [456, 789]])
        ;

        $api->all(1, ['iids' => [456, 789]]);
    }

    public static function getAllMilestonesWithParameterStateDataProvider()
    {
        return [
            GroupsMilestones::STATE_ACTIVE => [GroupsMilestones::STATE_ACTIVE],
            GroupsMilestones::STATE_CLOSED => [GroupsMilestones::STATE_CLOSED],
        ];
    }

    /**
     * @test
     *
     * @dataProvider getAllMilestonesWithParameterStateDataProvider
     */
    public function shouldGetAllMilestonesWithParameterState(string $state): void
    {
        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/milestones', ['state' => $state])
        ;

        $api->all(1, ['state' => $state]);
    }

    /**
     * @test
     */
    public function shouldGetAllMilestonesWithParameterSearch(): void
    {
        $searchValue = 'abc';

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/milestones', ['search' => $searchValue])
        ;

        $api->all(1, ['search' => $searchValue]);
    }

    /**
     * @test
     */
    public function shouldGetAllMilestonesWithParameterUpdatedBefore(): void
    {
        $updatedBefore = new \DateTimeImmutable('2023-11-25T08:00:00Z');

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/milestones', ['updated_before' => '2023-11-25T08:00:00.000Z'])
        ;

        $api->all(1, ['updated_before' => $updatedBefore]);
    }

    /**
     * @test
     */
    public function shouldGetAllMilestonesWithParameterUpdatedAfter(): void
    {
        $updatedAfter = new \DateTimeImmutable('2023-11-25T08:00:00Z');

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/milestones', ['updated_after' => '2023-11-25T08:00:00.000Z'])
        ;

        $api->all(1, ['updated_after' => $updatedAfter]);
    }

    /**
     * @test
     */
    public function shouldShowMilestone(): void
    {
        $expectedArray = ['id' => 1, 'name' => 'A milestone'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/milestones/2')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->show(1, 2));
    }

    /**
     * @test
     */
    public function shouldCreateMilestone(): void
    {
        $expectedArray = ['id' => 3, 'title' => 'A new milestone'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('post')
            ->with('groups/1/milestones', ['description' => 'Some text', 'title' => 'A new milestone'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->create(1, ['description' => 'Some text', 'title' => 'A new milestone']));
    }

    /**
     * @test
     */
    public function shouldUpdateMilestone(): void
    {
        $expectedArray = ['id' => 3, 'title' => 'Updated milestone'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('put')
            ->with('groups/1/milestones/3', ['title' => 'Updated milestone', 'due_date' => '2015-04-01', 'state_event' => 'close'])
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->update(1, 3, ['title' => 'Updated milestone', 'due_date' => '2015-04-01', 'state_event' => 'close']));
    }

    /**
     * @test
     */
    public function shouldRemoveMilestone(): void
    {
        $expectedBool = true;

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('delete')
            ->with('groups/1/milestones/2')
            ->will($this->returnValue($expectedBool))
        ;

        $this->assertEquals($expectedBool, $api->remove(1, 2));
    }

    /**
     * @test
     */
    public function shouldGetMilestonesIssues(): void
    {
        $expectedArray = [
            ['id' => 1, 'title' => 'An issue'],
            ['id' => 2, 'title' => 'Another issue'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/milestones/3/issues')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->issues(1, 3));
    }

    /**
     * @test
     */
    public function shouldGetMilestonesMergeRequests(): void
    {
        $expectedArray = [
            ['id' => 1, 'title' => 'A merge request'],
            ['id' => 2, 'title' => 'Another merge request'],
        ];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('groups/1/milestones/3/merge_requests')
            ->will($this->returnValue($expectedArray))
        ;

        $this->assertEquals($expectedArray, $api->mergeRequests(1, 3));
    }

    protected function getApiClass()
    {
        return GroupsMilestones::class;
    }
}
