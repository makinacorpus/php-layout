<?php

namespace MakinaCorpus\Layout\Controller;

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
        return preg_replace('/[^a-zA-Z0-9]+/', '', base64_encode(hash_hmac(
            'sha512',
            random_bytes(64),
            random_bytes(64),
            true
        )));
    }
}
