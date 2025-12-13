<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasUuidFields;
use App\Utils\UuidHelper;

/**
 * @deprecated Use Service model instead
 * This class is kept for backward compatibility only
 */
class Product extends Service
{
    // Alias for backward compatibility
}
