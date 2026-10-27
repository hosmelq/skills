# Controllers: Token Page Controller

Serve a link that carries a secret token, such as an invitation, to guests and signed-in users alike: 404 for unknown or used tokens, an expired page, accept immediately for the invited signed-in user, otherwise render sign-in or registration and return to the link afterwards.

- Keep the route outside `guest` and `auth` groups and read an optional user with `#[CurrentUser]`.
- Set the intended URL so sign-in and registration come back to the link.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Invitations\AcceptInvitation;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class InvitationController
{
    /**
     * Keep the route outside guest/auth groups to serve guests, the signed-in
     * invited account, and a different signed-in account.
     */
    public function show(
        string $token,
        AcceptInvitation $acceptInvitation,
        #[CurrentUser] null|User $currentUser,
    ): RedirectResponse|Response {
        $invitation = Invitation::findByToken($token);

        abort_if(! $invitation instanceof Invitation, 404);

        $props = [
            'clinic' => $invitation->clinic->name,
            'invitedAt' => $invitation->created_at->toIso8601String(),
            'invitedBy' => $invitation->invitedByUser->name?->full(),
            'token' => $token,
        ];

        if ($invitation->isExpired()) {
            return Inertia::render('auth/InvitationExpired', [
                ...$props,
                'expiredAt' => $invitation->expires_at->toIso8601String(),
            ]);
        }

        if ($currentUser?->email === $invitation->email) {
            $acceptInvitation->handle($currentUser, $invitation);

            return to_route('clinics.clients.index', $invitation->clinic);
        }

        $props['signedInEmail'] = $currentUser?->email;

        redirect()->setIntendedUrl(route('invitations.show', ['token' => $token]));

        $user = User::query()->where('email', $invitation->email)->first();

        if ($user !== null) {
            return Inertia::render('auth/InvitationLogin', [
                ...$props,
                'email' => $invitation->email,
                'expiresAt' => $invitation->expires_at->toIso8601String(),
                'firstName' => $user->first_name,
            ]);
        }

        return Inertia::render('auth/InvitationRegister', [
            ...$props,
            'email' => $invitation->email,
            'expiresAt' => $invitation->expires_at->toIso8601String(),
        ]);
    }
}
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

use App\Models\Invitation;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

describe('show', function (): void {
    it('returns not found for an unknown or accepted invitation', function (string $token): void {
        Invitation::factory()->accepted()->withToken('accepted-token')->createOne();

        $response = get(route('invitations.show', ['token' => $token]));

        $response->assertNotFound();
    })->with([
        'accepted' => ['accepted-token'],
        'unknown' => ['unknown-token'],
    ]);

    it('shows the expired page', function (): void {
        Invitation::factory()->expired()->withToken('invitation-token')->createOne();

        $response = get(route('invitations.show', ['token' => 'invitation-token']));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page): void {
                $page->component('auth/InvitationExpired');
            });
    });

    it('accepts the invitation when signed in with the invited mixed-case email', function (): void {
        $user = User::factory()->createOne(['email' => 'mariela@example.com']);
        $invitation = Invitation::factory()
            ->withToken('invitation-token')
            ->createOne(['email' => 'Mariela@Example.com']);
        $response = actingAs($user)
            ->get(route('invitations.show', ['token' => 'invitation-token']));

        $response->assertRedirectToRoute('clinics.clients.index', $invitation->clinic);

        expect($user->fresh()?->belongsToClinic($invitation->clinic))
            ->toBeTrue()
            ->and($invitation->fresh()?->accepted_at)
            ->not->toBeNull();
    });

    it('shows the sign in page when the mixed-case email has an account', function (): void {
        $user = User::factory()->createOne(['email' => 'mariela@example.com']);

        Invitation::factory()
            ->withToken('invitation-token')
            ->createOne(['email' => 'Mariela@Example.com']);

        $response = get(route('invitations.show', ['token' => 'invitation-token']));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($user): void {
                $page->component('auth/InvitationLogin')
                    ->where('email', $user->email)
                    ->where('firstName', $user->first_name);
            });
    });

    it('shows the new account page', function (): void {
        $invitation = Invitation::factory()->withToken('invitation-token')->createOne();
        $response = get(route('invitations.show', ['token' => 'invitation-token']));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($invitation): void {
                $page->component('auth/InvitationRegister')
                    ->where('clinic', $invitation->clinic->name)
                    ->where('email', $invitation->email)
                    ->where('invitedBy', $invitation->invitedByUser->name?->full());
            });
    });
});
```
