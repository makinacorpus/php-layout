<?php

namespace MakinaCorpus\Layout\Twig;

use MakinaCorpus\Layout\Context\Context;
use MakinaCorpus\Layout\Render\EditRendererDecorator;
use MakinaCorpus\Layout\Render\Renderer;
use MakinaCorpus\Layout\Storage\LayoutInterface;

class LayoutExtension extends \Twig_Extension
{
    private $context;
    private $renderer;

    /**
     * Default constructor
     */
    public function __construct(Renderer $renderer, Context $context)
    {
        $this->context = $context;
        $this->renderer = $renderer;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('layout', [$this, 'renderLayout'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Renders a single layout
     */
    public function renderLayout(LayoutInterface $layout) : string
    {
        $gridRenderer = $this->renderer->getGridRenderer();

        if ($gridRenderer instanceof EditRendererDecorator) {
            $isEditable = false;

            if ($this->context->hasToken()) {
                $token = $this->context->getToken();

                if ($token->contains($layout->getId())) {
                    // We are in edit mode, we must propagate the token
                    // @todo context and token should be attached to the request
                    //   and not be a global object: thus request or context should
                    //   be propagated to the renderer via render() methods, and to
                    //   the grid renderer as well all along the run stack; this
                    //   would make the whole algorithm stateless
                    $isEditable = true;
                    $gridRenderer->setCurrentToken($token);
                }
            }

            if (!$isEditable) {
                // Always drop the token, no matter what happened, we may
                // have kept a token from another rendering
                $gridRenderer->dropToken();
            }
        }

        return $this->renderer->render($layout->getTopLevelContainer());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'layout';
    }
}
