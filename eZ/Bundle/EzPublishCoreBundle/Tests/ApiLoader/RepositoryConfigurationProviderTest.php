<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use PHPUnit\Framework\TestCase;

class RepositoryConfigurationProviderTest extends TestCase
{
    public function testGetRepositoryConfigSpecifiedRepository()
    {
        $configResolver = $this->getConfigResolverMock();
        $repositoryAlias = 'main';
        $repositoryConfig = [
            'engine' => 'foo',
            'connection' => 'some_connection',
        ];
        $repositories = [
            $repositoryAlias => $repositoryConfig,
            'another' => [
                'engine' => 'bar',
            ],
        ];
        $provider = new RepositoryConfigurationProvider($configResolver, $repositories);

        $configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('repository')
            ->willReturn($repositoryAlias);

        $this->assertSame(
            ['alias' => $repositoryAlias] + $repositoryConfig,
            $provider->getRepositoryConfig()
        );
    }

    public function testGetRepositoryConfigNotSpecifiedRepository()
    {
        $configResolver = $this->getConfigResolverMock();
        $repositoryAlias = 'main';
        $repositoryConfig = [
            'engine' => 'foo',
            'connection' => 'some_connection',
        ];
        $repositories = [
            $repositoryAlias => $repositoryConfig,
            'another' => [
                'engine' => 'bar',
            ],
        ];
        $provider = new RepositoryConfigurationProvider($configResolver, $repositories);

        $configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('repository')
            ->willReturn(null);

        $this->assertSame(
            ['alias' => $repositoryAlias] + $repositoryConfig,
            $provider->getRepositoryConfig()
        );
    }

    /**
     * @expectedException \eZ\Bundle\EzPublishCoreBundle\ApiLoader\Exception\InvalidRepositoryException
     */
    public function testGetRepositoryConfigUndefinedRepository()
    {
        $configResolver = $this->getConfigResolverMock();
        $repositories = [
            'main' => [
                'engine' => 'foo',
            ],
            'another' => [
                'engine' => 'bar',
            ],
        ];

        $configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('repository')
            ->willReturn('undefined_repository');

        $provider = new RepositoryConfigurationProvider($configResolver, $repositories);
        $provider->getRepositoryConfig();
    }

    /**
     * @return \eZ\Publish\Core\MVC\ConfigResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getConfigResolverMock()
    {
        return $this->createMock(ConfigResolverInterface::class);
    }
}
