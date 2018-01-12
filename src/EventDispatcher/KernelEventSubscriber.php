<?php

namespace MakinaCorpus\Layout\EventDispatcher;

use MakinaCorpus\Layout\Context\Context;
use MakinaCorpus\Layout\Error\InvalidTokenError;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;

/**
 * React upon framework events
 */
class KernelEventSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['onRequest', 0],
            ],
        ];
    }

    private $context;
    private $dispatcher;

    /**
     * Default constructor
     */
    public function __construct(Context $context, EventDispatcherInterface $dispatcher)
    {
        $this->context = $context;
        $this->dispatcher = $dispatcher;
    }

    /**
     * On request initialize context
     */
    public function onRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (HttpKernel::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $collectEvent = new CollectLayoutEvent($this->context);
        $this->dispatcher->dispatch(CollectLayoutEvent::EVENT_NAME, $collectEvent);
        $tokenFound = false;

        // By setting the token after the event has run and context has been
        // populated, we ensure that any accidental layout load in the context
        // that might have happened will be reset to allow transparent editable
        // temporary tokens to be loaded instead.
        if ($tokenString = $request->get(Context::LAYOUT_TOKEN_PARAM)) {
            try {
                $this->context->setToken($tokenString);
                $tokenFound = true;
            } catch (InvalidTokenError $e) {
                // Fallback on non-edit mode
            }
        }

        if (!$tokenFound && $request->isXmlHttpRequest()) {
            if ($tokenString = $request->get('token')) {
                try {
                    $this->context->setToken($tokenString);
                } catch (InvalidTokenError $e) {
                    // Fallback on non-edit mode
                }
            }
        }
    }
}
