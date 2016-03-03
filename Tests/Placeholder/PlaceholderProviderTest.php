<?php

namespace Z38\Bundle\UiBundle\Tests\Placeholder;

use Oro\Component\Config\Resolver\ResolverInterface;
use Z38\Bundle\UiBundle\Placeholder\PlaceholderProvider;

class PlaceholderProviderTest extends \PHPUnit_Framework_TestCase
{
    const TEST_PLACEHOLDER = 'test_placeholder';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ResolverInterface
     */
    protected $resolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ExpressionChecker
     */
    protected $expressionChecker;

    protected function setUp()
    {
        $this->resolver = $this->getMock('Oro\Component\Config\Resolver\ResolverInterface');
        $this->expressionChecker = $this->getMockBuilder('Z38\Bundle\UiBundle\Security\ExpressionChecker')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testOnlyTemplateDefined()
    {
        $items = ['placeholder_item' => [
            'template' => 'template',
        ]];

        $variables = ['foo' => 'bar'];

        $provider = $this->createProvider($items);

        $this->resolver->expects($this->at(0))
            ->method('resolve')
            ->with($items['placeholder_item'], $variables)
            ->will($this->returnValue($items['placeholder_item']));
        $actual = $provider->getPlaceholderItems(self::TEST_PLACEHOLDER, $variables);

        $this->assertSame(
            [['template' => 'template']],
            $actual
        );
    }

    public function testTemplateAndDataDefined()
    {
        $items = ['placeholder_item' => [
            'template' => 'template',
            'data' => '@service->getData($entity$)',
        ]];

        $variables = ['foo' => 'bar'];

        $provider = $this->createProvider($items);
        $this->resolver->expects($this->at(0))
            ->method('resolve')
            ->with($items['placeholder_item'], $variables)
            ->will($this->returnValue($items['placeholder_item']));

        $actual = $provider->getPlaceholderItems(self::TEST_PLACEHOLDER, $variables);

        $this->assertSame(
            [$items['placeholder_item']],
            $actual
        );
    }

    public function testApplicableStringConditionSuccess()
    {
        $items = ['placeholder_item' => [
            'template' => 'template',
            'applicable' => '@service1->isApplicable($entity$)',
        ]];

        $variables = ['foo' => 'bar'];

        $provider = $this->createProvider($items);
        $this->resolver->expects($this->at(0))
            ->method('resolve')
            ->with(['applicable' => $items['placeholder_item']['applicable']], $variables)
            ->will($this->returnValue(['applicable' => true]));
        unset($items['placeholder_item']['applicable']);
        $this->resolver->expects($this->at(1))
            ->method('resolve')
            ->with($items['placeholder_item'], $variables)
            ->will($this->returnValue($items['placeholder_item']));

        $actual = $provider->getPlaceholderItems(self::TEST_PLACEHOLDER, $variables);

        $this->assertSame(
            [$items['placeholder_item']],
            $actual
        );
    }

    public function testApplicableArrayConditionsSuccess()
    {
        $items = ['placeholder_item' => [
            'template' => 'template',
            'applicable' => ['@service1->isApplicable($entity$)', '@service1->isApplicable($entity$)'],
        ]];

        $variables = ['foo' => 'bar'];

        $provider = $this->createProvider($items);
        $this->resolver->expects($this->at(0))
            ->method('resolve')
            ->with(['applicable' => $items['placeholder_item']['applicable'][0]], $variables)
            ->will($this->returnValue(['applicable' => true]));
        $this->resolver->expects($this->at(1))
            ->method('resolve')
            ->with(['applicable' => $items['placeholder_item']['applicable'][1]], $variables)
            ->will($this->returnValue(['applicable' => true]));
        unset($items['placeholder_item']['applicable']);
        $this->resolver->expects($this->at(2))
            ->method('resolve')
            ->with($items['placeholder_item'], $variables)
            ->will($this->returnValue($items['placeholder_item']));

        $actual = $provider->getPlaceholderItems(self::TEST_PLACEHOLDER, $variables);

        $this->assertSame(
            [$items['placeholder_item']],
            $actual
        );
    }

    public function testApplicableArrayConditionsFail()
    {
        $items = ['placeholder_item' => [
            'template' => 'template',
            'applicable' => ['@service1->isApplicable($entity$)', '@service1->isApplicable($entity$)'],
        ]];

        $variables = ['foo' => 'bar'];

        $provider = $this->createProvider($items);
        $this->resolver->expects($this->at(0))
            ->method('resolve')
            ->with(['applicable' => $items['placeholder_item']['applicable'][0]], $variables)
            ->will($this->returnValue(['applicable' => false]));

        $actual = $provider->getPlaceholderItems(self::TEST_PLACEHOLDER, $variables);

        $this->assertSame([], $actual);
    }

    public function testSecureConditionSuccess()
    {
        $items = ['placeholder_item' => [
            'template' => 'template',
            'secure' => 'has_role("ROLE_ADMIN")',
        ]];

        $variables = ['foo' => 'bar'];

        $provider = $this->createProvider($items);
        $this->expressionChecker->expects($this->at(0))
            ->method('check')
            ->with('has_role("ROLE_ADMIN")', $variables)
            ->will($this->returnValue(true));

        $actual = $provider->getPlaceholderItems(self::TEST_PLACEHOLDER, $variables);
        unset($items['placeholder_item']['secure']);
        $this->assertSame([], $actual);
    }

    public function testSecureConditionFail()
    {
        $items = ['placeholder_item' => [
            'template' => 'template',
            'secure' => 'has_role("ROLE_ADMIN")',
        ]];

        $variables = ['foo' => 'bar'];

        $provider = $this->createProvider($items);
        $this->expressionChecker->expects($this->at(0))
            ->method('check')
            ->with('has_role("ROLE_ADMIN")', $variables)
            ->will($this->returnValue(false));

        $actual = $provider->getPlaceholderItems(self::TEST_PLACEHOLDER, $variables);
        unset($items['placeholder_item']['secure']);
        $this->assertSame([], $actual);
    }

    /**
     * @param array $items
     *
     * @return PlaceholderProvider
     */
    protected function createProvider(array $items)
    {
        $placeholders = [
            'placeholders' => [
                self::TEST_PLACEHOLDER => [
                    'items' => array_keys($items),
                ],
            ],
            'items' => $items,
        ];

        return new PlaceholderProvider($placeholders, $this->resolver, $this->expressionChecker);
    }
}
