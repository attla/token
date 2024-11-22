<?php

$iss = fn($value) => compare('iss', $value);
$issWrong = fn($value) => compareWrong('iss', $value);

it(
    '"iss" is valid?',
    fn ($value) => assertTrue($iss($value)->isValid())
)->with('string');

it(
    'invalid "iss"?',
    fn ($value) => assertTrue($issWrong($value)->isInvalid())
)->with('string');
