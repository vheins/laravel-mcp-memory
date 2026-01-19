<?php

use App\Rules\ImmutableTypeRule;
use Illuminate\Support\Facades\Validator;

it('passes for human actor', function () {
    $rule = new ImmutableTypeRule('human');

    $validator = Validator::make(['type' => 'system_constraint'], [
        'type' => [$rule],
    ]);

    expect($validator->passes())->toBeTrue();
});

it('fails for ai actor with restricted type', function () {
    $rule = new ImmutableTypeRule('ai');

    $validator = Validator::make(['type' => 'system_constraint'], [
        'type' => [$rule],
    ]);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('type'))->toContain('AI agents cannot');
});

it('passes for ai actor with allowed type', function () {
    $rule = new ImmutableTypeRule('ai');

    $validator = Validator::make(['type' => 'preference'], [
        'type' => [$rule],
    ]);

    expect($validator->passes())->toBeTrue();
});
