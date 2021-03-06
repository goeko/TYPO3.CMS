<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Form\Tests\Unit\Domain\Finishers;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Prophecy\Argument;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;
use TYPO3\CMS\Form\Domain\Finishers\FinisherContext;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Form\Service\TranslationService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case
 */
class AbstractFinisherTest extends UnitTestCase
{
    /**
     * @var array A backup of registered singleton instances
     */
    protected $singletonInstances = [];

    /**
     * Set up
     */
    public function setUp()
    {
        $this->singletonInstances = GeneralUtility::getSingletonInstances();
    }

    /**
     * Tear down
     */
    public function tearDown(): void
    {
        GeneralUtility::resetSingletonInstances($this->singletonInstances);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function parseOptionReturnsNullIfOptionNameIsTranslation(): void
    {
        $mockAbstractFinisher = $this->getAccessibleMockForAbstractClass(
            AbstractFinisher::class,
            [],
            '',
            false
        );

        $this->assertNull($mockAbstractFinisher->_call('parseOption', 'translation'));
    }

    /**
     * @test
     */
    public function parseOptionReturnsNullIfOptionNameNotExistsWithinOptions(): void
    {
        $mockAbstractFinisher = $this->getAccessibleMockForAbstractClass(
            AbstractFinisher::class,
            [],
            '',
            false
        );

        $mockAbstractFinisher->_set('options', []);

        $this->assertNull($mockAbstractFinisher->_call('parseOption', 'foo'));
    }

    /**
     * @test
     */
    public function parseOptionReturnsNullIfOptionNameNotExistsWithinDefaultOptions(): void
    {
        $mockAbstractFinisher = $this->getAccessibleMockForAbstractClass(
            AbstractFinisher::class,
            [],
            '',
            false
        );

        $mockAbstractFinisher->_set('options', []);

        $this->assertNull($mockAbstractFinisher->_call('parseOption', 'foo'));
    }

    /**
     * @test
     */
    public function parseOptionReturnsArrayOptionValuesAsArray(): void
    {
        $mockAbstractFinisher = $this->getAccessibleMockForAbstractClass(
            AbstractFinisher::class,
            [],
            '',
            false
        );

        $mockAbstractFinisher->_set('options', [
            'foo' => ['bar', 'foobar']
        ]);

        $expected = ['bar', 'foobar'];

        $this->assertSame($expected, $mockAbstractFinisher->_call('parseOption', 'foo'));
    }

    /**
     * @test
     */
    public function parseOptionReturnsBoolOptionValuesAsBool(): void
    {
        $mockAbstractFinisher = $this->getAccessibleMockForAbstractClass(
            AbstractFinisher::class,
            [],
            '',
            false
        );

        $mockAbstractFinisher->_set('options', [
            'foo1' => false,
        ]);

        $expected = false;

        $this->assertSame($expected, $mockAbstractFinisher->_call('parseOption', 'foo1'));
    }

    /**
     * @test
     */
    public function parseOptionReturnsValueFromFormRuntimeIfOptionNameReferenceAFormElementIdentifierWhoseValueIsAString(): void
    {
        $objectManagerProphecy = $this->prophesize(ObjectManager::class);
        GeneralUtility::setSingletonInstance(ObjectManager::class, $objectManagerProphecy->reveal());

        $mockTranslationService = $this->getAccessibleMock(TranslationService::class, [
            'translateFinisherOption'
        ], [], '', false);

        $mockTranslationService
            ->expects($this->any())
            ->method('translateFinisherOption')
            ->willReturnArgument(3);

        $objectManagerProphecy
            ->get(TranslationService::class)
            ->willReturn($mockTranslationService);

        $expected = 'element-value';
        $elementIdentifier = 'element-identifier-1';

        $mockAbstractFinisher = $this->getAccessibleMockForAbstractClass(
            AbstractFinisher::class,
            [],
            '',
            false
        );

        $mockAbstractFinisher->_set('options', [
            'subject' => '{' . $elementIdentifier . '}'
        ]);

        $finisherContextProphecy = $this->prophesize(FinisherContext::class);

        $formRuntimeProphecy = $this->prophesize(FormRuntime::class);
        $formRuntimeProphecy->offsetExists(Argument::exact($elementIdentifier))->willReturn(true);
        $formRuntimeProphecy->offsetGet(Argument::exact($elementIdentifier))->willReturn($expected);

        $finisherContextProphecy->getFormRuntime(Argument::cetera())
            ->willReturn($formRuntimeProphecy->reveal());

        $mockAbstractFinisher->_set('finisherContext', $finisherContextProphecy->reveal());

        $this->assertSame($expected, $mockAbstractFinisher->_call('parseOption', 'subject'));
    }

    /**
     * @test
     */
    public function parseOptionReturnsNoReplacedValueFromFormRuntimeIfOptionNameReferenceAFormElementIdentifierWhoseValueIsNotAString(): void
    {
        $objectManagerProphecy = $this->prophesize(ObjectManager::class);
        GeneralUtility::setSingletonInstance(ObjectManager::class, $objectManagerProphecy->reveal());

        $mockTranslationService = $this->getAccessibleMock(TranslationService::class, [
            'translateFinisherOption'
        ], [], '', false);

        $mockTranslationService
            ->expects($this->any())
            ->method('translateFinisherOption')
            ->willReturnArgument(3);

        $objectManagerProphecy
            ->get(TranslationService::class)
            ->willReturn($mockTranslationService);

        $elementIdentifier = 'element-identifier-1';
        $expected = '{' . $elementIdentifier . '}';

        $mockAbstractFinisher = $this->getAccessibleMockForAbstractClass(
            AbstractFinisher::class,
            [],
            '',
            false
        );

        $mockAbstractFinisher->_set('options', [
            'subject' => '{' . $elementIdentifier . '}'
        ]);

        $finisherContextProphecy = $this->prophesize(FinisherContext::class);

        $formRuntimeProphecy = $this->prophesize(FormRuntime::class);
        $formRuntimeProphecy->offsetExists(Argument::exact($elementIdentifier))->willReturn(true);
        $formElementValue = new \DateTime;
        $formRuntimeProphecy->offsetGet(Argument::exact($elementIdentifier))->willReturn($formElementValue);

        $finisherContextProphecy->getFormRuntime(Argument::cetera())
            ->willReturn($formRuntimeProphecy->reveal());

        $mockAbstractFinisher->_set('finisherContext', $finisherContextProphecy->reveal());

        $this->assertSame($expected, $mockAbstractFinisher->_call('parseOption', 'subject'));
    }

    /**
     * @test
     */
    public function parseOptionReturnsNoReplacedValueFromFormRuntimeIfOptionNameReferenceANonExistingFormElement(): void
    {
        $objectManagerProphecy = $this->prophesize(ObjectManager::class);
        GeneralUtility::setSingletonInstance(ObjectManager::class, $objectManagerProphecy->reveal());

        $mockTranslationService = $this->getAccessibleMock(TranslationService::class, [
            'translateFinisherOption'
        ], [], '', false);

        $mockTranslationService
            ->expects($this->any())
            ->method('translateFinisherOption')
            ->willReturnArgument(3);

        $objectManagerProphecy
            ->get(TranslationService::class)
            ->willReturn($mockTranslationService);

        $elementIdentifier = 'element-identifier-1';

        $mockAbstractFinisher = $this->getAccessibleMockForAbstractClass(
            AbstractFinisher::class,
            [],
            '',
            false
        );

        $mockAbstractFinisher->_set('options', [
            'subject' => '{' . $elementIdentifier . '}'
        ]);

        $finisherContextProphecy = $this->prophesize(FinisherContext::class);

        $formRuntimeProphecy = $this->prophesize(FormRuntime::class);
        $formRuntimeProphecy->offsetExists(Argument::cetera())->willReturn(true);
        $formRuntimeProphecy->offsetGet(Argument::cetera())->willReturn(false);

        $finisherContextProphecy->getFormRuntime(Argument::cetera())
            ->willReturn($formRuntimeProphecy->reveal());

        $mockAbstractFinisher->_set('finisherContext', $finisherContextProphecy->reveal());

        $expected = '{' . $elementIdentifier . '}';
        $this->assertSame($expected, $mockAbstractFinisher->_call('parseOption', 'subject'));
    }

    /**
     * @test
     */
    public function parseOptionReturnsDefaultOptionValueIfOptionNameNotExistsWithinOptionsButWithinDefaultOptions(): void
    {
        $objectManagerProphecy = $this->prophesize(ObjectManager::class);
        GeneralUtility::setSingletonInstance(ObjectManager::class, $objectManagerProphecy->reveal());

        $mockTranslationService = $this->getAccessibleMock(TranslationService::class, [
            'translateFinisherOption'
        ], [], '', false);

        $mockTranslationService
            ->expects($this->any())
            ->method('translateFinisherOption')
            ->willReturnArgument(3);

        $objectManagerProphecy
            ->get(TranslationService::class)
            ->willReturn($mockTranslationService);

        $expected = 'defaultValue';

        $mockAbstractFinisher = $this->getAccessibleMockForAbstractClass(
            AbstractFinisher::class,
            [],
            '',
            false
        );

        $mockAbstractFinisher->_set('options', []);
        $mockAbstractFinisher->_set('defaultOptions', [
            'subject' => $expected
        ]);

        $finisherContextProphecy = $this->prophesize(FinisherContext::class);

        $formRuntimeProphecy = $this->prophesize(FormRuntime::class);
        $formRuntimeProphecy->offsetExists(Argument::cetera())->willReturn(true);
        $formRuntimeProphecy->offsetGet(Argument::cetera())->willReturn(false);

        $finisherContextProphecy->getFormRuntime(Argument::cetera())
            ->willReturn($formRuntimeProphecy->reveal());

        $mockAbstractFinisher->_set('finisherContext', $finisherContextProphecy->reveal());

        $this->assertSame($expected, $mockAbstractFinisher->_call('parseOption', 'subject'));
    }

    /**
     * @test
     */
    public function parseOptionReturnsDefaultOptionValueIfOptionValueIsAFormElementReferenceAndTheFormElementValueIsEmpty(): void
    {
        $objectManagerProphecy = $this->prophesize(ObjectManager::class);
        GeneralUtility::setSingletonInstance(ObjectManager::class, $objectManagerProphecy->reveal());

        $mockTranslationService = $this->getAccessibleMock(TranslationService::class, [
            'translateFinisherOption'
        ], [], '', false);

        $mockTranslationService
            ->expects($this->any())
            ->method('translateFinisherOption')
            ->willReturnArgument(3);

        $objectManagerProphecy
            ->get(TranslationService::class)
            ->willReturn($mockTranslationService);

        $elementIdentifier = 'element-identifier-1';
        $expected = 'defaultValue';

        $mockAbstractFinisher = $this->getAccessibleMockForAbstractClass(
            AbstractFinisher::class,
            [],
            '',
            false
        );

        $mockAbstractFinisher->_set('options', [
            'subject' => '{' . $elementIdentifier . '}'
        ]);
        $mockAbstractFinisher->_set('defaultOptions', [
            'subject' => $expected
        ]);

        $finisherContextProphecy = $this->prophesize(FinisherContext::class);

        $formRuntimeProphecy = $this->prophesize(FormRuntime::class);
        $formRuntimeProphecy->offsetExists(Argument::exact($elementIdentifier))->willReturn(true);
        $formRuntimeProphecy->offsetGet(Argument::exact($elementIdentifier))->willReturn('');

        $finisherContextProphecy->getFormRuntime(Argument::cetera())
            ->willReturn($formRuntimeProphecy->reveal());

        $mockAbstractFinisher->_set('finisherContext', $finisherContextProphecy->reveal());

        $this->assertSame($expected, $mockAbstractFinisher->_call('parseOption', 'subject'));
    }

    /**
     * @test
     */
    public function parseOptionReturnsTimestampIfOptionValueIsATimestampRequestTrigger(): void
    {
        $objectManagerProphecy = $this->prophesize(ObjectManager::class);
        GeneralUtility::setSingletonInstance(ObjectManager::class, $objectManagerProphecy->reveal());

        $mockTranslationService = $this->getAccessibleMock(TranslationService::class, [
            'translateFinisherOption'
        ], [], '', false);

        $mockTranslationService
            ->expects($this->any())
            ->method('translateFinisherOption')
            ->willReturnArgument(3);

        $objectManagerProphecy
            ->get(TranslationService::class)
            ->willReturn($mockTranslationService);

        $mockAbstractFinisher = $this->getAccessibleMockForAbstractClass(
            AbstractFinisher::class,
            [],
            '',
            false
        );

        $mockAbstractFinisher->_set('options', [
            'crdate' => '{__currentTimestamp}'
        ]);

        $finisherContextProphecy = $this->prophesize(FinisherContext::class);

        $formRuntimeProphecy = $this->prophesize(FormRuntime::class);

        $finisherContextProphecy->getFormRuntime(Argument::cetera())
            ->willReturn($formRuntimeProphecy->reveal());

        $mockAbstractFinisher->_set('finisherContext', $finisherContextProphecy->reveal());

        $expected = '#^([0-9]{10})$#';
        $this->assertEquals(1, preg_match($expected, $mockAbstractFinisher->_call('parseOption', 'crdate')));
    }
}
