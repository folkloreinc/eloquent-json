<?php

return [
    'validator' => [
        'constraints' =>
            \JsonSchema\Constraints\Constraint::CHECK_MODE_APPLY_DEFAULTS |
            \JsonSchema\Constraints\Constraint::CHECK_MODE_TYPE_CAST
    ]
];
