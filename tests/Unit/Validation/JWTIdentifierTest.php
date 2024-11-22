<?php

$jti = fn($value) => compare('jti', $value);
$jtiWrong = fn($value) => compareWrong('jti', $value);

it(
    '"jti" is valid?',
    fn ($value) => assertTrue($jti($value)->isValid())
)->with('string');

it(
    'invalid "jti"?',
    fn ($value) => assertTrue($jtiWrong($value)->isInvalid())
)->with('string');
