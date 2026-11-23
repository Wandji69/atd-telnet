
<?php

use Inertia\Testing\AssertableInertia as Assert;

test('a guest can view the public landing page', function () {
    // Disable Vite asset loading during this PHP test
    $this->withoutVite();

    // Confirm the visitor is not authenticated
    $this->assertGuest();

    // Verify the landing page loads successfully
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('landing')
            ->where('title', 'Telnet intern attendance')
        );
});
