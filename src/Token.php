<?php

namespace Attla\Token;

use Attla\Pincryp\Factory as Pincryp;
use Attla\Support\{
    Arr as AttlaArr,
    Str as AttlaStr,
    AbstractData,
    Envir
};
use Carbon\CarbonInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class Token extends AbstractData
{
    /**
     * The header of the token
     *
     * @var AbstractData<Claim, string|string[]>
     */
    public $header = [];

    /**
     * The header token string
     *
     * @var string
     */
    public $headerString = '';

    /**
     * The token claims
     *
     * @var AbstractData<Claim, string|string[]>
     */
    public $claims = [];

    /**
     * The body of the token
     *
     * @var mixed
     */
    public $body;

    /**
     * Researchable body
     *
     * @var array
     */
    public $bodyArray = [];

    /**
     * The body token string
     *
     * @var string
     */
    public $bodyString = '';

    /**
     * The signature of the token
     *
     * @var string
     */
    public $signature = '';

    /**
     * Determine if the token is on same mode
     *
     * @var bool
     */
    public $same = false;

    /**
     * When checking nbf, iat or expiration times,
     * we want to provide some extra leeway time to
     * account for clock skew.
     *
     * @var int
     */
    public $leeway = 0;

    /**
     * Determine if the body has an associative array.
     *
     * @var bool
     */
    public $associative = false;

    /**
     * Pincryp instance
     *
     * @var \Attla\Pincryp\Factory
     */
    public $pincryp;

    /**
     * Group part separator
     *
     * @var string
     */
    private $separator = '.';

    /**
     * Create a new Token instance
     *
     * @param object|array $source
     * @return void
     */
    public function __construct(object|array $source = [])
    {
        parent::__construct($source);
        $this->pincryp = new Pincryp();

        if ($secret = Envir::get('app.key', Envir::get('APP_KEY'))) {
            $this->secret($secret);
        }
    }

    /**
     * Set secret key
     *
     * @param string $secret
     * @return void
     */
    public function secret(string $key)
    {
        $this->pincryp->config->key = $key;
    }

    /**
     * Set header value
     *
     * @param object|array $header
     * @return AbstractData
     */
    public function setHeader(object|array $header = []): AbstractData
    {
        return new Data($header);
    }

    /**
     * Set claims value
     *
     * @param object|array $claims
     * @return AbstractData
     */
    public function setClaims(object|array $claims = []): AbstractData
    {
        return new Data($claims);
    }

    /**
     * Alias to set header value
     *
     * @param string $header
     * @return $this
     */
    public function parseHeader(string $header = null): self
    {
        if (($value = $this->pincryp->decode($header)) instanceof \stdClass) {
            $this->set('header', $value);
            $this->set('headerString', $header);
        }

        return $this;
    }

    /**
     * Get encode header value
     *
     * @return string
     */
    public function encodedHeader()
    {
        $this->newEntropy();

        return $this->headerString = $this->pincryp->encode(
            $this->same ? $this->header : AttlaArr::randomized($this->header)
        );
    }

    /**
     * Alias to set body value
     *
     * @param mixed $body
     * @return $this
     */
    public function parseBody(string $body = null)
    {
        if (
            !empty($this->header->e)
            && $value = $this->pincryp
                    ->onceKey($this->header->e)
                    ->decode($body, $this->associative)
        ) {
            $this->set('body', $value);
            $this->set('bodyString', $body);

            in_array(gettype($value), ['array', 'object'])
                && $this->set('bodyArray', AttlaArr::toArray($value));
        }

        return $this;
    }

    /**
     * Get encode body value
     *
     * @return string
     */
    public function encodedBody()
    {
        return $this->bodyString = $this->pincryp
            ->onceKey($this->entropy())
            ->encode($this->body);
    }

    /**
     * Get token signature
     *
     * @return string
     */
    public function getSignature()
    {
        return $this->hash(
            $this->headerString . $this->bodyString,
            $this->secret . $this->entropy()
        );
    }

    /**
     * Check if the signature is valid
     *
     * @return bool
     */
    public function signed()
    {
        if (!$signature = $this->signature) {
            return false;
        }

        return $signature === $this->getSignature();
    }

    /**
     * Check if the token is valid
     *
     * @return bool
     */
    public function isValid(): bool
    {
        if (!$this->get('body') || !$this->signed()) {
            return false;
        }

        return $this->validateClaims();
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
        if ($this->header->has($prop)) {
            return is_null($value) ? true : $this->header->get($prop) === $value;
        }

        if (!empty($this->bodyArray) && isset($this->bodyArray[$prop])) {
            return is_null($value) ? true : $this->bodyArray[$prop] === $value;
        }

        return false;
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
     * Check all validations of the token
     *
     * @return bool
     */
    public function validateClaims(): bool
    {
        if (
            $this->isExpired($now = $this->claims->getInt(Claim::NOW, time()))
            || !$this->notBefore($now)
            || !$this->issuedBefore($now)
            || !$this->validate(Claim::AUDIENCE)
            || !$this->validate(Claim::ISSUER)
            || !$this->validate(Claim::ID)
            || !$this->validate(Claim::SUBJECT)
            || !$this->validateCustomClaims()
            // TODO: validate ip, bwr, loc
        ) {
            return false;
        }

        return true;
    }

    /**
     * Check if the token is expired
     *
     * @param int|\Carbon\CarbonInterface|\DateTimeInterface|null $date
     * @return bool
     */
    public function isExpired(int|CarbonInterface|\DateTimeInterface $date = null): bool
    {
        if (!$this->header->has($claim = Claim::EXPIRATION_TIME)) {
            return false;
        }

        return Util::timestamp($date) - $this->leeway() >= $this->header->getInt($claim);
    }

    /**
     * Check if the token has valid before a date
     *
     * @param string $claim
     * @param int|\Carbon\CarbonInterface|\DateTimeInterface|null $date
     * @return bool
     */
    private function validAfter(string $claim, int|CarbonInterface|\DateTimeInterface $date = null): bool
    {
        if (!$this->header->has($claim)) {
            return true;
        }

        return Util::timestamp($date) + $this->leeway() > $this->header->getInt($claim);
    }

    /**
     * Check if the token has not before a date
     *
     * @param int|\Carbon\CarbonInterface|\DateTimeInterface|null $date
     * @return bool
     */
    public function notBefore(int|CarbonInterface|\DateTimeInterface $date = null): bool
    {
        return $this->validAfter(Claim::NOT_BEFORE, $date);
    }

    /**
     * Check if the token has issued before a date
     *
     * @param int|\Carbon\CarbonInterface|\DateTimeInterface|null $date
     * @return bool
     */
    public function issuedBefore(int|CarbonInterface|\DateTimeInterface $date = null): bool
    {
        return $this->validAfter(Claim::ISSUED_AT, $date);
    }

    /**
     * Validate a value on token
     *
     * @param string $claim
     * @param mixed|null $expected
     * @return bool
     */
    public function validate(string $claim, $expected = null): bool
    {
        $expected ??= $this->claims->get($claim);
        $value = $this->getTokenValue($claim);

        $value = is_array($value) ? $value : [$value];
        if (is_array($expected)) {
            return empty(array_diff($expected, $value));
        }

        return in_array($expected, $value, true);
    }

    /**
     * Retrieve a token value from header or body
     *
     * @param string $claim
     * @return mixed
     */
    protected function getTokenValue(string $claim)
    {
        if ($this->header->has($claim)) {
            return $this->header->get($claim);
        }

        if (!empty($this->bodyArray) && !empty($item = $this->bodyArray[$claim] ?? null)) {
            return $item;
        }

        return null;
    }

    /**
     * Validate custom claim values on token
     *
     * @return bool
     */
    protected function validateCustomClaims(): bool
    {
        $exceptions = array_merge(['e'], Claim::ALL);
        $headers = Arr::except($this->header->all(), $exceptions);
        $claims = array_merge($headers, Arr::except($this->claims->all(), array_merge($exceptions, array_keys($headers))));

        foreach ($claims as $claim => $expected) {
            if (!$this->validate($claim)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Set token to same mode
     *
     * @param string $entropy
     * @return $this
     */
    public function same(string $entropy = ''): self
    {
        $this->same = true;
        $this->header->e = $this->hash($entropy ?: $this->secret);
        $this->pincryp->config->entropy = 0;

        return $this;
    }

    /**
     * Get entropy token
     *
     * @return string
     */
    private function entropy()
    {
        return $this->header->e ?? $this->newEntropy();
    }

    /**
     * Generate new entropy token
     *
     * @return string
     */
    private function newEntropy()
    {
        if ($this->same && !empty($this->header->e)) {
            return $this->header->e;
        }

        return $this->header->e = AttlaStr::randHex(6);
    }

    /**
     * Generate a unique hash
     *
     * @return string
     */
    private function hash($data, string $secret = null)
    {
        $config = clone $this->pincryp->config;
        $config->entropy = 0;
        is_null($secret) || $config->key = $secret;

        return $this->pincryp->onceConfig($config)->encode(md5((string) $data, true));
    }

    /**
     * Split the token into groups
     *
     * @return string[]
     */
    public function getParts($token) {
        return explode($this->separator, $token);
    }

    /**
     * Return token as string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return implode($this->separator, [
            $this->headerString ?: $this->encodedHeader(),
            $this->bodyString ?: $this->encodedBody(),
            $this->signature(),
        ]);
    }
}
