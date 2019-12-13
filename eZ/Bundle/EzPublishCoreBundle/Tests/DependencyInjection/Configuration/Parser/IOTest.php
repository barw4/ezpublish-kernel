<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ChainConfigResolver;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ComplexSettings\ComplexSettingParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\IO;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\Provider\StaticSiteAccessProvider;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessProviderInterface;
use Symfony\Component\Yaml\Yaml;

class IOTest extends AbstractParserTestCase
{
    private const DEFAULT_NAMESPACE = 'ezsettings';

    private $minimalConfig;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container->setParameter('ezsettings.default.var_dir', 'var'); // PS: Does not seem to take effect
        $this->container->setParameter('ezsettings.default.storage_dir', 'storage');
        $this->container->setParameter('ezsettings.ezdemo_site.var_dir', 'var/ezdemo_site');

        $this->container->set(ChainConfigResolver::class, $this->getChainConfigResolver());
    }

    protected function getContainerExtensions(): array
    {
        return [
            new EzPublishCoreExtension([new IO(new ComplexSettingParser())]),
        ];
    }

    protected function getMinimalConfiguration(): array
    {
        if ($this->minimalConfig === null) {
            $this->minimalConfig = Yaml::parse(file_get_contents(__DIR__ . '/../../Fixtures/ezpublish_minimal.yml'));
        }

        return $this->minimalConfig;
    }

    public function testHandlersConfig()
    {
        $config = [
            'system' => [
                'ezdemo_site' => [
                    'io' => [
                        'binarydata_handler' => 'cluster',
                        'metadata_handler' => 'cluster',
                    ],
                ],
            ],
        ];

        $this->load($config);

        $this->assertConfigResolverParameterValue('io.metadata_handler', 'cluster', 'ezdemo_site');
        $this->assertConfigResolverParameterValue('io.binarydata_handler', 'cluster', 'ezdemo_site');
    }

    /**
     * Tests that a complex default io.url_prefix will be set in a context where one of its dependencies is set.
     */
    public function testComplexIoUrlPrefix()
    {
        $this->load();

        // Should have been defined & converted in ezdemo_site
        $this->assertContainerBuilderHasParameter('ezsettings.ezdemo_site.io.url_prefix', 'var/ezdemo_site/storage');
        // Should have been converted in default
        $this->assertContainerBuilderHasParameter('ezsettings.default.io.url_prefix', 'var/storage');
    }

    /**
     * Tests that a complex default io.url_prefix will be set in a context where one of its dependencies is set.
     */
    public function testComplexIoLegacyUrlPrefix()
    {
        $this->load();

        // Should have been defined & converted in ezdemo_site
        $this->assertContainerBuilderHasParameter('ezsettings.ezdemo_site.io.legacy_url_prefix', 'var/ezdemo_site/storage');
        // Should have been converted in default
        $this->assertContainerBuilderHasParameter('ezsettings.default.io.legacy_url_prefix', 'var/storage');
    }

    /**
     * Tests that a complex default io.url_prefix will be set in a context where one of its dependencies is set.
     */
    public function testComplexIoRootDir()
    {
        $this->load();

        // Should have been defined & converted in ezdemo_site
        $this->assertContainerBuilderHasParameter('ezsettings.ezdemo_site.io.root_dir', '%webroot_dir%/var/ezdemo_site/storage');
        // Should have been converted in default
        $this->assertContainerBuilderHasParameter('ezsettings.default.io.root_dir', '%webroot_dir%/var/storage');
    }

    private function getChainConfigResolver(): ChainConfigResolver
    {
        $siteAccessProvider = $this->getSiteAccessProviderMock();

        $chainConfigResolver = new ChainConfigResolver();
        $globalConfigResolver = new ConfigResolver\GlobalScopeConfigResolver(self::DEFAULT_NAMESPACE);
        $globalConfigResolver->setContainer($this->container);
        $chainConfigResolver->addResolver($globalConfigResolver, 255);

        $saConfigResolver = new ConfigResolver\StaticSiteAccessConfigResolver(
            $siteAccessProvider,
            self::DEFAULT_NAMESPACE
        );
        $saConfigResolver->setContainer($this->container);
        $saConfigResolver->setSiteAccess(new SiteAccess('ezdemo_site'));
        $chainConfigResolver->addResolver($saConfigResolver, 100);

        $saGroupConfigResolver = new ConfigResolver\SiteAccessGroupConfigResolver(
            $siteAccessProvider,
            self::DEFAULT_NAMESPACE
        );
        $saGroupConfigResolver->setContainer($this->container);
        $saGroupConfigResolver->setSiteAccess(new SiteAccess('ezdemo_site'));
        $chainConfigResolver->addResolver($saGroupConfigResolver, 50);

        $defaultConfigResolver = new ConfigResolver\DefaultScopeConfigResolver(self::DEFAULT_NAMESPACE);
        $defaultConfigResolver->setContainer($this->container);
        $chainConfigResolver->addResolver($defaultConfigResolver, 0);

        return $chainConfigResolver;
    }

    protected function getSiteAccessProviderMock(): SiteAccessProviderInterface
    {
        $siteAccessProvider = $this->createMock(SiteAccessProviderInterface::class);
        $siteAccessProvider
            ->method('isDefined')
            ->willReturnMap([
                ['ezdemo_site', true],
                ['fre', true],
                ['ezdemo_site_admin', true],
                ['default', true],
                ['ezdemo_group', true],
                ['ezdemo_frontend_group', true],
            ]);
        $siteAccessProvider
            ->method('getSiteAccess')
            ->willReturnMap([
                ['ezdemo_site', $this->getSiteAccess('ezdemo_site', StaticSiteAccessProvider::class, [])],
                ['fre', $this->getSiteAccess('fre', StaticSiteAccessProvider::class, [])],
                ['ezdemo_site_admin', $this->getSiteAccess('ezdemo_site_admin', StaticSiteAccessProvider::class, [])],
                ['default', $this->getSiteAccess('default', StaticSiteAccessProvider::class, [])],
                ['ezdemo_group', $this->getSiteAccess('ezdemo_group', StaticSiteAccessProvider::class, [])],
                ['ezdemo_frontend_group', $this->getSiteAccess('ezdemo_frontend_group', StaticSiteAccessProvider::class, [])],
            ]);

        return $siteAccessProvider;
    }
}
