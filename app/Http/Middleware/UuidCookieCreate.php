<?php
namespace App\Http\Middleware;

use Closure;

class UuidCookieCreate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->cookie('uid')) {
            if ($user = $request->cookie('user') AND gettype($user) === 'string') {
                $uid = $user;
                \Cookie::queue(\Cookie::forget('user'));
            } else {
                $uid = trim(file_get_contents('/proc/sys/kernel/random/uuid'));
            }
            \Cookie::queue(\Cookie::make('uid', $uid, 10000000, null, null, null, false));
        }

        return $next($request);
    }
}
