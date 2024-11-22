<?php

$claim = fn($value) => compare('with', 'uid', $value);
$claimWrong = fn($value) => compareWrong('with', 'uid', $value);

it(
    'custom claim is valid?',
    fn ($value) => assertTrue($claim($value)->isValid())
)->with('string');

it(
    'invalid custom claim?',
    fn ($value) => assertTrue($claimWrong($value)->isInvalid())
)->with('string');
