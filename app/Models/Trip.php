<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_date',
        'trip_time',
        'passengers',
        'origin',
        'destination',
        'payment_method',
        'equipment_number',
        'is_ida',
        'is_vuelta',
        'amount',
        'notes',
        'driver_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'trip_date' => 'date',
            'trip_time' => 'datetime:H:i',
            'is_ida' => 'boolean',
            'is_vuelta' => 'boolean',
            'amount' => 'decimal:2',
            'passengers' => 'integer',
        ];
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'E' => 'Efectivo',
            'CH' => 'Cargo Habitación',
            'TJ' => 'Tarjeta',
            default => $this->payment_method,
        };
    }
}
