<?php

namespace App\Http\Requests;

use App\Rules\InventoryCheckRule;
use Illuminate\Foundation\Http\FormRequest;

class TransactionUpdateValidationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return \Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'branch_id' => 'required|exists:branches,id',
            'items' => ['nullable', new InventoryCheckRule($this->all())]
        ];
    }

    public function messages() 
    {
        return [
            'branch_id.required' => 'Please select branch'
        ];
    }
}
