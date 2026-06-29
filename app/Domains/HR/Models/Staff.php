<?php
namespace App\Domains\HR\Models;

use App\Domains\Commissions\Models\Commission;
use App\Domains\Expenses\Models\Expense;
use App\Domains\Operations\Models\JobCard;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staff';

    protected $fillable = [
        'name', 'phone', 'email', 'role', 'is_active', 'base_salary'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'base_salary' => 'decimal:2',
    ];

    public function jobCards()
    {
        return $this->hasMany(JobCard::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function employmentDetails()
    {
        return $this->hasOne(EmploymentDetail::class);
    }
}
