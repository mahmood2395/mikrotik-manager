<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Router;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouterTest extends TestCase
{
    use RefreshDatabase;

    protected bool $withoutCsrf = true;

    private function user(): User
    {
        return User::factory()->create();
    }

    public function test_routers_page_requires_auth(): void
    {
        $response = $this->get(route('routers.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_routers_page(): void
    {
        $response = $this->actingAs($this->user())
            ->get(route('routers.index'));
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_create_router(): void
    {
        $response = $this->actingAs($this->user())
            ->post(route('routers.store'), [
                'name'       => 'Test Router',
                'ip_address' => '192.168.1.1',
                'api_port'   => 8728,
                'rest_port'  => 80,
                'username'   => 'admin',
                'password'   => 'password',
            ]);

        $response->assertRedirect(route('routers.index'));
        $this->assertDatabaseHas('routers', ['name' => 'Test Router']);
    }

    public function test_router_requires_valid_ip(): void
    {
        $response = $this->actingAs($this->user())
            ->post(route('routers.store'), [
                'name'       => 'Bad Router',
                'ip_address' => 'not-an-ip',
                'api_port'   => 8728,
                'rest_port'  => 80,
                'username'   => 'admin',
                'password'   => 'password',
            ]);

        $response->assertSessionHasErrors('ip_address');
        $this->assertDatabaseMissing('routers', ['name' => 'Bad Router']);
    }

    public function test_authenticated_user_can_delete_router(): void
    {
        $router = Router::factory()->create();

        $response = $this->actingAs($this->user())
            ->delete(route('routers.destroy', $router));

        $response->assertRedirect(route('routers.index'));
        $this->assertDatabaseMissing('routers', ['id' => $router->id]);
    }
    
}