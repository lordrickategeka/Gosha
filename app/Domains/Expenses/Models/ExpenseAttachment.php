<?php

namespace App\Domains\Expenses\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ExpenseAttachment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'expense_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'attachment_type',
        'uploaded_by',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attachment) {
            $attachment->uploaded_at = now();
        });

        static::deleting(function ($attachment) {
            // Delete the physical file when the record is deleted
            if (Storage::exists($attachment->file_path)) {
                Storage::delete($attachment->file_path);
            }
        });
    }

    // Relationships
    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Helpers
    public function getUrl(): string
    {
        return Storage::url($this->file_path);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function isImage(): bool
    {
        return in_array($this->file_type, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']);
    }

    public function isPdf(): bool
    {
        return $this->file_type === 'application/pdf';
    }

    public function getIconAttribute(): string
    {
        if ($this->isImage()) {
            return '🖼️';
        }
        if ($this->isPdf()) {
            return '📄';
        }
        return '📎';
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->attachment_type) {
            'receipt' => 'success',
            'invoice' => 'primary',
            'supporting_doc' => 'info',
            'photo' => 'secondary',
            default => 'ghost',
        };
    }
}
