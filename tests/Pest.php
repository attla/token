<?php

use Attla\{
    Token\Util,
    Token\Factory as Token,
    Support\Envir,
    Support\Str as AttlaStr
};

uses(Tests\TestCase::class)->in(__DIR__);

Envir::set('APP_KEY', AttlaStr::randHex());

$value = 'Now I am become Death, the destroyer of worlds.';
Envir::set('TEST_SECRET', $secret = str_shuffle($value));
Envir::set('TEST_VALUE', $value);

function apply($token, $method, ...$args) {
    return $token->{$method}(...$args);
}

function parse(string $token, $secret = null) {
    return Token::parse($token)
        ->secret($secret ?? Envir::get('TEST_SECRET'));
}

function create($body, $secret = null) {
    return Token::create()
        ->secret($secret ?? Envir::get('TEST_SECRET'))
        ->body($body);
}

function compare($method, ...$args) {
    $token = apply(create($args[0] ?? Envir::get('TEST_VALUE', 'test')), $method, ...$args)->get();
    return apply(parse($token), $method, ...$args);
}

function compareWrong($method, ...$args) {
    $token = apply(create($args[0] ?? Envir::get('TEST_VALUE', 'test')), $method, ...$args)->get();
    return apply(parse($token), $method, $rand = AttlaStr::randHex(), $rand, $rand);
}

dataset('string', $charTypes = [
    'alfa' => $value,
    'alfanum'   => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
    'special'   => '`~!@#$%^&*()\\][+={}/|:;"\'<>,.?-_',
    'acents'    => 'àáâãäÀÁÂÃÄ çÇ èéêëÈÉÊË ìíîïÌÍÎÏ ñÑ òóôõöÒÓÔÕÖ ùúûüÙÚÛÜ ýÿÝ',
    'japanese'  => '今、私は世界の破壊者である死になりました。',
    'mandarin'  => '现在我变成了死神，世界的毁灭者。',
    'hindi'     => 'अब मैं मृत्यु बन गया हूँ, संसारों का नाश करने वाला।',
]);

dataset('passphrases', $charTypes);
dataset('value', [$value]);
dataset('passphrase', [$value]);

$dateLabels = ['10 sec', '30 min', '1 hour', '1 day', '1 week'];
$dateTransform = [
    fn($str) => strtotime($str),
    fn($str) => Util::strToCarbon($str),
    fn($str) => Util::strToCarbonImmutable($str),
    fn($str) => Util::strToDateTime($str),
    fn($str) => Util::strToDateTimeImmutable($str),
];

dataset('before-dates', $beforeDates = array_combine(
    $dateLabels,
    array_map(
        fn($label, $transform) => $transform(($label != 'now' ? '-' : '') . $label),
        $dateLabels,
        $dateTransform
    )
));

dataset('after-dates', $afterDates = array_combine(
    $keys = $dateLabels,
    array_map(
        fn($label, $transform) => $transform('+' . $label),
        $keys,
        $dateTransform
    )
));

dataset('before-at-dates', array_combine(
    $keys,
    array_map(
        fn($date, $now) => [$date, $now],
        $afterDatesValues = array_values($afterDates),
        $beforeDatesValues = array_values($beforeDates)
    )
));

dataset('after-at-dates', array_combine(
    $keys,
    array_map(
        fn($date, $now) => [$date, $now],
        $beforeDatesValues,
        $afterDatesValues
    )
));

dataset('before-leeways', array_combine(
    $keys,
    array_map(
        fn($key, $value) => [abs(strtotime($key, 0)) / 2, $value],
        $keys,
        $afterDatesValues
    )
));

dataset('after-leeways', array_combine(
    $dateLabels,
    array_map(
        fn($key, $value) => [abs(strtotime($key, 0)), $value],
        $dateLabels,
        $beforeDates
    )
));

dataset('var-types', array_merge($charTypes, [
    'int' => 42,
    'float' => 4.2,
    'array (sequential)' => ['v' => [4,2]],
    'array (associative)' => $assoc = ['v' => ['four' => 4,'two' => 2]],
    'object (stdClass)' => (object) $assoc['v'],
    'byte' => 0x2A,
    'null (byte)' => chr(0),
    'separator (byte)' => "\x1c",
    'null string (byte)' => "\x00",
    'others' => " \t\n\r\0\x0B\x0c\xc2\xa0",
]));
