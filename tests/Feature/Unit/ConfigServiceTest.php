<?php

test('example', function (): void {
    $response = $this->get('/');

    $response->assertRedirect();
});
