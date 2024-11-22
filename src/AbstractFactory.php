<?php

namespace Attla\Token;

abstract class AbstractFactory
{
    /** Encode/Decode the token */
    abstract public function get();

    /**
     * Retrieve the encoded token
     *
     * @return string
     */
    abstract public function __toString(): string;

    /**
     * Set token body as associative when it can be converted.
     *
     * @return $this
     */
    public function associative(): self
    {
        $this->token->associative = true;
        return $this;
    }

    /**
     * Set token body as object when it can be converted.
     *
     * @return $this
     */
    public function asObject(): self
    {
        $this->token->associative = false;
        return $this;
    }
}
