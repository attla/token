<?php

namespace Benchmark;

use Attla\Token\Factory as Token;

class TokenBench
{
    /**
     * Secret passphrase
     *
     * @var string
     */
    protected $secret = 'Now I am become Death, the destroyer of worlds.';

    /**
     * Benchmark data example
     *
     * @var array
     */
    protected $data = [
        'name'  => 'John Doe',
        'email' => 'john@e.com',
    ];

    /**
     * Benchmark encoded data example
     *
     * @var string
     */
    protected $encodedData = '';

    public function __construct()
    {
        $this->encodedData = $this->benchEncode();
    }

    /** @Revs(1000) */
    public function benchEncode()
    {
        return Token::create()
            ->secret($this->secret)
            ->payload($this->data)
            ->get();
    }

    /** @Revs(1000) */
    public function benchDecode()
    {
        Token::parse($this->encodedData)
            ->secret($this->secret)
            ->get();
    }

    /** @Revs(1000) */
    public function benchEncode_Decode()
    {
        $token = Token::create()
            ->secret($this->secret)
            ->payload($this->data)
            ->get();

        Token::parse($token)->secret($this->secret)->get();
    }
}
