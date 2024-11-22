<?php

namespace Attla\Token;

use Attla\Support\{
    AbstractData,
    Invoke,
    Str as AttlaStr
};
use Illuminate\Support\Arr;

trait HasAliasesTrait
{
    /**
     * Aliases
     *
     * @var array<string, string>
     */
    protected $aliases = [];

    /**
     * Alias origins
     *
     * @var array<object>
     */
    protected $aliasOrigin = [];

    /**
     * Complile method aliases
     *
     * @return void
     */
    protected function registerAliases()
    {
        if (empty($this->aliases()) || empty($this->aliasOrigin())) {
            return;
        }

        $this->aliases = collect($this->aliases())
            ->flatMap(function ($aliasList, $method) {
                return collect(Arr::flatten((array) $aliasList))
                    ->filter(fn($alias) => is_string($alias))
                    ->mapWithKeys(fn($alias) => [$alias => $method]);
            })->toArray();

        $this->aliasOrigin = Arr::map(
            $this->aliasOrigin(),
            fn($origin) => is_object($origin) ? $origin : Invoke::make($origin)
        );
    }

    /**
     * Retrieve aliases
     *
     * @return array<string, string|string[]>
     */
    protected function aliases()
    {
        return [];
    }

    /**
     * Retrieve alias origins
     *
     * @return array<string|object>
     */
    protected function aliasOrigin()
    {
        return [];
    }

    /**
     * Dynamically call method aliases
     *
     * @param string $name
     * @param array $arguments
     * @return mixed|$this
     *
     * @throws \BadMethodCallException
     */
    public function __call($name, $arguments)
    {
        if (!is_null($alias = $this->aliases[$name] ?? null)) {
            $name = $alias;
        }

        if (is_string($name)) {
            $origins = $this->aliasOrigin ?? [];
            foreach ($origins as $origin) {
                if (
                    method_exists($origin, $name)
                    || $origin instanceof AbstractData && $origin->hasMethod($name)
                ) {
                    $result = $origin->{$name}(...$arguments);
                    return $this;
                } else if (
                    property_exists($origin, $name)
                    || $origin instanceof AbstractData && $origin->isset($name)
                ) {
                    $origin->set(AttlaStr::removePrefix($name, 'get', 'set'), ...$arguments);
                    return $this;
                }
            }
        }

        if (is_array($name)) {
            $name = implode(', ', $name);
        }

        throw new \BadMethodCallException('Method "' . $name . '" not exists on ' . __CLASS__);
    }
}
