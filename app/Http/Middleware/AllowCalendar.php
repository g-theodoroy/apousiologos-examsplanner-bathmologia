<?php

namespace App\Http\Middleware;

use App\Anathesi;
use Closure;

class AllowCalendar
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
        // έλεγχος αν έχει μαθήματα. 
        // Αν ΝΑΙ είναι καθηγητής άν ΟΧΙ είναι απουσιολογος
        if (! Anathesi::countMathimata()) return back();
        return $next($request);
    }
}
