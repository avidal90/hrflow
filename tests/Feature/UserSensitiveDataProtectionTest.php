<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class UserSensitiveDataProtectionTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_sensitive_identifiers_are_encrypted_and_masked(): void
    {
        $user = User::factory()->create([
            'national_id' => '12345678A',
            'social_security_number' => '12 3456789012',
        ]);

        $this->assertDatabaseMissing('users', [
            'id' => $user->getKey(),
            'national_id' => '12345678A',
        ]);

        $this->assertDatabaseMissing('users', [
            'id' => $user->getKey(),
            'social_security_number' => '12 3456789012',
        ]);

        $user->refresh();

        $this->assertSame('*****678A', $user->maskedNationalId());
        $this->assertSame('********9012', $user->maskedSocialSecurityNumber());
    }
}
