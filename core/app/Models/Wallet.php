<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function crypto()
    {
        return $this->belongsTo(CryptoCurrency::class, 'crypto_currency_id');
    }

    public function cryptoWallet()
    {
        return $this->belongsTo(CryptoWallet::class, 'user_id','user_id');
    }
}
