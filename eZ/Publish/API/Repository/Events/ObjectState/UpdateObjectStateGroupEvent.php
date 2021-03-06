<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\ObjectState;

use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct;
use eZ\Publish\SPI\Repository\Event\AfterEvent;

final class UpdateObjectStateGroupEvent extends AfterEvent
{
    /** @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup */
    private $updatedObjectStateGroup;

    /** @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup */
    private $objectStateGroup;

    /** @var \eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct */
    private $objectStateGroupUpdateStruct;

    public function __construct(
        ObjectStateGroup $updatedObjectStateGroup,
        ObjectStateGroup $objectStateGroup,
        ObjectStateGroupUpdateStruct $objectStateGroupUpdateStruct
    ) {
        $this->updatedObjectStateGroup = $updatedObjectStateGroup;
        $this->objectStateGroup = $objectStateGroup;
        $this->objectStateGroupUpdateStruct = $objectStateGroupUpdateStruct;
    }

    public function getUpdatedObjectStateGroup(): ObjectStateGroup
    {
        return $this->updatedObjectStateGroup;
    }

    public function getObjectStateGroup(): ObjectStateGroup
    {
        return $this->objectStateGroup;
    }

    public function getObjectStateGroupUpdateStruct(): ObjectStateGroupUpdateStruct
    {
        return $this->objectStateGroupUpdateStruct;
    }
}
