<?php

namespace Tests\Feature;

use App\Models\DropdownOption;
use App\Models\Record;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecordTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup test fixtures.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create dropdown options for tests
        DropdownOption::create([
            'type' => DropdownOption::TYPE_SINGLE_SELECT,
            'label' => 'Option A',
            'value' => 'option_a',
        ]);

        DropdownOption::create([
            'type' => DropdownOption::TYPE_SINGLE_SELECT,
            'label' => 'Option B',
            'value' => 'option_b',
        ]);

        DropdownOption::create([
            'type' => DropdownOption::TYPE_MULTI_SELECT,
            'label' => 'Tag 1',
            'value' => 'tag_1',
        ]);

        DropdownOption::create([
            'type' => DropdownOption::TYPE_MULTI_SELECT,
            'label' => 'Tag 2',
            'value' => 'tag_2',
        ]);
    }

    /**
     * Test can list records.
     */
    public function test_can_list_records(): void
    {
        $admin = $this->actingAsWebadmin();

        Record::create([
            'text_field' => 'Record 1',
            'single_select_id' => 1,
            'created_by' => $admin->id,
        ]);

        Record::create([
            'text_field' => 'Record 2',
            'single_select_id' => 2,
            'created_by' => $admin->id,
        ]);

        $response = $this->getJson('/api/records');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'text_field',
                        'single_select_id',
                        'single_select',
                        'multi_select_ids',
                        'multi_select_options',
                        'created_by',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                ],
            ]);
    }

    /**
     * Test can create record with valid data.
     */
    public function test_can_create_record_with_valid_data(): void
    {
        $this->actingAsWebadmin();

        $response = $this->postJson('/api/records', [
            'text_field' => 'New Record',
            'single_select_id' => 1,
            'multi_select_ids' => [3, 4],
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Record created successfully',
                'data' => [
                    'text_field' => 'New Record',
                    'single_select_id' => 1,
                    'multi_select_ids' => [3, 4],
                ],
            ]);

        $this->assertDatabaseHas('records', [
            'text_field' => 'New Record',
            'single_select_id' => 1,
        ]);
    }

    /**
     * Test create record requires text_field.
     */
    public function test_create_record_requires_text_field(): void
    {
        $this->actingAsWebadmin();

        $response = $this->postJson('/api/records', [
            'single_select_id' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['text_field']);
    }

    /**
     * Test can show single record.
     */
    public function test_can_show_single_record(): void
    {
        $admin = $this->actingAsWebadmin();

        $record = Record::create([
            'text_field' => 'Test Record',
            'single_select_id' => 1,
            'created_by' => $admin->id,
        ]);

        $response = $this->getJson("/api/records/{$record->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $record->id,
                    'text_field' => 'Test Record',
                ],
            ]);
    }

    /**
     * Test show returns 404 for non-existent record.
     */
    public function test_show_returns_404_for_nonexistent_record(): void
    {
        $this->actingAsWebadmin();

        $response = $this->getJson('/api/records/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * Test can update record.
     */
    public function test_can_update_record(): void
    {
        $admin = $this->actingAsWebadmin();

        $record = Record::create([
            'text_field' => 'Original Text',
            'single_select_id' => 1,
            'created_by' => $admin->id,
        ]);

        $response = $this->putJson("/api/records/{$record->id}", [
            'text_field' => 'Updated Text',
            'single_select_id' => 2,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'text_field' => 'Updated Text',
                    'single_select_id' => 2,
                ],
            ]);

        $this->assertDatabaseHas('records', [
            'id' => $record->id,
            'text_field' => 'Updated Text',
            'single_select_id' => 2,
        ]);
    }

    /**
     * Test can update record with multi-select options.
     */
    public function test_can_update_record_with_multi_select(): void
    {
        $admin = $this->actingAsWebadmin();

        $record = Record::create([
            'text_field' => 'Test Record',
            'single_select_id' => 1,
            'created_by' => $admin->id,
        ]);

        $response = $this->putJson("/api/records/{$record->id}", [
            'multi_select_ids' => [3, 4],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'multi_select_ids' => [3, 4],
                ],
            ]);

        $this->assertDatabaseHas('record_multi_options', [
            'record_id' => $record->id,
            'dropdown_option_id' => 3,
        ]);

        $this->assertDatabaseHas('record_multi_options', [
            'record_id' => $record->id,
            'dropdown_option_id' => 4,
        ]);
    }

    /**
     * Test can delete record.
     */
    public function test_can_delete_record(): void
    {
        $admin = $this->actingAsWebadmin();

        $record = Record::create([
            'text_field' => 'To Delete',
            'single_select_id' => 1,
            'created_by' => $admin->id,
        ]);

        $recordId = $record->id;

        $response = $this->deleteJson("/api/records/{$recordId}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Record deleted successfully',
            ]);

        $this->assertDatabaseMissing('records', ['id' => $recordId]);
    }

    /**
     * Test delete cascades to multi-select pivot table.
     */
    public function test_delete_cascades_to_multi_select(): void
    {
        $admin = $this->actingAsWebadmin();

        $record = Record::create([
            'text_field' => 'Test Record',
            'single_select_id' => 1,
            'created_by' => $admin->id,
        ]);

        $record->syncMultiSelectOptions([3, 4]);

        $recordId = $record->id;

        $this->deleteJson("/api/records/{$recordId}");

        $this->assertDatabaseMissing('record_multi_options', ['record_id' => $recordId]);
    }

    /**
     * Test pagination works correctly.
     */
    public function test_records_pagination(): void
    {
        $admin = $this->actingAsWebadmin();

        // Create 20 records
        for ($i = 1; $i <= 20; $i++) {
            Record::create([
                'text_field' => "Record {$i}",
                'single_select_id' => 1,
                'created_by' => $admin->id,
            ]);
        }

        $response = $this->getJson('/api/records?per_page=5');

        $response->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'per_page' => 5,
                    'total' => 20,
                ],
            ]);

        $this->assertCount(5, $response->json('data'));
    }

    /**
     * Test created_by is automatically set to current user.
     */
    public function test_created_by_is_set_to_current_user(): void
    {
        $admin = $this->actingAsWebadmin();

        $response = $this->postJson('/api/records', [
            'text_field' => 'My Record',
            'single_select_id' => 1,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'created_by' => $admin->id,
                ],
            ]);
    }
}
