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
