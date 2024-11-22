<?php

namespace Benchmark;

use Attla\Token\Factory as Token;

class TokenBench
{
    protected $secret = 'Now I am become Death, the destroyer of worlds.';
    protected $encoded = 'oWawXBSVCosJSecCNogWJwz1_2G43SkNMAsU2kJVTDgSRfdZtwHt2gPo3zxHzEU28dgTVq2ViKdivUVCeeFgcs_3cD61nHgn9XRVBrsFMMWWhWYY';

    /** @Revs(1000) */
    public function benchEncode()
    {
        Token::create()
            ->secret($this->secret)
            ->payload([
                'name'  => 'John Doe',
                'email' => 'john@e.com',
            ])->get();
    }

    /** @Revs(1000) */
    public function benchDecode()
    {
        Token::parse($this->encoded)
            ->secret($this->secret)
            ->get();
    }

    /** @Revs(1000) */
    public function benchEncode_Decode()
    {
        $token = Token::create()
            ->secret($this->secret)
            ->payload([
                'name'  => 'John Doe',
                'email' => 'john@e.com',
            ])->get();

        Token::parse($token)->secret($this->secret)->get();
    }
}
