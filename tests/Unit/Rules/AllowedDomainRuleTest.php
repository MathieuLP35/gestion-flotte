<?php

use App\Models\AllowedDomain;
use App\Rules\AllowedDomainRule;
use Illuminate\Support\Facades\Validator;

it('passe si le domaine est autorisé', function (): void {
    AllowedDomain::create(['name' => 'example.com']);

    $rule = new AllowedDomainRule;
    $v = Validator::make(
        ['email' => 'user@example.com'],
        ['email' => [$rule]]
    );

    expect($v->passes())->toBeTrue();
});

it('échoue si le domaine n\'est pas autorisé', function (): void {
    $rule = new AllowedDomainRule;
    $v = Validator::make(
        ['email' => 'user@interdit.com'],
        ['email' => [$rule]]
    );

    expect($v->fails())->toBeTrue();
    expect($v->errors()->first('email'))->toContain('interdit.com');
});
