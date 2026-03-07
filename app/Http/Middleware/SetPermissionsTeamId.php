<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPermissionsTeamId
{
    public function handle(Request $request, Closure $next): Response
    {
        if (tenant('id')) {
            setPermissionsTeamId(tenant('id'));
            \Illuminate\Support\Facades\Log::info("SetPermissionsTeamId middleware: " . tenant('id'));
        }

        return $next($request);
    }
}
