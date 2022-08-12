<?php

namespace BookStack\Actions;

use BookStack\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Bulletin extends Model
{
    protected $fillable = ['role_id'];

    /**
     * Get the related model that can be bulletined.
     */
    public function bulletinable(): MorphTo
    {
        return $this->morphTo();
    }
}
