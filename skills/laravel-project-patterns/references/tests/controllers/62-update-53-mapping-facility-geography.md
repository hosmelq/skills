# Update Tests: Mapping Facility Geography

Pest PATCH update: Facility update maps the submitted name and both current-country/cleared-province geographic transformations.

Use valid country/province pairs from the consuming suite. Empty country context and changing country without a province are separate cases.

## Complete block

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

        signIn(team: $facility->team);

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
            'facility' => $facility->public_id,
        ])
            ->assertToast('Facility updated');
    });

    it('maps the province using the current country when country is empty', function (): void {
        $facility = Facility::factory()->createOne([
            'country_code' => CountryCode::Nicaragua,
        ]);

        signIn(team: $facility->team);

        mock(UpdateFacility::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Facility $facilityArgument, UpdateFacilityInput $input): bool => $facilityArgument->is($facility)
                && $input->provinceCode === 'MN');

        $response = patch(route('teams.facilities.update', [
            'team' => $facility->team,
            'facility' => $facility,
        ]), [
            'country_code' => '',
            'province_code' => 'MN',
        ]);

        $response->assertRedirectToRoute('teams.facilities.show', [
            'team' => $facility->team,
            'facility' => $facility->public_id,
        ])
            ->assertToast('Facility updated');
    });

    it('clears the province when changing country without a province', function (): void {
        $facility = Facility::factory()->createOne([
            'country_code' => CountryCode::Nicaragua,
            'province_code' => 'MN',
        ]);

        signIn(team: $facility->team);

        mock(UpdateFacility::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (Facility $facilityArgument, UpdateFacilityInput $input): bool => $facilityArgument->is($facility)
                && $input->countryCode === CountryCode::CostaRica
                && $input->provinceCode === null);

        $response = patch(route('teams.facilities.update', [
            'team' => $facility->team,
            'facility' => $facility,
        ]), [
            'country_code' => CountryCode::CostaRica->value,
        ]);

        $response->assertRedirectToRoute('teams.facilities.show', [
            'team' => $facility->team,
            'facility' => $facility->public_id,
        ])
            ->assertToast('Facility updated');
    });
});
```
