<?php

declare(strict_types=1);

use App\Enums\VisitStage;

return [

    'defaults' => [
        'initial_name' => 'Checked in',
    ],

    'stages' => [
        VisitStage::Cancelled->value => 'Cancelled',
        VisitStage::CheckedIn->value => 'Checked in',
        VisitStage::Completed->value => 'Completed',
        VisitStage::InExam->value => 'In exam',
    ],

    'validation' => [
        'initial_must_be_checked_in' => 'The initial visit status must use the Checked in stage.',
    ],

];
