<?php

namespace App\Models\V3;

use Illuminate\Database\Eloquent\Model;

class Comparisons extends Model
{
      protected $guarded = [];
      protected $table ='comparisons';
      public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
