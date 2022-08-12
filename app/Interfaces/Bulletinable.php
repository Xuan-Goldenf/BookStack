<?php

namespace BookStack\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Bulletinable
{
    /**
     * Get the related bulletin instances.
     */
    public function bulletins(): MorphMany;
}
