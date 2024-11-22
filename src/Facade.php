<?php

namespace Attla\Token;

/**
 * @method static Creator create()
 * @method static Parser parse(string $token)
 *
 * @see \Attla\Token\Creator
 * @see \Attla\Token\Parser
 */
class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * Get the registered name of the component
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Factory::class;
    }
}
