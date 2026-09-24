# Update Tests: Mapping Facility Geography

PATCH update: Facility update maps the submitted name and both current-country/cleared-province geographic transformations. Use valid seeded country/province pairs.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\Facilities\Inputs\UpdateFacilityInput;
use App\Actions\Facilities\UpdateFacility;
use App\Enums\CountryCode;
use App\Models\Facility;

describe('update', function (): void {
    it('updates the record', function (): void {
        $facility = Facility::factory()->createOne();

        login(team: $facility->team);

        mock(UpdateFacility::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Facility $facilityArgument, UpdateFacilityInput $input): bool => $facilityArgument->is($facility)
                && $input->name === 'Updated Hub');

        $response = patch(route('teams.facilities.update', [
            'team' => $facility->team,
            'facility' => $facility,
        ]), [
            'name' => 'Updated Hub',
        ]);

        $response->assertRedirectToRoute('teams.facilities.show', [
            'team' => $facility->team,
            'facility' => $facility->sqid,
        ])
            ->assertToast('Facility updated');
    });

    it('maps the province using the current country when country is empty', function (): void {
        $facility = Facility::factory()->createOne([
            'country_code' => CountryCode::UnitedStates,
        ]);

        login(team: $facility->team);

        mock(UpdateFacility::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Facility $facilityArgument, UpdateFacilityInput $input): bool => $facilityArgument->is($facility)
                && $input->provinceCode === 'WA');

        $response = patch(route('teams.facilities.update', [
            'team' => $facility->team,
            'facility' => $facility,
        ]), [
            'country_code' => '',
            'province_code' => 'WA',
        ]);

        $response->assertRedirectToRoute('teams.facilities.show', [
            'team' => $facility->team,
            'facility' => $facility->sqid,
        ])
            ->assertToast('Facility updated');
    });

    it('clears the province when changing country without a province', function (): void {
        $facility = Facility::factory()->createOne([
            'country_code' => CountryCode::UnitedStates,
            'province_code' => 'WA',
        ]);

        login(team: $facility->team);

        mock(UpdateFacility::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Facility $facilityArgument, UpdateFacilityInput $input): bool => $facilityArgument->is($facility)
                && $input->countryCode === CountryCode::Canada
                && $input->provinceCode === null);

        $response = patch(route('teams.facilities.update', [
            'team' => $facility->team,
            'facility' => $facility,
        ]), [
            'country_code' => CountryCode::Canada->value,
        ]);

        $response->assertRedirectToRoute('teams.facilities.show', [
            'team' => $facility->team,
            'facility' => $facility->sqid,
        ])
            ->assertToast('Facility updated');
    });
});
```
