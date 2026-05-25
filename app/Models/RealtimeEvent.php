<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealtimeEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_key',
        'entity_type',
        'entity_id',
        'mesa_id',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
        'entity_id' => 'integer',
        'mesa_id' => 'integer',
    ];

    public static function record(string $eventKey, array $payload = []): self
    {
        return self::create([
            'event_key' => $eventKey,
            'entity_type' => $payload['entity_type'] ?? null,
            'entity_id' => $payload['entity_id'] ?? null,
            'mesa_id' => $payload['mesa_id'] ?? null,
            'payload' => $payload,
        ]);
    }

    public function toRealtimeArray(): array
    {
        return [
            'id' => $this->id,
            'event_key' => $this->event_key,
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,
            'mesa_id' => $this->mesa_id,
            'payload' => $this->payload ?? [],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
