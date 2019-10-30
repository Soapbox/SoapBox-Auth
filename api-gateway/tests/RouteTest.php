<?php

class RouteTest extends TestCase
{
    var $jwt;
    public function setUp() : void
    {
        parent::setUp();
        $this->jwt = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9hdXRoLXNlcnZlci50ZXN0IiwiYXVkIjoiaHR0cDpcL1wvYXBpLWdhdGV3YXkudGVzdCIsImlhdCI6MTU3MjM0NDUxMywiZXhwIjoxNTcyOTQ5MzEzLCJuYW1lIjoiQ2FsZWIgTWJha3dlIiwiZW1haWwiOiJjYWxlYkBzb2FwYm94aHEuY29tIiwiYXZhdGFyIjoiaHR0cHM6XC9cL2xoNS5nb29nbGV1c2VyY29udGVudC5jb21cLy13Z3dXWF9LNkZWQVwvQUFBQUFBQUFBQUlcL0FBQUFBQUFBQUFBXC9BQ0hpM3JlYWM2cVRuX0pTak9RQU9WelBRXzZOV3VTWmRnXC9waG90by5qcGcifQ.fUsixNLW87PbTecfTt46TjEVgv1gT4byCkHbfizuFZ8";
        app('redis')->sAdd(env('REDIS_KEY'), $this->jwt);
    }
    
    public function testValidGet()
    {
        $this->get('/email/health-check');

        $this->assertEquals(
            404, $this->response->getStatusCode()
        );
    }

    public function testInvalidGet()
    {
        $this->get('/service/not-found');

        $this->assertEquals(
            404, $this->response->getStatusCode()
        );
    }

    public function testValidPost()
    {
        $response = $this->json('POST', '/email/send-email', ['subject' => 'Sally', 'body' => 'Ommlette du fromage'], 
            ['Authorization' => 'Bearer ' . $this->jwt]);
        $this->assertEquals(404, $this->response->status());
    }

    public function testUnauthorizedPost()
    {
        $response = $this->json('POST', '/email/send-email', ['subject' => 'Sally', 'body' => 'Ommlette du fromage']);
        $this->assertEquals(401, $this->response->status());
    }

    public function testValidPut()
    {
        $response = $this->json('PUT', '/email/address', ['user_id' => 1, 'email' => 'ommlette.du@fromage.com'], 
            ['Authorization' => 'Bearer ' . $this->jwt]);
        $this->assertEquals(404, $this->response->status());
    }

    public function testValidDelete()
    {
        $response = $this->json('DELETE', '/email/records', ['user_id' => 1], 
            ['Authorization' => 'Bearer ' . $this->jwt]);
        $this->assertEquals(404, $this->response->status());
    }

    public function tearDown() : void
    {
        app('redis')->sRem(env('REDIS_KEY'), $this->jwt);
        parent::tearDown();
    }
}
