# Update Tests: Mapping Cabinet Input

PATCH update: Cabinet update delegates typed label input and checks nested redirect and toast.

## Updates the record

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\Cabinets\Inputs\UpdateCabinetInput;
use App\Actions\Cabinets\UpdateCabinet;
use App\Models\Cabinet;

describe('update', function (): void {
    it('updates the record', function (): void {
        $cabinet = Cabinet::factory()->createOne();

        login(team: $cabinet->member->team);

        mock(UpdateCabinet::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                Cabinet $cabinetArgument,
                UpdateCabinetInput $input
            ): bool => $cabinetArgument->is($cabinet)
                && $input->label === 'Miami');

        $response = patch(route('teams.members.cabinets.update', [
            'team' => $cabinet->member->team,
            'member' => $cabinet->member,
            'cabinet' => $cabinet,
        ]), [
            'label' => 'Miami',
        ]);

        $response->assertRedirectToRoute('teams.members.cabinets.show', [
            'team' => $cabinet->member->team,
            'member' => $cabinet->member,
            'cabinet' => $cabinet,
        ])
            ->assertToast('Cabinet updated');
    });
});
```
