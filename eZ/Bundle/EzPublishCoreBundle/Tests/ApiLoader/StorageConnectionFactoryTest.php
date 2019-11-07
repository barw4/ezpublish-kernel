<?php

/**
 * File containing the StorageConnectionFactoryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\StorageConnectionFactory;
use eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use PHPUnit\Framework\TestCase;

class StorageConnectionFactoryTest extends TestCase
{
    /**
     * @dataProvider getConnectionProvider
     */
    public function testGetConnection($repositoryAlias, $doctrineConnection)
    {
        $repositories = [
            $repositoryAlias => [
                'storage' => [
                    'engine' => 'legacy',
                    'connection' => $doctrineConnection,
                ],
            ],
        ];

        $configResolver = $this->getConfigResolverMock();
        $configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('repository')
            ->willReturn($repositoryAlias);

        $container = $this->getContainerMock();
        $container
            ->expects($this->once())
            ->method('has')
            ->with("doctrine.dbal.{$doctrineConnection}_connection")
            ->willReturn(true);
        $container
            ->expects($this->once())
            ->method('get')
            ->with("doctrine.dbal.{$doctrineConnection}_connection")
            ->willReturn($this->getMockBuilder('Doctrine\DBAL\Connection')->disableOriginalConstructor()->getMock());

        $repositoryConfigurationProvider = new RepositoryConfigurationProvider($configResolver, $repositories);
        $factory = new StorageConnectionFactory($repositoryConfigurationProvider);
        $factory->setContainer($container);
        $connection = $factory->getConnection();
        $this->assertInstanceOf(
            'Doctrine\DBAL\Connection',
            $connection
        );
    }

    public function getConnectionProvider()
    {
        return [
            ['my_repository', 'my_doctrine_connection'],
            ['foo', 'default'],
            ['répository_de_dédé', 'la_connexion_de_bébêrt'],
        ];
    }

    /**
     * @expectedException \eZ\Bundle\EzPublishCoreBundle\ApiLoader\Exception\InvalidRepositoryException
     */
    public function testGetConnectionInvalidRepository()
    {
        $repositories = [
            'foo' => [
                'storage' => [
                    'engine' => 'legacy',
                    'connection' => 'my_doctrine_connection',
                ],
            ],
        ];

        $configResolver = $this->getConfigResolverMock();
        $configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('repository')
            ->willReturn('inexistent_repository');

        $repositoryConfigurationProvider = new RepositoryConfigurationProvider($configResolver, $repositories);
        $factory = new StorageConnectionFactory($repositoryConfigurationProvider);
        $factory->setContainer($this->getContainerMock());
        $factory->getConnection();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetConnectionInvalidConnection()
    {
        $repositoryConfigurationProviderMock = $this->createMock(RepositoryConfigurationProvider::class);
        $repositoryConfig = [
            'alias' => 'foo',
            'storage' => [
                'engine' => 'legacy',
                'connection' => 'my_doctrine_connection',
            ],
        ];
        $repositoryConfigurationProviderMock
            ->expects($this->once())
            ->method('getRepositoryConfig')
            ->willReturn($repositoryConfig);

        $container = $this->getContainerMock();
        $container
            ->expects($this->once())
            ->method('has')
            ->with('doctrine.dbal.my_doctrine_connection_connection')
            ->willReturn(false);
        $container
            ->expects($this->once())
            ->method('getParameter')
            ->with('doctrine.connections')
            ->willReturn([]);
        $factory = new StorageConnectionFactory($repositoryConfigurationProviderMock);
        $factory->setContainer($container);
        $factory->getConnection();
    }

    protected function getConfigResolverMock()
    {
        return $this->createMock(ConfigResolverInterface::class);
    }

    protected function getContainerMock()
    {
        return $this->createMock(ContainerInterface::class);
    }
}
