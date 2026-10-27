<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Clinic;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SetClinicPermissionContext
{
    /**
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = $request->user();

            if (
                $user !== null
                && $user->current_clinic_id === null
                && ($defaultClinic = $user->defaultClinic()) !== null
            ) {
                $user->switchClinic($defaultClinic);
            }

            $activeClinic = $user?->currentClinic;

            setPermissionsTeamId($activeClinic?->id);

            $clinic = $request->route('clinic');

            if ($user !== null && $clinic instanceof Clinic) {
                abort_unless($user->isCurrentClinic($clinic), 403);
            }

            return $next($request);
        } finally {
            setPermissionsTeamId(null);
        }
    }
}
