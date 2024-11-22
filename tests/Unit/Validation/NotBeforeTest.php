<?php

use Attla\Support\Envir;

$value = Envir::get('TEST_VALUE');
$nbf = fn($date) => create($value)->nbf($date)->get();
$validAt = fn($date, $now) => parse($nbf($date))->validAt($now);
$leeway = fn($leeway, $date) => parse($nbf($date))->leeway($leeway);

it(
    '"nbf" date is valid?',
    fn ($date) => assertTrue(parse($nbf($date))->isValid())
)->with('before-dates');

it(
    '"nbf" at date is valid?',
    fn ($date, $now) => assertTrue($validAt($date, $now)->isValid())
)->with('after-at-dates');

it(
    '"nbf" with leeway is valid?',
    fn ($leewayTime, $date) => assertTrue($leeway($leewayTime, $date)->isValid())
)->with('after-leeways');

it(
    'invalid "nbf"?',
    fn ($date) => assertTrue(parse($nbf($date))->isInvalid())
)->with('after-dates');

it(
    'invalid "nbf" at date?',
    fn ($date, $now) => assertTrue($validAt($date, $now)->isInvalid())
)->with('before-at-dates');

it(
    'invalid "nbf" leeway?',
    fn ($leewayTime, $date) => assertTrue($leeway($leewayTime, $date)->isInvalid())
)->with('before-leeways');
