<?php

it('can add two numbers', function () {
    $result = 2 + 2;
    expect($result)->toBe(4);
});

it('can check if string contains text', function () {
    $text = 'Hello, World!';
    expect($text)->toContain('World');
});

it('can validate array has key', function () {
    $data = ['name' => 'John', 'age' => 30];
    expect($data)->toHaveKey('name');
    expect($data['name'])->toBe('John');
});
