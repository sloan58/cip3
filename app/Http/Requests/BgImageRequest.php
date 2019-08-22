<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;

class BgImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ($this->isMethod('POST')) {
            return [
                'name' => 'required|min:5|max:255',
                'dimensions' => 'required',
                'full_image' => 'required|image|mimes:png',
                'thumbnail_image' => 'required|image|mimes:png',
                'device_pools' => 'required'
            ];
        } else {
            return [
                'name' => 'required|min:5|max:255',
                'dimensions' => 'required',
                'full_image' => 'required_if:thumbnail_image,1|image|mimes:png',
                'thumbnail_image' => 'required_if:full_image,1|image|mimes:png',
                'device_pools' => 'required'
            ];
        }
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            //
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            //
        ];
    }
}
