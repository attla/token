<?php

$parse = parse(create(['email' => $email = 'emai@l.com'])->with('uid', $uid = 42)->get());

it(
    'has prop "uid"?',
    fn() => assertTrue($parse->has('uid') && $parse->has('uid', $uid))
);

it(
    'has prop "email"?',
    fn() => assertTrue($parse->has('email') && $parse->has('email', $email))
);

it(
    'there is no prop "name"?',
    fn() => assertFalse($parse->has('name') && $parse->has('name', $email))
);

it(
    'no has prop "email" with other value?',
    fn() => assertFalse($parse->has('email', $uid))
);
