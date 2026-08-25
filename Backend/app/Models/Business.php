<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;
    protected $fillable = [
        'business_name',
        'business_email',
        'business_phone',
        'business_website',
        'business_logo',
        'business_industry',
        'business_location',
        'business_code',
        'tax_rate',
        'kra_pin',
        'tax_api_key',
        'mpesa_shortcode',
        'mpesa_shortcode_type',
        'mpesa_passkey',
        'mpesa_consumer_key',
        'mpesa_consumer_secret',
        'digitax_api_key',
        'digitax_environment',
        'digitax_enabled',
    ];

    protected $hidden = [
        'tax_api_key',
        'mpesa_passkey',
        'mpesa_consumer_key',
        'mpesa_consumer_secret',
        'digitax_api_key',
    ];

    protected function casts(): array
    {
        return [
            'tax_api_key' => 'encrypted',
            'mpesa_passkey' => 'encrypted',
            'mpesa_consumer_key' => 'encrypted',
            'mpesa_consumer_secret' => 'encrypted',
            'digitax_api_key' => 'encrypted',
            'digitax_enabled' => 'boolean',
        ];
    }
}
