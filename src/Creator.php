<?php

namespace Attla\Token;

use Carbon\CarbonInterface;
use Illuminate\Support\Arr;

class Creator extends AbstractFactory
{
    use HasAliasesTrait;

    /**
     * The token instance
     *
     * @var \Attla\Token\Token
     */
    protected Token $token;

    public function __construct()
    {
        $this->token = new Token();
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
            'setBody' => [
                'body',
                'payload',
                'content',
            ],
            'secret' => [
                'phrase',
                'passphrase',
            ],
            'expiresAt' => [
                'expiration',
                'exp',
            ],
            'canOnlyBeUsedAfter' => [
                'notBefore',
                'nbf',
            ],
            'issuedAt' => [
                'issuedBefore',
                'iat',
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
     * Set token expiration date
     *
     * @param int|\Carbon\CarbonInterface|\DateTimeInterface $date
     * @return $this
     */
    public function expiresAt(int|CarbonInterface|\DateTimeInterface $date): self
    {
        $this->token->header->set(Claim::EXPIRATION_TIME, Util::timestamp($date));
        return $this;
    }

    /**
     * Set token not before date validation
     *
     * @param int|\Carbon\CarbonInterface|\DateTimeInterface $date
     * @return $this
     */
    public function canOnlyBeUsedAfter(int|CarbonInterface|\DateTimeInterface $date): self
    {
        $this->token->header->set(Claim::NOT_BEFORE, Util::timestamp($date));
        return $this;
    }

    /**
     * Set token issued before date validation
     *
     * @param int|\Carbon\CarbonInterface|\DateTimeInterface $date
     * @return $this
     */
    public function issuedAt(int|CarbonInterface|\DateTimeInterface $date): self
    {
        $this->token->header->set(Claim::ISSUED_AT, Util::timestamp($date));
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
        $this->token->header->set(Claim::ID, $id);
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
        $this->token->header->set(Claim::SUBJECT, $subject);
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
        $this->token->header->set(Claim::AUDIENCE, array_merge(
            $this->token->header->getArray(Claim::AUDIENCE),
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
        $this->token->header->set(Claim::ISSUER, $issuer);
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
        $this->token->header->set($claim, $value);
        return $this;
    }

    /**
     * Encode the token
     *
     * @return string
     */
    public function get(): string
    {
        return implode('_', [
            $this->token->encodedHeader(),
            $this->token->encodedBody(),
            $this->token->signature(),
        ]);
    }

    /**
     * Encode the token if it is casted as a string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->get();
    }
}
