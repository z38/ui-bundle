<?php

namespace Z38\Bundle\UiBundle\Twig\Parser;

class PlaceholderTokenParser extends \Twig_TokenParser
{
    /**
     * {@inheritdoc}
     */
    public function parse(\Twig_Token $token)
    {
        $stream = $this->parser->getStream();
        $expressionParser = $this->parser->getExpressionParser();

        if ($stream->test(\Twig_Token::NAME_TYPE)) {
            $currentToken = $stream->getCurrent();
            $currentValue = $currentToken->getValue();
            $currentLine = $currentToken->getLine();

            // Creates expression: placeholder_name|default('placeholder_name')
            // To parse either variable value or name
            $name = new \Twig_Node_Expression_Filter_Default(
                new \Twig_Node_Expression_Name($currentValue, $currentLine),
                new \Twig_Node_Expression_Constant('default', $currentLine),
                new \Twig_Node(
                    [
                        new \Twig_Node_Expression_Constant(
                            $currentValue,
                            $currentLine
                        ),
                    ],
                    [],
                    $currentLine
                ),
                $currentLine
            );

            $stream->next();
        } else {
            $name = $expressionParser->parseExpression();
        }

        if ($stream->nextIf(\Twig_Token::NAME_TYPE, 'with')) {
            $variables = $expressionParser->parseExpression();
        } else {
            $variables = new \Twig_Node_Expression_Constant([], $token->getLine());
        }

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        // build expression to call 'placeholder' function
        $expr = new \Twig_Node_Expression_Function(
            'placeholder',
            new \Twig_Node(
                [
                    'name' => $name,
                    'variables' => $variables,
                ]
            ),
            $token->getLine()
        );

        return new \Twig_Node_Print($expr, $token->getLine(), $this->getTag());
    }

    /**
     * {@inheritdoc}
     */
    public function getTag()
    {
        return 'placeholder';
    }
}
