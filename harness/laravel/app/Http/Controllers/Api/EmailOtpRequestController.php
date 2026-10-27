<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use function HosmelQ\AuditLog\audit_log;

use App\Actions\GenerateOneTimePasswordCode;
use App\Enums\AuditLogEvent;
use App\Http\Requests\Api\EmailOtpRequest;
use App\Notifications\OneTimePasswordNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

final class EmailOtpRequestController
{
    public function __invoke(
        EmailOtpRequest $request,
        GenerateOneTimePasswordCode $generateOneTimePasswordCode,
    ): JsonResponse {
        return DB::transaction(
            function () use ($generateOneTimePasswordCode, $request): JsonResponse {
                $otp = $generateOneTimePasswordCode->handle(
                    $email = $request->string('email')->toString(),
                );

                audit_log(AuditLogEvent::AuthOtpRequested)->target($otp)->record();

                Notification::route('mail', $email)->notify(new OneTimePasswordNotification($otp));

                return new JsonResponse();
            },
        );
    }
}
