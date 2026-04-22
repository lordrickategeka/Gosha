<?php

namespace App\Traits;

trait GeneratesOrderNumber
{
    protected static function bootGeneratesOrderNumber(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getOrderNumberField()})) {
                $model->{$model->getOrderNumberField()} = $model->generateOrderNumber();
            }
        });
    }

    public function getOrderNumberField(): string
    {
        return $this->orderNumberField ?? 'order_number';
    }

    public function getOrderNumberPrefix(): string
    {
        return $this->orderNumberPrefix ?? 'ORD';
    }

    public function generateOrderNumber(): string
    {
        $prefix = $this->getOrderNumberPrefix();
        $date = now()->format('Ymd');

        // Get the last order number for today
        $lastOrder = static::withoutGlobalScopes()
            ->where($this->getOrderNumberField(), 'like', "{$prefix}-{$date}-%")
            ->orderByDesc($this->getOrderNumberField())
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->{$this->getOrderNumberField()}, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $date, $nextNumber);
    }
}
