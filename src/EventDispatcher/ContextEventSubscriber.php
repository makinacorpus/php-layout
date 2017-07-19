<?php

namespace MakinaCorpus\Layout\Context;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Main listener that will handle gracefully context life time
 */
class ContextEventSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {

    }
}
