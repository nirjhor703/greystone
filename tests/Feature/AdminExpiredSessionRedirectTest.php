<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminExpiredSessionRedirectTest extends TestCase
{
    public function test_guest_admin_request_with_referer_redirects_to_login(): void
    {
        $response = $this
            ->withHeader('referer', url('/admin/customers/people-profiles'))
            ->get('/admin/customers/people-profiles');

        $response->assertRedirect(route('login'));
    }
}
