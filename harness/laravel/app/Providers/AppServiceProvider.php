<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\BoardingStay;
use App\Models\Clinic;
use App\Models\Invitation;
use App\Models\OneTimePassword;
use App\Models\Client;
use App\Models\ClientAddress;
use App\Models\Pet;
use App\Models\Room;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\ServiceProvider;
use Tests\Support\Models\TestModel;

final class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Date::use(CarbonImmutable::class);

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
}
