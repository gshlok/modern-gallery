<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImageUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('image'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'alt_text' => 'nullable|string|max:255',
            'album_id' => 'nullable|exists:albums,id',
            'visibility' => 'in:public,unlisted,private',
            'license' => 'nullable|string|max:100',
            'allow_download' => 'boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The title field is required.',
            'album_id.exists' => 'The selected album does not exist.',
            'visibility.in' => 'Invalid visibility option.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values
        $this->merge([
            'allow_download' => $this->boolean('allow_download', true),
        ]);

        // Clean up tags
        if ($this->has('tags') && is_string($this->tags)) {
            $tags = array_filter(array_map('trim', explode(',', $this->tags)));
            $this->merge(['tags' => $tags]);
        }
    }
}
