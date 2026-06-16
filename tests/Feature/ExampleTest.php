<?php

test('root route redirects to login for guests', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
