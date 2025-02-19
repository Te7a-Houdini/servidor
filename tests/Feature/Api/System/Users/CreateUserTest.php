<?php

namespace Tests\Feature\Api\System\Users;

use Illuminate\Http\Response;
use Tests\PrunesDeletables;
use Tests\RequiresAuth;

class CreateUserTest extends TestCase
{
    use PrunesDeletables;
    use RequiresAuth;

    public function tearDown()
    {
        $this->pruneDeletable('users');

        parent::tearDown();
    }

    /** @test */
    public function guest_cannot_create_user()
    {
        $response = $this->postJson($this->endpoint, [
            'name' => 'guesttestuser',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJsonCount(1);
        $response->assertJson(['message' => 'Unauthenticated.']);

        $updated = $this->authed()->getJson($this->endpoint);
        $updated->assertJsonMissing(['name' => 'guesttestuser']);
    }

    /** @test */
    public function authed_user_can_create_user_with_minimum_data()
    {
        $response = $this->authed()->postJson($this->endpoint, [
            'name' => 'newtestuser',
            'gid' => 0,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonFragment(['name' => 'newtestuser']);
        $response->assertJsonStructure($this->expectedKeys);

        $this->addDeletable('user', $response);
    }

    /** @test */
    public function cannot_create_user_without_required_fields()
    {
        $response = $this->authed()->postJson($this->endpoint, ['uid' => 1337]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors(['name', 'gid']);

        $updated = $this->authed()->getJson($this->endpoint);
        $updated->assertJsonMissing(['uid' > 1337]);
    }

    /** @test */
    public function cannot_create_user_with_invalid_data()
    {
        $response = $this->authed()->postJson($this->endpoint, [
            'name' => '',
            'gid' => '',
            'groups' => 'notanarray',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'name',
            'gid',
            'groups',
        ]);

        $updated = $this->authed()->getJson($this->endpoint);
        $updated->assertJsonMissing(['groups' => 'notanarray']);
    }

    /** @test */
    public function name_cannot_start_with_dash()
    {
        $response = $this->authed()->postJson($this->endpoint, [
            'name' => '-test-dash-prefix',
            'gid' => 0,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonFragment(['The name format is invalid.']);
    }

    /** @test */
    public function name_cannot_start_with_plus()
    {
        $response = $this->authed()->postJson($this->endpoint, [
            'name' => '+test-plus-prefix',
            'gid' => 0,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonFragment(['The name format is invalid.']);
    }

    /** @test */
    public function name_cannot_start_with_tilde()
    {
        $response = $this->authed()->postJson($this->endpoint, [
            'name' => '~test-tilde-prefix',
            'gid' => 0,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonFragment(['The name format is invalid.']);
    }

    /** @test */
    public function name_cannot_contain_colon()
    {
        $response = $this->authed()->postJson($this->endpoint, [
            'name' => 'test-contains-:',
            'gid' => 0,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonFragment(['The name cannot contain a colon.']);
    }

    /** @test */
    public function name_cannot_contain_comma()
    {
        $response = $this->authed()->postJson($this->endpoint, [
            'name' => 'test,contains,comma',
            'gid' => 0,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonFragment(['The name cannot contain a comma.']);
    }

    /** @test */
    public function name_cannot_contain_tab()
    {
        $response = $this->authed()->postJson($this->endpoint, [
            'name' => "test\tcontains\ttab",
            'gid' => 0,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonFragment(['The name cannot contain whitespace or newlines.']);
    }

    /** @test */
    public function name_cannot_contain_newline()
    {
        $response = $this->authed()->postJson($this->endpoint, [
            'name' => "test\ncontains\nnewline",
            'gid' => 0,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonFragment(['The name cannot contain whitespace or newlines.']);
    }

    /** @test */
    public function name_cannot_contain_whitespace()
    {
        $response = $this->authed()->postJson($this->endpoint, [
            'name' => 'test contains space',
            'gid' => 0,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonFragment(['The name cannot contain whitespace or newlines.']);
    }

    /** @test */
    public function name_ending_with_whitespace_gets_trimmed()
    {
        $response = $this->authed()->postJson($this->endpoint, [
            'name' => 'testuser ',
            'gid' => 0,
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonFragment(['name' => 'testuser']);

        $this->addDeletable('user', $response);
    }

    /** @test */
    public function name_cannot_be_too_long()
    {
        $response = $this->authed()->postJson($this->endpoint, [
            'name' => '_im-a-name-that-is-over-32-chars-',
            'gid' => 0,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonFragment(['The name may not be greater than 32 characters.']);
    }
}
