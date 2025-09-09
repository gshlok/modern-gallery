<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImageStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->canUpload();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $maxSize = config('media.max_size', 10240); // KB
        $allowedMimes = implode(',', config('media.allowed_mimes', ['jpeg', 'jpg', 'png', 'gif', 'webp']));

        return [
            'image' => [
                'required',
                'file',
                'image',
                "max:{$maxSize}",
                "mimes:{$allowedMimes}",
            ],
            'title' => 'nullable|string|max:255',
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
            'image.required' => 'Please select an image to upload.',
            'image.image' => 'The file must be a valid image.',
            'image.max' => 'The image size cannot exceed ' . config('media.max_size', 10240) . ' KB.',
            'image.mimes' => 'Only ' . implode(', ', config('media.allowed_mimes', [])) . ' images are allowed.',
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
            'visibility' => $this->visibility ?? 'public',
            'allow_download' => $this->boolean('allow_download', true),
        ]);

        // Clean up tags
        if ($this->has('tags') && is_string($this->tags)) {
            $tags = array_filter(array_map('trim', explode(',', $this->tags)));
            $this->merge(['tags' => $tags]);
        }
    }
}
