<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * A view raiz carregada na primeira visita à página.
     */
    protected $rootView = 'app';

    /**
     * Determina a "versão" do asset atual (usado pelo Inertia para invalidar cache).
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Dados sempre compartilhados por padrão em toda resposta Inertia.
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
        ];
    }
}
