<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class IpWhitelistMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if IP whitelist enforcement is active
        if (!config('app.force_ip_whitelist', false)) {
            return $next($request); // Skip check if not enforced
        }

        // Get whitelisted IPs from environment variable (comma-separated)
        $whitelistedIps = explode(',', config('app.whitelisted_ip_addresses', ''));

        // Trim whitespace from each IP address
        $whitelistedIps = array_map('trim', $whitelistedIps);

        // Get the client's IP address (Laravel handles trusted proxies)
        $clientIp = $request->ip();

        // Check if the client's IP is in the whitelist
        if (!in_array($clientIp, $whitelistedIps)) {
            // If not whitelisted, abort with 403 Forbidden
            throw new HttpException(403, 'Access denied.');
        }

        // If whitelisted, proceed with the request
        return $next($request);
    }
}
