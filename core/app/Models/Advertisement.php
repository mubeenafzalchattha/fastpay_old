<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model {
    use HasFactory, GlobalStatus;

    public function fiat() {
        return $this->belongsTo(FiatCurrency::class, 'fiat_currency_id');
    }

    public function fiatGateway() {
        return $this->belongsTo(FiatGateway::class);
    }

    public function crypto() {
        return $this->belongsTo(CryptoCurrency::class, 'crypto_currency_id');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function tradeRequests() {
        return $this->hasMany(Trade::class);
    }

    public function reviews() {
        return $this->hasMany(Review::class);
    }

    // Scopes

    public function scopeBuy($query) {
        return $query->where('type', 1);
    }

    public function scopeSell($query) {
        return $query->where('type', 2);
    }

    public function typeBadge(): Attribute {
        return new Attribute(function () {
            $html = '';
            if ($this->type == 1) {
                $html = '<span class="badge badge--primary">' . trans("Buy") . '</span>';
            } else {
                $html = '<span class="badge badge--warning">' . trans("Sell") . '</span>';
            }
            return $html;
        });
    }

    public function marginValue(): Attribute {
        return new Attribute(function () {
            $html = '';
            if ($this->fixed_price > 0) {
                $html = '<span class="text--warning">' . trans('Fixed') . '</span>';
            } else {
                $html = '<span class="text--info">' . trans('Margin') . ': ' . getAmount($this->margin) . '%</span>';
            }
            return $html;
        });
    }
}
