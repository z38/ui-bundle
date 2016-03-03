<?php

namespace Z38\Bundle\UiBundle\Tests\Security;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Z38\Bundle\UiBundle\Security\ExpressionChecker;
use Z38\Bundle\UiBundle\Security\ExpressionLanguage;

class ExpressionCheckerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider expressions
     */
    public function testCheck($expression, $variables, $expected)
    {
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $token->method('getRoles')->will($this->returnValue([]));

        $tokenStorage = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface');
        $tokenStorage->method('getToken')->will($this->returnValue($token));

        $authChecker = $this->getMock('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface');
        $authChecker->method('isGranted')->will($this->throwException(new AccessDeniedException()));

        $trustResolver = $this->getMock('Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface');

        $requestStack = new RequestStack();

        $language = new ExpressionLanguage();

        $checker = new ExpressionChecker($language, $tokenStorage, $authChecker, $trustResolver, $requestStack, null);

        $this->assertSame($expected, $checker->check($expression, $variables));
    }

    public function expressions()
    {
        return [
            ['a', ['a' => true], true],
            ['a', ['a' => false], false],
            ['has_role("ROLE_BAR") or has_role("ROLE_FOO")', [], false],
            ['token', [], true],
            ['token', ['token' => false], false],
        ];
    }
}
