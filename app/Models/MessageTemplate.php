<?php

namespace App\Models;

use App\Concerns\LogsActivityWithTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class MessageTemplate extends Model
{
    use BelongsToTenant, HasFactory, LogsActivityWithTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'subject',
        'body',
        'channel',
        'placeholders',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'placeholders' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Parse the template with given data.
     */
    public function parse(array $data): string
    {
        $body = $this->body;

        foreach ($data as $key => $value) {
            $body = str_replace("{{$key}}", (string) $value, $body);
        }

        return $body;
    }

    /**
     * Parse the subject with given data.
     */
    public function parseSubject(array $data): ?string
    {
        if (! $this->subject) {
            return null;
        }

        $subject = $this->subject;

        foreach ($data as $key => $value) {
            $subject = str_replace("{{$key}}", (string) $value, $subject);
        }

        return $subject;
    }
}
