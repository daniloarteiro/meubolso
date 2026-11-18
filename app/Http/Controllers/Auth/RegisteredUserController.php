<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ConfirmarCadastroMail;
use App\Models\Categoria;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Valida os dados e envia o link de confirmação sem gravar no banco antes do clique.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc,dns', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'email.email' => 'Informe um endereço de e-mail válido com domínio existente.',
            'email.unique' => 'Este e-mail já está cadastrado.',
        ]);

        $token = Str::random(64);
        $pendente = [
            'name' => $dados['name'],
            'email' => $dados['email'],
            'password' => Hash::make($dados['password']),
        ];

        Cache::put("cadastro_pendente:{$token}", $pendente, now()->addMinutes(60));

        try {
            $this->enviarLinkConfirmacao($token, $pendente['name'], $pendente['email']);
        } catch (\Throwable $e) {
            Cache::forget("cadastro_pendente:{$token}");
            report($e);

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => 'Não foi possível enviar o e-mail de confirmação. Verifique o endereço e tente novamente.']);
        }

        $request->session()->put([
            'cadastro_pendente_token' => $token,
            'cadastro_pendente_email' => $pendente['email'],
        ]);

        return redirect()->route('cadastro.aguardando');
    }

    public function aguardando(Request $request): View|RedirectResponse
    {
        $token = $request->session()->get('cadastro_pendente_token');
        $email = $request->session()->get('cadastro_pendente_email');

        if (! is_string($token) || ! is_string($email) || ! Cache::has("cadastro_pendente:{$token}")) {
            return redirect()->route('cadastro');
        }

        return view('auth.cadastro-aguardando', compact('email'));
    }

    public function reenviar(Request $request): RedirectResponse
    {
        $token = $request->session()->get('cadastro_pendente_token');
        $pendente = is_string($token) ? Cache::get("cadastro_pendente:{$token}") : null;

        if (! is_array($pendente)) {
            return redirect()->route('cadastro')
                ->withErrors(['email' => 'Seu cadastro pendente expirou. Preencha os dados novamente.']);
        }

        Cache::put("cadastro_pendente:{$token}", $pendente, now()->addMinutes(60));

        try {
            $this->enviarLinkConfirmacao($token, $pendente['name'], $pendente['email']);
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['email' => 'Não foi possível reenviar o e-mail agora. Tente novamente em instantes.']);
        }

        return back()->with('status', 'verification-link-sent');
    }

    public function confirmar(Request $request, string $token): RedirectResponse
    {
        $pendente = Cache::get("cadastro_pendente:{$token}");

        if (! is_array($pendente)) {
            return redirect()->route('cadastro')
                ->withErrors(['email' => 'Link de confirmação inválido ou expirado. Faça o cadastro novamente.']);
        }

        if (User::where('email', $pendente['email'])->exists()) {
            Cache::forget("cadastro_pendente:{$token}");

            return redirect()->route('login')
                ->with('status', 'Este e-mail já foi confirmado. Faça login para continuar.');
        }

        $user = DB::transaction(function () use ($pendente) {
            $user = new User([
                'name' => $pendente['name'],
                'email' => $pendente['email'],
                'password' => $pendente['password'],
            ]);
            $user->email_verified_at = now();
            $user->save();

            Categoria::criarPadraoPara($user);

            return $user;
        });

        Cache::forget("cadastro_pendente:{$token}");
        $request->session()->forget(['cadastro_pendente_token', 'cadastro_pendente_email']);

        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect(route('painel.index', absolute: false))
            ->with('sucesso', 'Conta confirmada com sucesso! Bem-vindo ao MeuBolso.');
    }

    private function enviarLinkConfirmacao(string $token, string $nome, string $email): void
    {
        $urlConfirmacao = URL::temporarySignedRoute(
            'cadastro.confirmar',
            now()->addMinutes(60),
            ['token' => $token]
        );

        Mail::to($email)->send(new ConfirmarCadastroMail($nome, $urlConfirmacao));
    }
}
