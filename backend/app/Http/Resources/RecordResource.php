<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'text_field' => $this->text_field,
            'single_select_id' => $this->single_select_id,
            'single_select' => $this->whenLoaded('singleSelect', function () {
                return new DropdownOptionResource($this->singleSelect);
            }),
            'multi_select_ids' => $this->whenLoaded('multiSelectOptions', function () {
                return $this->multiSelectOptions->pluck('id');
            }),
            'multi_select_options' => $this->whenLoaded('multiSelectOptions', function () {
                return DropdownOptionResource::collection($this->multiSelectOptions);
            }),
            'created_by' => $this->created_by,
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
