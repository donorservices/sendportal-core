<?php

namespace Sendportal\Base\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Rules\ContainsContentTag;

class TemplateUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'max:255',
                Rule::unique('sendportal_templates')
                    ->where('workspace_id', Sendportal::currentWorkspaceId())
                    ->ignore($this->template),
            ],
            'content' => ['required', 'string', new ContainsContentTag()],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => __('The template name must be unique.'),
        ];
    }
}
