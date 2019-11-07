<?php

/**
 * File containing the ValueObjectVisitorDispatcherTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\Tests\Output;

use eZ\Publish\Core\REST\Common;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use stdClass;
use PHPUnit\Framework\TestCase;

/**
 * Visitor test.
 */
class ValueObjectVisitorDispatcherTest extends TestCase
{
    /** @var Common\Output\Visitor|\PHPUnit\Framework\MockObject\MockObject */
    private $outputVisitorMock;

    /** @var Common\Output\Generator|\PHPUnit\Framework\MockObject\MockObject */
    private $outputGeneratorMock;

    public function testVisitValueObject()
    {
        $data = new stdClass();

        $visitor = $this->getValueObjectVisitorMock();
        $visitor
            ->expects($this->at(0))
            ->method('visit')
            ->with($this->getOutputVisitorMock(), $this->getOutputGeneratorMock(), $data);

        $valueObjectDispatcher = $this->getValueObjectDispatcher();
        $valueObjectDispatcher->addVisitor('stdClass', $visitor);

        $valueObjectDispatcher->visit($data);
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\InvalidTypeException
     */
    public function testVisitValueObjectInvalidType()
    {
        $this->getValueObjectDispatcher()->visit(42);
    }

    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Output\Exceptions\NoVisitorFoundException
     */
    public function testVisitValueObjectNoMatch()
    {
        $dispatcher = $this->getValueObjectDispatcher();

        $dispatcher->visit(new stdClass());
    }

    public function testVisitValueObjectParentMatch()
    {
        $data = new ValueObject();

        $valueObjectVisitor = $this->getValueObjectVisitorMock();
        $valueObjectVisitor
            ->expects($this->at(0))
            ->method('visit')
            ->with($this->getOutputVisitorMock(), $this->getOutputGeneratorMock(), $data);

        $dispatcher = $this->getValueObjectDispatcher();
        $dispatcher->addVisitor('stdClass', $valueObjectVisitor);

        $dispatcher->visit($data);
    }

    public function testVisitValueObjectSecondRuleParentMatch()
    {
        $data = new ValueObject();

        $valueObjectVisitor1 = $this->getValueObjectVisitorMock();
        $valueObjectVisitor2 = $this->getValueObjectVisitorMock();

        $dispatcher = $this->getValueObjectDispatcher();
        $dispatcher->addVisitor('WontMatch', $valueObjectVisitor1);
        $dispatcher->addVisitor('stdClass', $valueObjectVisitor2);

        $valueObjectVisitor1
            ->expects($this->never())
            ->method('visit');

        $valueObjectVisitor2
            ->expects($this->at(0))
            ->method('visit')
            ->with($this->getOutputVisitorMock(), $this->getOutputGeneratorMock(), $data);

        $dispatcher->visit($data);
    }

    /**
     * @return Common\Output\ValueObjectVisitorDispatcher
     */
    private function getValueObjectDispatcher()
    {
        $dispatcher = new Common\Output\ValueObjectVisitorDispatcher();
        $dispatcher->setOutputGenerator($this->getOutputGeneratorMock());
        $dispatcher->setOutputVisitor($this->getOutputVisitorMock());

        return $dispatcher;
    }

    /**
     * @return \eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getValueObjectVisitorMock()
    {
        return $this->getMockForAbstractClass(ValueObjectVisitor::class);
    }

    /**
     * @return Common\Output\Visitor|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getOutputVisitorMock()
    {
        if (!isset($this->outputVisitorMock)) {
            $this->outputVisitorMock = $this->createMock(Visitor::class);
        }

        return $this->outputVisitorMock;
    }

    /**
     * @return Common\Output\Generator|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getOutputGeneratorMock()
    {
        if (!isset($this->outputGeneratorMock)) {
            $this->outputGeneratorMock = $this->createMock(Generator::class);
        }

        return $this->outputGeneratorMock;
    }
}
