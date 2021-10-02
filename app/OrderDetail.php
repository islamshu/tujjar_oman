<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function pickup_point()
    {
        return $this->belongsTo(PickupPoint::class);
    }

    public function refund_request()
    {
        return $this->hasOne(RefundRequest::class);
    }

    public function atts(){
        $variation = $this->variation;
        if ($variation != null) {
            $pieces = explode("-", $variation);
            foreach ($pieces as $key=> $piece) {
                $color = Color::where('name', $piece)->first();
                $size = DB::table('size_att')->where('name_ar', $piece)->Orwhere('name_en', $piece)->first();
                $fabric = DB::table('fabrics')->where('name_ar', $piece)->Orwhere('name_en', $piece)->first();
                if ($color) {
                    $color_name = $color->name;
                } elseif ($size) {
                    $ss=   $size->name_ar;
                } elseif ($fabric) {
                    $fabric = $fabric->name_ar;
                }
            }
            if($color_name !=null){
                $data['color']=$color_name;
            }
            if(@$ss !=null){

                $data['size'] = @$ss;
            }
            if(@$fabric !=null){
                $data['fabric'] =  @$fabric;
            }
        }else{
            $data = null;
        }
        return $data;

    }

    public function atts_en()
    {
        $variation = $this->variation;
        if ($variation != null) {
            $pieces = explode("-", $variation);
            foreach ($pieces as $key => $piece) {
                $color = Color::where('name', $piece)->first();
                $size = DB::table('size_att')->where('name_ar', $piece)->Orwhere('name_en', $piece)->first();
                $fabric = DB::table('fabrics')->where('name_ar', $piece)->Orwhere('name_en', $piece)->first();
                if ($color) {
                    $color_name = $color->name;
                } elseif ($size) {
                    $ss = $size->name_ar;
                } elseif ($fabric) {
                    $fabric = $fabric->name_en;
                }
            }
            if ($color_name != null) {
                $data['color'] = $color_name;
            }
            if ($ss != null) {
                $data['size'] = $ss;
            }
            if ($fabric != null) {
                $data['fabric'] = $fabric;
            }
        } else {
            $data = null;
        }
        return $data;
    }
}
