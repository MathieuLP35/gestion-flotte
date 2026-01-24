<?php

it('returns a successful response on the home page', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

it('can use expect for assertions', function () {
    expect(true)->toBeTrue();
    expect([1, 2, 3])->toContain(2);
});
