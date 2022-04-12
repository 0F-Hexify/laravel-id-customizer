<?php

namespace Hexify\LaraIdCustomizer;

use Hexify\LaraIdCustomizer\Helpers\Random;

interface IdFactory
{
    /**
     * Factory Methods
     */
    const INCREMENTAL = 'incremental';
    const RANDOM = 'random';

    /**
     * Predefined sets of characters.
     */
    const ALPHA = Random::ALPHA;
    const NUMERIC = Random::NUMERIC;
    const ALPHA_NUMERIC = Random::ALPHA_NUMERIC;

}
