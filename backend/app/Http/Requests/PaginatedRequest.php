<?php

namespace App\Http\Requests;

use App\Http\Pagination\HasPagination;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Base class for paginated requests.
 * Extend this class for any index/list endpoint that needs pagination.
 * 
 * Usage:
 *   class IndexRecordsRequest extends PaginatedRequest
 *   {
 *       protected function paginationConfig(): array
 *       {
 *           return array_merge(parent::paginationConfig(), [
 *               'allowed_sort_fields' => ['id', 'text_field', 'created_at'],
 *               'default_sort_field' => 'id',
 *           ]);
 *       }
 *   }
 */
abstract class PaginatedRequest extends FormRequest
{
    use HasPagination;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return $this->paginationRules();
    }
}
