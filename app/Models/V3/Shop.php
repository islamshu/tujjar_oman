<?php

namespace App\Models\V3;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Shop
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $name
 * @property string|null $logo
 * @property string|null $sliders
 * @property string|null $address
 * @property string|null $facebook
 * @property string|null $google
 * @property string|null $twitter
 * @property string|null $youtube
 * @property string|null $instagram
 * @property string|null $slug
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereFacebook($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereGoogle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereInstagram($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereSliders($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereTwitter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Shop whereYoutube($value)
 * @mixin \Eloquent
 */

class Shop extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class, 'user_id');
    }

    public function getTranslation($field = '', $lang = false)
    {
        $lang = $lang == false ? \App::getLocale() : $lang;
        $shop_translations = $this->hasMany(ShopTranslation::class)->where('lang', $lang)->first();

        return $shop_translations != null ? $shop_translations->$field : $this->$field;
    }

    public function shop_translations()
    {
        return $this->hasMany(ShopTranslation::class);
    }

    public function shop_name()
    {
        $lang = Session()->get('locale');
        $dir = Language::where('code', $lang)->first()->rtl;
        if ($dir == 1) {
            return $this->name_ar;

        } else {
            return $this->name;
        }

    }

    public function shop_address()
    {
        $lang = Session()->get('locale');
        $dir = Language::where('code', $lang)->first()->rtl;
        if ($dir == 1) {
            return $this->address_ar;
        } else {
            return $this->address;
        }
    }

    public function city()
    {
        return $this->belongsTo(City2::class,'address');
    }
}
