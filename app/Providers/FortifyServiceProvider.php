<?php

namespace App\Providers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use Illuminate\Support\Facades\RateLimiter;
use App\Actions\Fortify\UpdateUserProfileInformation;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
    
        // Rate limiter per il login
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());
            Log::info("L'utente $throttleKey ha effettuato un tentativo di login.");
            
    
            return Limit::perMinute(5)->by($throttleKey);
        });

        Event::listen(Logout::class, function (Logout $event) {
            $user = $event->user;
            $ip = request()->ip(); // Prende l'IP dalla request attuale
        
            if ($user) {
                Log::info("L'utente {$user->email} ha effettuato il logout da IP {$ip}.");
            }
        });
    
        // Rate limiter per il two-factor authentication
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    
        // login
        Fortify::loginView(function () {
            return view('auth.login');
        });
    
        // registrazione
        Fortify::registerView(function () {
            return view('auth.register');
            Log::info("L'utente $throttleKey ha effettuato un tentativo di registrazione.");
        });

        RateLimiter::for('article-search', function (Request $request) {
            $ip = $request->ip();
    
            // Se l'IP è già bloccato, impedisce la richiesta
            if (Cache::has("blocked:$ip")) {
                abort(429, "Troppe richieste. Riprova più tardi.");
            }
    
            // Definisce il limite (es. 20 richieste al minuto per IP)
            $limit = Limit::perMinute(5)->by($ip);
    
            // Se l'IP supera il limite, viene bloccato per 10 minuti
            if ($limit) {
                Cache::put("blocked:$ip", true, now()->addMinutes(3));
            }
    
            return $limit;

            RateLimiter::for('carrers', function (Request $request) {
                $ip = $request->ip();
                return Limit::perMinute(5)->by($ip);
            });
            
            RateLimiter::for('submit', function (Request $request) {
                $ip = $request->ip();
                return Limit::perMinute(5)->by($ip);
            });
        });
    }
}
    




