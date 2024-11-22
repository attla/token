<?php

use Attla\Support\Envir;

$value = Envir::get('TEST_VALUE');
$iat = fn($date) => create($value)->iat($date)->get();
$validAt = fn($date, $now) => parse($iat($date))->validAt($now);
$leeway = fn($leeway, $date) => parse($iat($date))->leeway($leeway);

it(
    '"iat" date is valid?',
    fn ($date) => assertTrue(parse($iat($date))->isValid())
)->with('before-dates');

it(
    '"iat" at date is valid?',
    fn ($date, $now) => assertTrue($validAt($date, $now)->isValid())
)->with('after-at-dates');

it(
    '"iat" with leeway is valid?',
    fn ($leewayTime, $date) => assertTrue($leeway($leewayTime, $date)->isValid())
)->with('after-leeways');

it(
    'invalid "iat"?',
    fn ($date) => assertTrue(parse($iat($date))->isInvalid())
)->with('after-dates');

it(
    'invalid "iat" at date?',
    fn ($date, $now) => assertTrue($validAt($date, $now)->isInvalid())
)->with('before-at-dates');

it(
    'invalid "iat" leeway?',
    fn ($leewayTime, $date) => assertTrue($leeway($leewayTime, $date)->isInvalid())
)->with('before-leeways');
