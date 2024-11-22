<?php

$aud = fn($value) => compare('aud', $value);
$audWrong = fn($value) => compareWrong('aud', $value);

it(
    '"aud" is valid?',
    fn ($value) => assertTrue($aud($value)->isValid())
)->with('string');

it(
    'invalid "aud"?',
    fn ($value) => assertTrue($audWrong($value)->isInvalid())
)->with('string');
