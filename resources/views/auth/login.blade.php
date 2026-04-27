@extends('layouts.auth')

@section('content')
<div class="auth-card">
    <div class="auth-header">
        <h1>Ação Direta</h1>
        <p class="auth-subtitle">Faça login para continuar</p>
    </div>

    @if($errors->any())
        <div class="error-message">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('login') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="email">E-mail</label>
            <input type="email" name="email" id="email" class="form-control" required autofocus placeholder="seu@email.com">
        </div>

        <div class="form-group">
            <label for="password">Senha</label>
            <input type="password" name="password" id="password" class="form-control" required placeholder="••••••••">
        </div>

        <div class="form-check">
            <input type="checkbox" name="remember" id="remember" class="form-checkbox">
            <label for="remember">Lembrar de mim</label>
        </div>

        <button type="submit" class="btn btn-primary w-full">
            Entrar
        </button>
    </form>
</div>
@endsection
