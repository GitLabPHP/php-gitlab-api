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

use Gitlab\Api\Registry;

class RegistryTest extends TestCase
{
    protected function getApiClass(): string
    {
        return Registry::class;
    }

    /**
     * @test
     */
    public function shouldGetSingleRepository(): void
    {
        $expectedArray = ['id' => 1, 'name' => 'John Doe'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('registry/repositories/1')
            ->willReturn($expectedArray);

        $this->assertEquals($expectedArray, $api->repositories(1));
    }

    /**
     * @test
     */
    public function shouldGetSingleRepositoryWithParams(): void
    {
        $expectedArray = ['id' => 1, 'name' => 'John Doe', 'tags' => ['tag1', 'tag2'], 'tags_count' => 2];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('registry/repositories/1', ['tags' => 'true', 'tags_count' => 'true'])
            ->willReturn($expectedArray);

        $this->assertEquals($expectedArray, $api->repositories(1, ['tags' => true, 'tags_count' => true]));
    }

    /**
     * @test
     */
    public function shouldGetRepositoryTags(): void
    {
        $expectedArray = [['name' => 'A', 'path' => 'group/project:A'], ['name' => 'B', 'path' => 'group/project:B']];
        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('projects/1/registry/repositories/1/tags')
            ->willReturn($expectedArray);

        $this->assertEquals($expectedArray, $api->repositoryTags(1, 1));
    }

    /**
     * @test
     */
    public function shouldGetRepositoryTag(): void
    {
        $expectedArray = ['name' => 'A', 'path' => 'group/project:A'];

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('get')
            ->with('projects/1/registry/repositories/1/tags/A')
            ->willReturn($expectedArray);

        $this->assertEquals($expectedArray, $api->repositoryTag(1, 1, 'A'));
    }

    /**
     * @test
     */
    public function shouldRemoveRepositoryTag(): void
    {
        $expectedBool = true;

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('delete')
            ->with('projects/1/registry/repositories/1/tags/A')
            ->will($this->returnValue($expectedBool));

        $this->assertEquals($expectedBool, $api->removeRepositoryTag(1, 1, 'A'));
    }

    /**
     * @test
     */
    public function shouldRemoveRepositoryTags(): void
    {
        $expectedBool = true;

        $api = $this->getApiMock();
        $api->expects($this->once())
            ->method('delete')
            ->with('projects/1/registry/repositories/1/tags', ['name_regex_delete' => '.*', 'keep_n' => 12])
            ->will($this->returnValue($expectedBool));

        $this->assertEquals($expectedBool, $api->removeRepositoryTags(1, 1, ['name_regex_delete' => '.*', 'keep_n' => 12]));
    }
}
