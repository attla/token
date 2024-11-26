<?php

it('is valid token type? [default secret]', function ($value) {
    assertEquals(
        parse(create($value)->get())->get(),
        $value
    );
})->with('string');

it('is valid token type? [secret]', function ($value, $secret) {
    assertEquals(
        parse(create($value, $secret)->get(), $secret)->get(),
        $value
    );
})->with('string')
->with('passphrases');

it('each time generate a unique token?', function ($value) {
    $token = create($value);

    assertNotSame($token->get(), $token->get());
})->with('var-types');

it(
    'always generate a unique token?',
    fn ($value) => assertEquals(6, count(array_unique(array_map(
        fn() => create($value)->get(),
        range(0, 5)
    ))))
)->with('value');

it(
    'always generate a same token?',
    fn ($value) => assertEquals(1, count(array_unique(array_map(
        fn() => create($value)->same()->get(),
        range(0, 5)
    ))))
)->with('value');

it('have the correct value type?', function ($value) {
    $decoder = parse(create($value)->get());
    $type = gettype($value);
    if ($type == 'array') {
        $decoder->associative();
    }

    assertTrue($decoder->isValid());
    assertEquals($type, gettype($decoder->get()));
    assertEquals($value, $decoder->get());
})->with('var-types');

it('invalid if decoded with wrong secret?', function ($value, $secret) {
    $decoder = parse(create($value)->get(), $secret);
    $type = gettype($value);
    if ($type == 'array') {
        $decoder->associative();
    }

    assertTrue($decoder->isInvalid());
    assertNotSame($type, gettype($decoder->get()));
    assertNotSame($value, $decoder->get());
})->with('var-types')
->with('passphrase');
