<?php

namespace Attla\Token;

use Carbon\CarbonInterface;
use Illuminate\Support\Arr;

class Parser extends AbstractFactory
{
    use HasAliasesTrait;

    /**
     * The token instance
     *
     * @var \Attla\Token\Token
     */
    protected Token $token;

    /**
     * The token string
     *
     * @var string
     */
    private $tokenString = '';

    /**
     * indicates if token was parsed
     *
     * @var bool
     */
    private $parsed = false;

    public function __construct($token)
    {
        $this->token = new Token();
        $this->tokenString = (string) $token;
        $this->registerAliases();
    }

    /**
     * Retrieve aliases
     *
     * @return array<string, string|string[]>
     */
    protected function aliases()
    {
        return [
            'secret' => [
                'phrase',
                'passphrase',
            ],
            'validAt' => [
                'expiresAt', 'expiration', 'exp',
                'canOnlyBeUsedAfter', 'notBefore', 'nbf',
                'issuedAt', 'issuedBefore', 'iat',
            ],
            'issuedBy' => 'iss',
            'identifiedBy' => 'jti',
            'relatedTo' => 'sub',
            'permittedFor' => [
                'audience',
                'aud',
            ],
            'withClaim' => 'with',
        ];
    }

    /**
     * Retrieve alias origins
     *
     * @return array<string|object>
     */
    protected function aliasOrigin()
    {
        return [$this, $this->token];
    }

    /**
     * Set token expiration date.
     *
     * @param int|\Carbon\CarbonInterface|\DateTimeInterface $date
     * @return $this
     */
    public function validAt(int|CarbonInterface|\DateTimeInterface $date): self
    {
        $this->token->claims->set(Claim::NOW, Util::timestamp($date));
        return $this;
    }

    /**
     * Set token "jti" validation.
     *
     * @param string $id
     * @return $this
     */
    public function identifiedBy(string $id): self
    {
        $this->token->claims->set(Claim::ID, $id);
        return $this;
    }

    /**
     * Set token "sub" validation.
     *
     * @param string $subject
     * @return $this
     */
    public function relatedTo(string $subject): self
    {
        $this->token->claims->set(Claim::SUBJECT, $subject);
        return $this;
    }

    /**
     * Set token audience validation.
     *
     * @param mixed ...$aud
     * @return $this
     */
    public function permittedFor(...$aud): self
    {
        $this->token->claims->set(Claim::AUDIENCE, array_merge(
            $this->token->claims->getArray(Claim::AUDIENCE),
            Arr::flatten($aud)
        ));

        return $this;
    }

    /**
     * Set token "iss" validation.
     *
     * @param string $issuer
     * @return $this
     */
    public function issuedBy(string $issuer): self
    {
        $this->token->claims->set(Claim::ISSUER, $issuer);
        return $this;
    }

    /**
     * Set custom claim value.
     *
     * @param string $claim
     * @param mixed $value
     * @return $this
     */
    public function withClaim(string $claim, $value): self
    {
        $this->token->claims->set($claim, $value);
        return $this;
    }

    /**
     * Parse token value
     *
     * @return void
     */
    private function parseToken()
    {
        if (
            $this->parsed
            || !$this->tokenString
            || !is_string($this->tokenString)
            || (($parts = $this->token->getParts($this->tokenString)) !== false
                && count($parts) != 3)
        ) {
            return;
        }

        [$header, $payload, $signature] = $parts;

        $this->token
            ->parseHeader($header)
            ->parseBody($payload)
            ->setSignature($signature);

        $this->parsed = true;
    }

    /**
     * Check if the token is valid
     *
     * @return bool
     */
    public function isValid(): bool
    {
        $this->parseToken();
        return $this->token->isValid();
    }

    /**
     * Check if the token is invalid
     *
     * @return bool
     */
    public function isInvalid(): bool
    {
        return !$this->isValid();
    }

    /**
     * Check if value is present on token
     *
     * @param string $prop
     * @param mixed $value
     * @return bool
     */
    public function hasValue(string $prop, $value = null): bool
    {
        $this->parseToken();
        return $this->token->hasValue($prop, $value);
    }

    /**
     * Check if value is present on token
     *
     * @param string $prop
     * @param mixed $value
     * @return bool
     */
    public function has(string $prop, $value = null): bool
    {
        return $this->hasValue($prop, $value);
    }

    /**
     * Returns the value of token
     *
     * @return mixed
     */
    public function get()
    {
        $this->parseToken();
        return $this->token->body;
    }

    /**
     * Retrieve the encoded token
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->tokenString;
    }
}
