<?php

namespace Tests\Feature;

use Tests\TestCase;

class ToastNotificationsTest extends TestCase
{
    public function test_success_error_and_status_flash_messages_render_as_escaped_toasts(): void
    {
        $this->withSession([
            'success' => 'Berhasil disimpan.',
            'error' => '<script>alert("xss")</script>',
            'status' => 'Informasi akun.',
        ])->get(route('admin.login'))
            ->assertOk()
            ->assertSee('data-toast-type="success"', false)
            ->assertSee('Berhasil disimpan.')
            ->assertSee('data-toast-type="error"', false)
            ->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false)
            ->assertSee('data-toast-type="info"', false)
            ->assertSee('Informasi akun.');
    }

    public function test_login_validation_errors_render_as_error_toasts(): void
    {
        $this->followingRedirects()
            ->from(route('admin.login'))
            ->post(route('admin.login.store'), [
                'email' => 'missing-user@example.com',
                'password' => 'incorrect-password',
            ])
            ->assertOk()
            ->assertSee('data-toast-type="error"', false)
            ->assertSee('Email atau password salah.');
    }
}
