<?php
namespace Z38\Bundle\UiBundle\Tests\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Z38\Bundle\UiBundle\DependencyInjection\Configuration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigTreeBuilder()
    {
        $bundleConfiguration = new Configuration();
        $this->assertTrue($bundleConfiguration->getConfigTreeBuilder() instanceof TreeBuilder);
    }
}
