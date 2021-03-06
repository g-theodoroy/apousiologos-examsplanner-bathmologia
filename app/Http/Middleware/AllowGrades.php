<?php

namespace App\Http\Middleware;

use App\Anathesi;
use App\Config;
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
        // έλεγχος αν έχει μαθήματα. 
        // Αν ΝΑΙ είναι καθηγητής άν ΟΧΙ είναι απουσιολογος
        if (! Anathesi::countMathimata()) return back();
        // έλεγχος αν είναι επιλεγμένη ενεργή περίοδος βαθμολογίας
        if (! Config::getConfigValueOf('activeGradePeriod')) return back();
        return $next($request);
    }
}
