<?php

namespace MakinaCorpus\Layout\Controller;

/**
 * Unique token generator
 */
interface TokenGeneratorInterface
{
    /**
     * Compute a unique token
     *
     * @return string
     */
    public function create() : string;
}
