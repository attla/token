<?php

use Attla\Support\Str;

it('type is valid? [Laravel] [unique]', function ($value) {
    $parser = Token::parse(Token::create()->body($value)->get());
    if (is_array($value)) {
        $parser->associative();
    }

    assertEquals($value, $parser->get());
})->with('var-types');

it('parse with wrong key is invalid [Laravel] [unique]', function ($value) {
    $token = Token::create()->body($value)->get();
    $parser = Token::parse($token)->secret(Str::randHex());
    if (is_array($value)) {
        $parser->associative();
    }

    assertNotSame($value, $parser->get());
})->with('var-types');

it(
    'always unique? [Laravel]',
    fn ($value) => assertEquals(6, count(array_unique(array_map(
        fn() => Token::create()->body($value)->get(),
        range(0, 5)
    ))))
)->with('value');

it(
    'always same? [Laravel]',
    fn ($value) => assertEquals(1, count(array_unique(array_map(
        fn() => Token::create()->body($value)->same()->get(),
        range(0, 5)
    ))))
)->with('value');
