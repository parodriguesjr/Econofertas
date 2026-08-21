<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockTunnelAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if(str_ends_with($request->getHost(), '.trycloudflare.com'), 404);

        return $next($request);
    }
}
