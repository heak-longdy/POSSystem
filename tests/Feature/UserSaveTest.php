<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSaveTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_update_user_with_image_and_language_preference()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 1,
            'language_preference' => 'en',
        ]);

        $this->actingAs($admin);

        $payload = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'status' => 1,
            'language_preference' => 'km',
            'image' => '/monster_energy_can.png',
        ];

        $response = $this->post(route('admin-user-save', $admin->id), $payload);

        $response->assertRedirect(route('admin-user-list', 1));

        $admin->refresh();
        $this->assertEquals('Updated Name', $admin->name);
        $this->assertEquals('updated@example.com', $admin->email);
        $this->assertEquals('km', $admin->language_preference);
        $this->assertEquals('/monster_energy_can.png', $admin->image);
        $this->assertStringContainsString('/file_manager/monster_energy_can.png', $admin->image_url);
    }
}
