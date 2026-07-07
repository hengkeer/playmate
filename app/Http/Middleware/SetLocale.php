<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check for query parameter override (e.g. ?lang=id)
        if ($request->has('lang') && in_array($request->query('lang'), ['en', 'id'])) {
            app()->setLocale($request->query('lang'));
            session()->put('locale', $request->query('lang'));
        } 
        // 2. Check session
        elseif (session()->has('locale')) {
            app()->setLocale(session()->get('locale'));
        } 
        // 3. Fallback to browser language
        else {
            $languages = $request->getLanguages();
            $locale = 'en';
            
            foreach ($languages as $lang) {
                $langCode = substr($lang, 0, 2);
                if (in_array($langCode, ['en', 'id'])) {
                    $locale = $langCode;
                    break;
                }
            }
            
            app()->setLocale($locale);
        }
        
        return $next($request);
    }
}
