<?php

namespace MakinaCorpus\Layout\Context;

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
