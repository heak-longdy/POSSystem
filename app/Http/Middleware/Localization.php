<?php
namespace App\Http\Middleware;
use Closure;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;
use App\Support\Language;

class Localization
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
        $preferredLanguage = optional($request->user())->language_preference;
        $lang = Language::resolve($preferredLanguage ?: Session::get('locale', Session::get('language', App::getLocale())));

        Session::put('locale', $lang);
        Session::put('language', $lang);
        App::setLocale($lang);
        Carbon::setLocale($lang);

        return $next($request);
    }
}
