<?php

namespace Attla\Token;

/**
 * @method static Creator create()
 * @method static Parser parse(string $token)
 *
 * @see \Attla\Token\Creator
 * @see \Attla\Token\Parser
 */
class Factory
{
    /**
     * Returns a token creation manager.
     *
     * @return \Attla\Token\Creator
     */
    public static function create(): Creator
    {
        return new Creator();
    }

    /**
     * Returns a token parse manager.
     *
     * @param string $token
     * @return \Attla\Token\Parser
     */
    public static function parse(string $token): Parser
    {
        return new Parser($token);
    }
}
