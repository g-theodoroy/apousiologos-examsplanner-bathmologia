<?php

namespace App\Http\Middleware;

use Closure;

class AllowGrades
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // έλεγχος αν είναι επιλεγμένη ενεργή περίοδος βαθμολογίας
        if(! \App\Config::getConfigValueOf('activeGradePeriod')) return back();
        return $next($request);
    }
}
