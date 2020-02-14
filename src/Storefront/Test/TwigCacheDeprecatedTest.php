<?php declare(strict_types=1);

namespace Shopware\Storefront\Test;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Adapter\Twig\TemplateFinder;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\Kernel;
use Shopware\Storefront\Test\fixtures\BundleFixture;

/**
 * @deprecated tag:v6.3.0 can safely be removed, once we removed the `TemplateFinder::registerBundles()`
 */
class TwigCacheDeprecatedTest extends TestCase
{
    use KernelTestBehaviour;

    public function testChangeCacheOnDifferentPlugins12(): void
    {
        [$twig, $templateFinder] = $this->createFinder([
            new BundleFixture('Storefront', __DIR__ . '/fixtures/Storefront/'),
            new BundleFixture('TestPlugin2', __DIR__ . '/fixtures/Plugins/TestPlugin2'),
        ]);

        $templateName = 'storefront/frontend/index.html.twig';

        $templateFinder->find($templateName);

        $firstCacheKey = $twig->getCache(false)->generateKey($templateName, static::class);

        [$twig, $templateFinder] = $this->createFinder([
            new BundleFixture('Storefront', __DIR__ . '/fixtures/Storefront/'),
            new BundleFixture('TestPlugin1', __DIR__ . '/fixtures/Plugins/TestPlugin1'),
            new BundleFixture('TestPlugin2', __DIR__ . '/fixtures/Plugins/TestPlugin2'),
        ]);

        $templateFinder->find($templateName);
        $secondCacheKey = $twig->getCache(false)->generateKey($templateName, static::class);

        static::assertNotEquals($firstCacheKey, $secondCacheKey);
    }

    private function createFinder(array $bundles): array
    {
        $twig = $this->getContainer()->get('twig');

        $templateFinder = $this->getContainer()->get(TemplateFinder::class);

        $loader = $this->getContainer()->get('twig.loader.native_filesystem');
        /** @var BundleFixture $bundle */
        foreach ($bundles as $bundle) {
            $directory = $bundle->getPath() . '/Resources/views';
            $loader->addPath($directory);
            $loader->addPath($directory, $bundle->getName());
        }

        $kernel = $this->createMock(Kernel::class);
        $kernel->expects(static::any())
                ->method('getBundles')
                ->willReturn($bundles);

        $templateFinder->registerBundles($kernel);

        return [$twig, $templateFinder];
    }
}
