<?php

namespace Z38\Bundle\UiBundle\Security;

use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * ExpressionChecker checks if a given expression is satisfied
 */
class ExpressionChecker
{
    private $language;
    private $tokenStorage;
    private $authChecker;
    private $trustResolver;
    private $requestStack;
    private $roleHierarchy;

    public function __construct(ExpressionLanguage $language, TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authChecker, AuthenticationTrustResolverInterface $trustResolver, RequestStack $requestStack, RoleHierarchyInterface $roleHierarchy = null)
    {
        $this->language = $language;
        $this->tokenStorage = $tokenStorage;
        $this->authChecker = $authChecker;
        $this->trustResolver = $trustResolver;
        $this->requestStack = $requestStack;
        $this->roleHierarchy = $roleHierarchy;
    }

    /**
     * Check whether the expression evaluates to true
     *
     * @param string $expression
     * @param array  $variables
     */
    public function check($expression, array $variables)
    {
        return (bool) $this->language->evaluate($expression, $this->getVariables($variables));
    }

    // code should be in sync with Sensio\Bundle\FrameworkExtraBundle\EventListener\SecurityListener
    private function getVariables($variables)
    {
        $token = $this->tokenStorage->getToken();
        $request = $this->requestStack->getCurrentRequest();

        if (null !== $this->roleHierarchy) {
            $roles = $this->roleHierarchy->getReachableRoles($token->getRoles());
        } else {
            $roles = $token->getRoles();
        }

        return array_merge([
            'token' => $token,
            'user' => $token->getUser(),
            'object' => $request,
            'request' => $request,
            'roles' => array_map(function ($role) { return $role->getRole(); }, $roles),
            'trust_resolver' => $this->trustResolver,
            'auth_checker' => $this->authChecker,
        ], $variables);
    }
}
