<?php

namespace Z38\Bundle\UiBundle\Twig;

use Symfony\Bridge\Twig\Extension\HttpKernelExtension;
use Symfony\Component\HttpFoundation\RequestStack;
use Z38\Bundle\UiBundle\Placeholder\PlaceholderProvider;
use Z38\Bundle\UiBundle\Twig\Parser\PlaceholderTokenParser;

class PlaceholderExtension extends \Twig_Extension
{
    const EXTENSION_NAME = 'z38_ui_placeholder';

    /**
     * @var \Twig_Environment
     */
    protected $environment;

    /**
     * @var PlaceholderProvider
     */
    protected $placeholder;

    /**
     * @var HttpKernelExtension
     */
    protected $kernelExtension;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @param \Twig_Environment   $environment
     * @param PlaceholderProvider $placeholder
     * @param HttpKernelExtension $kernelExtension
     */
    public function __construct(
        \Twig_Environment $environment,
        PlaceholderProvider $placeholder,
        HttpKernelExtension $kernelExtension,
        RequestStack $requestStack
    ) {
        $this->environment     = $environment;
        $this->placeholder     = $placeholder;
        $this->kernelExtension = $kernelExtension;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'placeholder',
                [$this, 'renderPlaceholder'],
                ['is_safe' => ['html']]
            )
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getTokenParsers()
    {
        return array(
            new PlaceholderTokenParser()
        );
    }

    /**
     * Render placeholder by name
     *
     * @param string $name
     * @param array  $variables
     * @param array  $attributes Supported attributes:
     *                           'delimiter' => string
     * @return string|array
     */
    public function renderPlaceholder($name, array $variables = array(), array $attributes = array())
    {
        return implode(
            isset($attributes['delimiter']) ? $attributes['delimiter'] : '',
            $this->getPlaceholderData($name, $variables, true)
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::EXTENSION_NAME;
    }

    /**
     * Renders the given item.
     *
     * @param array $item
     * @param array $variables
     * @return string
     * @throws \RuntimeException If placeholder cannot be rendered.
     */
    protected function renderItemContent(array $item, array $variables)
    {
        if (isset($item['data']) || array_key_exists('data', $item)) {
            $variables['data'] = $item['data'];
        }

        if (isset($item['template'])) {
            return $this->environment->render($item['template'], $variables);
        }

        if (isset($item['action'])) {
            $query = array();
            if (($request = $this->requestStack->getCurrentRequest()) !== null) {
                $query = $request->query->all();
            }

            return $this->kernelExtension->renderFragment(
                $this->kernelExtension->controller($item['action'], $variables, $query)
            );
        }

        throw new \RuntimeException(
            sprintf(
                'Cannot render placeholder item with keys "%s". Expects "template" or "action" key.',
                implode('", "', $item)
            )
        );
    }

    /**
     * @param string $name
     * @param array  $variables
     * @return array
     */
    protected function getPlaceholderData($name, $variables)
    {
        $result = array();

        $items = $this->placeholder->getPlaceholderItems($name, $variables);
        foreach ($items as $item) {
            $result[] = $this->renderItemContent($item, $variables);
        }

        return $result;
    }
}
