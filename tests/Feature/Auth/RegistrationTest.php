<?php

namespace Tests\Feature\Auth;

use App\Mail\ConfirmarCadastroMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        Mail::fake();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'teste.registro@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'teste.registro@gmail.com']);
        $response->assertRedirect(route('cadastro.aguardando', absolute: false));

        $urlConfirmacao = null;
        Mail::assertSent(ConfirmarCadastroMail::class, function (ConfirmarCadastroMail $mail) use (&$urlConfirmacao) {
            $urlConfirmacao = $mail->urlConfirmacao;

            return $mail->hasTo('teste.registro@gmail.com');
        });

        $this->assertNotNull($urlConfirmacao);

        $confirmResponse = $this->get($urlConfirmacao);
        $this->assertAuthenticated();
        $confirmResponse->assertRedirect(route('painel.index', absolute: false));

        $user = User::where('email', 'teste.registro@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->email_verified_at);

        // Garante que a senha não sofreu duplo hash ao gravar após confirmação
        $this->post('/logout');
        $this->post('/login', [
            'email' => 'teste.registro@gmail.com',
            'password' => 'password',
        ])->assertRedirect(route('painel.index', absolute: false));
        $this->assertAuthenticatedAs($user);
    }
}
