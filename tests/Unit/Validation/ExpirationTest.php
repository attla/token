<?php

use Attla\Support\Envir;

$value = Envir::get('TEST_VALUE');
$exp = fn($date) => create($value)->exp($date)->get();
$validAt = fn($date, $now) => parse($exp($date))->validAt($now);
$leeway = fn($leeway, $date) => parse($exp($date))->leeway($leeway);

it(
    '"exp" is valid?',
    fn ($date) => assertTrue(parse($exp($date))->isValid())
)->with('after-dates');

it(
    '"exp" "at date" is valid?',
    fn ($date, $now) => assertTrue($validAt($date, $now)->isValid())
)->with('before-at-dates');

it(
    '"exp" with leeway is valid?',
    fn ($leewayTime, $date) => assertTrue($leeway($leewayTime, $date)->isValid())
)->with('before-leeways');

it(
    'invalid "exp"?',
    fn ($date) => assertTrue(parse($exp($date))->isInvalid())
)->with('before-dates');

it(
    'invalid "exp" "at date"?',
    fn ($date, $now) => assertTrue($validAt($date, $now)->isInvalid())
)->with('after-at-dates');

it(
    'invalid "exp" leeway?',
    fn ($leewayTime, $date) => assertTrue($leeway($leewayTime, $date)->isInvalid())
)->with('after-leeways');
