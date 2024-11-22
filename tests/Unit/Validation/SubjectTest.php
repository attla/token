<?php

$sub = fn($value) => compare('sub', $value);
$subWrong = fn($value) => compareWrong('sub', $value);

it(
    '"sub" is valid?',
    fn ($value) => assertTrue($sub($value)->isValid())
)->with('string');

it(
    'invalid "sub"?',
    fn ($value) => assertTrue($subWrong($value)->isInvalid())
)->with('string');
