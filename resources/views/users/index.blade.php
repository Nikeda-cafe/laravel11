<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>ユーザー一覧</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-light">
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand fw-bold" href="{{ url('/') }}">Laravel App</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/') }}">ホーム</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="{{ route('users.index') }}">ユーザー一覧</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="min-vh-100 py-5">
            <div class="container">
                <h1 class="h2 fw-semibold mb-4">ユーザー一覧</h1>

                <div class="card shadow">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="p-3 text-start small fw-medium text-muted">ID</th>
                                        <th class="p-3 text-start small fw-medium text-muted">名前</th>
                                        <th class="p-3 text-start small fw-medium text-muted">メールアドレス</th>
                                        <th class="p-3 text-start small fw-medium text-muted">メール認証日時</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($users as $user)
                                        <tr>
                                            <td class="p-3 small text-dark">{{ $user->id }}</td>
                                            <td class="p-3 small text-dark">{{ $user->name }}</td>
                                            <td class="p-3 small text-dark">{{ $user->email }}</td>
                                            <td class="p-3 small text-secondary">
                                                {{ $user->emailVerifiedAt ? \Illuminate\Support\Carbon::parse($user->emailVerifiedAt)->format('Y/m/d H:i') : '未認証' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="p-4 text-center small text-muted">ユーザーが登録されていません。</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
