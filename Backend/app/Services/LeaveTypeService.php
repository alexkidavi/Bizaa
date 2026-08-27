<?php

namespace App\Services;

use App\Models\LeaveType;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class LeaveTypeService
{
    protected const CONFIG_FIELDS = [
        'name', 'days_allowed', 'description',
        'accrual_rule', 'accrual_rate', 'carry_forward_allowed', 'max_carry_forward_days',
        'gender_eligibility', 'requires_documentation', 'requires_approval', 'is_paid', 'is_active',
    ];

    public function list(): \Illuminate\Database\Eloquent\Collection
    {
        return LeaveType::where('business_id', Auth::user()->business_id)
            ->withCount('leaves')
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): LeaveType
    {
        return LeaveType::create(array_merge(
            ['business_id' => Auth::user()->business_id],
            Arr::only($data, self::CONFIG_FIELDS)
        ));
    }

    public function update(int $id, array $data): LeaveType
    {
        $lt = $this->find($id);
        $lt->update(Arr::only($data, self::CONFIG_FIELDS));
        return $lt->fresh();
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
    }

    public function find(int $id): LeaveType
    {
        return LeaveType::where('id', $id)
            ->where('business_id', Auth::user()->business_id)
            ->firstOrFail();
    }
}
