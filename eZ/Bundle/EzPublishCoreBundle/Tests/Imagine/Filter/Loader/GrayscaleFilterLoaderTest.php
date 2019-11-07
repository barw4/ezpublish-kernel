<?php

/**
 * File containing the GrayscaleFilterLoaderTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\Filter\Loader;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader\GrayscaleFilterLoader;
use Imagine\Effects\EffectsInterface;
use Imagine\Image\ImageInterface;
use PHPUnit\Framework\TestCase;

class GrayscaleFilterLoaderTest extends TestCase
{
    public function testLoad()
    {
        $image = $this->createMock(ImageInterface::class);
        $effects = $this->createMock(EffectsInterface::class);
        $image
            ->expects($this->once())
            ->method('effects')
            ->willReturn($effects);
        $effects
            ->expects($this->once())
            ->method('grayscale')
            ->willReturn($effects);

        $loader = new GrayscaleFilterLoader();
        $this->assertSame($image, $loader->load($image));
    }
}
