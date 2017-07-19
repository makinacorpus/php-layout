<?php

namespace MakinaCorpus\Layout\Context;

/**
 * Default implementation for testing purpose, creates tokens with an overkill
 * method, but at least we're sure they're safe (or should be).
 */
class DefaultTokenGenerator implements TokenGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function create() : string
    {
        return hash_hmac(
            'md5',
            random_bytes(64),
            random_bytes(64),
            false
        );
    }
}
