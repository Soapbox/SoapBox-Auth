<?php

use Tests\TestCase;

class GoogleControllerTest extends TestCase
{
    /**
     * On landing on url
     *
     * @return void
     */
    public function test_login()
    {
        $response = $this->get('/google-login');

        $response->assertStatus(302);
    }

    /**
     * On error occurred
     *
     * @return void
     */
    public function test_login_error()
    {
        $response = $this->get('/google-login?error=the_error_message');

        $response->assertStatus(302);

        $response->assertRedirect('/?error=the_error_message');
    }

    /**
     * On success
     * 
     * @ return void
     */
    public function test_login_success()
    {
        $response = $this->get('/google-login?code=the_oath_code');

        $response->assertSuccessful();
    }
}
