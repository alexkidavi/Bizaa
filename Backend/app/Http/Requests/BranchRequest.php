<?php
namespace App\Http\Requests;

use App\Policies\BranchPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BranchRequest extends FormRequest
{

    public function authorize(): bool
    {
        $user   = Auth::user();
        $policy = new BranchPolicy();
        return $policy->create($user);
    }
    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'Branch name is required.',
            'name.max'      => 'Branch name cannot exceed 255 characters.',
            'location.max'  => 'Location cannot exceed 255 characters.',
        ];
    }
}
