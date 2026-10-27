<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\BoardingStay;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\Clinic;
use App\Models\Invitation;
use App\Models\OneTimePassword;
use App\Models\Pet;
use App\Models\Room;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitStatus;
use Carbon\CarbonImmutable;
use HosmelQ\AuditLog\AuditLogContext;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Tests\Support\Models\TestModel;

final class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureAuditLogs();
        $this->configureCommands();
        $this->configureDates();
        $this->configureFormRequests();
        $this->configureGates();
        $this->configureModels();
        $this->configurePasswordValidation();
        $this->configureRateLimiters();
        $this->configureResources();
        $this->configureUrls();
        $this->configureVite();
    }

    private function configureAuditLogs(): void
    {
        AuditLogContext::resolveActorUsing(fn (): null|User => auth()->user());
        AuditLogContext::resolveTenantUsing(fn (): null|int => auth()->user()?->current_clinic_id);
    }

    private function configureCommands(): void
    {
        DB::prohibitDestructiveCommands($this->app->isProduction());
    }

    private function configureDates(): void
    {
        Date::use(CarbonImmutable::class);
    }

    private function configureFormRequests(): void
    {
        FormRequest::failOnUnknownFields();
    }

    private function configureGates(): void
    {
        Gate::define('admin', fn (User $user): bool => $user->isAdmin());
    }

    private function configureModels(): void
    {
        Model::automaticallyEagerLoadRelationships();
        Model::shouldBeStrict();
        Model::unguard();

        Relation::enforceMorphMap([
            'boarding_stay' => BoardingStay::class,
            'client' => Client::class,
            'client_address' => ClientAddress::class,
            'clinic' => Clinic::class,
            'invitation' => Invitation::class,
            'one_time_password' => OneTimePassword::class,
            'pet' => Pet::class,
            'room' => Room::class,
            'test_model' => TestModel::class,
            'user' => User::class,
            'visit' => Visit::class,
            'visit_status' => VisitStatus::class,
        ]);
    }

    private function configurePasswordValidation(): void
    {
        Password::defaults(
            fn (): Password
                => $this->app->isProduction()
                    ? Password::min(8)->uncompromised()
                    : Password::min(8),
        );
    }

    private function configureRateLimiters(): void
    {
        RateLimiter::for('apple.login', function (Request $request): Limit {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('email.login', function (Request $request): Limit {
            return Limit::perMinute(10)->by(
                str(
                    sprintf('%s|%s', $request->string('email'), $request->ip()),
                )->transliterate()->toString(),
            );
        });

        RateLimiter::for('email.request', function (Request $request): Limit {
            return Limit::perMinute(5)->by(
                str(
                    sprintf('%s|%s', $request->string('email'), $request->ip()),
                )->transliterate()->toString(),
            );
        });

        RateLimiter::for('google.login', function (Request $request): Limit {
            return Limit::perMinute(10)->by($request->ip());
        });
    }

    private function configureResources(): void
    {
        JsonResource::withoutWrapping();
    }

    private function configureUrls(): void
    {
        url()->forceHttps(! $this->app->environment('local', 'testing'));
    }

    private function configureVite(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
