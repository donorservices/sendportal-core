<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Requests\Api;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Http\Requests\CampaignStoreRequest as BaseCampaignStoreRequest;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\CampaignStatus;
use Sendportal\Base\Repositories\Campaigns\CampaignTenantRepositoryInterface;
use Sendportal\Base\Repositories\TagTenantRepository;

class CampaignStoreRequest extends BaseCampaignStoreRequest
{
    /**
     * @var CampaignTenantRepositoryInterface
     */
    protected $campaigns;

    /**
     * @var int|null
     */
    protected $workspaceId;

    /**
     * The campaign resolved from the {campaign} route parameter (update only).
     *
     * @var Campaign|null
     */
    protected $resolvedCampaign;

    public function __construct(CampaignTenantRepositoryInterface $campaigns)
    {
        parent::__construct();

        $this->campaigns = $campaigns;
        $this->workspaceId = Sendportal::currentWorkspaceId();

        Validator::extendImplicit('valid_status', function ($attribute, $value, $parameters, $validator) {
            return $this->route('campaign')
                ? $this->getCampaign()->status_id === CampaignStatus::STATUS_DRAFT
                : true;
        });
    }

    /**
     * @throws \Exception
     */
    public function getCampaign(): Campaign
    {
        return $this->resolvedCampaign = $this->campaigns->find($this->workspaceId, $this->route('campaign'));
    }

    public function rules(): array
    {
        $tags = app(TagTenantRepository::class)->pluck(
            $this->workspaceId,
            'id'
        );

        $rules = [
            'send_to_all' => [
                'required',
                'boolean',
            ],
            'tags' => [
                // Not 'required_unless:send_to_all,1': that compares strictly
                // when the value is a bool, so JSON `true` still required tags.
                Rule::requiredIf(fn () => ! $this->boolean('send_to_all')),
                'array',
                Rule::in($tags),
            ],
            'tags.*' => [
                'integer',
            ],
            'scheduled_at' => [
                'required',
                'date',
            ],
            'save_as_draft' => [
                'nullable',
                'boolean',
            ],
            'status_id' => 'valid_status',
        ];

        return array_merge($this->getRules(), $rules);
    }

    public function messages(): array
    {
        return [
            'valid_status' => __('A campaign cannot be updated if its status is not draft'),
            'tags.in' => 'One or more of the tags is invalid.',
        ];
    }
}
