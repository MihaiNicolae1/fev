<?php

namespace Tests\Feature;

use App\Models\DropdownOption;
use App\Models\Record;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test webadmin can create records.
     */
    public function test_webadmin_can_create_records(): void
    {
        $this->actingAsWebadmin();

        $singleSelect = DropdownOption::create([
            'type' => DropdownOption::TYPE_SINGLE_SELECT,
            'label' => 'Option A',
            'value' => 'option_a',
        ]);

        $response = $this->postJson('/api/records', [
            'text_field' => 'Test Record',
            'single_select_id' => $singleSelect->id,
            'multi_select_ids' => [],
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Record created successfully',
            ]);
    }

    /**
     * Test regular user cannot create records.
     */
    public function test_regular_user_cannot_create_records(): void
    {
        $this->actingAsUser();

        $singleSelect = DropdownOption::create([
            'type' => DropdownOption::TYPE_SINGLE_SELECT,
            'label' => 'Option A',
            'value' => 'option_a',
        ]);

        $response = $this->postJson('/api/records', [
            'text_field' => 'Test Record',
            'single_select_id' => $singleSelect->id,
            'multi_select_ids' => [],
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Forbidden.',
            ]);
    }

    /**
     * Test webadmin can update any record.
     */
    public function test_webadmin_can_update_any_record(): void
    {
        $admin = $this->actingAsWebadmin();

        $singleSelect = DropdownOption::create([
            'type' => DropdownOption::TYPE_SINGLE_SELECT,
            'label' => 'Option A',
            'value' => 'option_a',
        ]);

        $record = Record::create([
            'text_field' => 'Original Text',
            'single_select_id' => $singleSelect->id,
            'created_by' => $admin->id,
        ]);

        $response = $this->putJson("/api/records/{$record->id}", [
            'text_field' => 'Updated Text',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'text_field' => 'Updated Text',
                ],
            ]);
    }

    /**
     * Test regular user cannot update records.
     */
    public function test_regular_user_cannot_update_records(): void
    {
        $admin = $this->createUser('webadmin');
        $this->actingAsUser();

        $singleSelect = DropdownOption::create([
            'type' => DropdownOption::TYPE_SINGLE_SELECT,
            'label' => 'Option A',
            'value' => 'option_a',
        ]);

        $record = Record::create([
            'text_field' => 'Original Text',
            'single_select_id' => $singleSelect->id,
            'created_by' => $admin->id,
        ]);

        $response = $this->putJson("/api/records/{$record->id}", [
            'text_field' => 'Updated Text',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Forbidden.',
            ]);
    }

    /**
     * Test webadmin can delete records.
     */
    public function test_webadmin_can_delete_records(): void
    {
        $admin = $this->actingAsWebadmin();

        $singleSelect = DropdownOption::create([
            'type' => DropdownOption::TYPE_SINGLE_SELECT,
            'label' => 'Option A',
            'value' => 'option_a',
        ]);

        $record = Record::create([
            'text_field' => 'To be deleted',
            'single_select_id' => $singleSelect->id,
            'created_by' => $admin->id,
        ]);

        $response = $this->deleteJson("/api/records/{$record->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Record deleted successfully',
            ]);

        $this->assertDatabaseMissing('records', ['id' => $record->id]);
    }

    /**
     * Test regular user cannot delete records.
     */
    public function test_regular_user_cannot_delete_records(): void
    {
        $admin = $this->createUser('webadmin');
        $this->actingAsUser();

        $singleSelect = DropdownOption::create([
            'type' => DropdownOption::TYPE_SINGLE_SELECT,
            'label' => 'Option A',
            'value' => 'option_a',
        ]);

        $record = Record::create([
            'text_field' => 'Cannot delete',
            'single_select_id' => $singleSelect->id,
            'created_by' => $admin->id,
        ]);

        $response = $this->deleteJson("/api/records/{$record->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('records', ['id' => $record->id]);
    }

    /**
     * Test regular user can view records.
     */
    public function test_regular_user_can_view_records(): void
    {
        $admin = $this->createUser('webadmin');

        $singleSelect = DropdownOption::create([
            'type' => DropdownOption::TYPE_SINGLE_SELECT,
            'label' => 'Option A',
            'value' => 'option_a',
        ]);

        Record::create([
            'text_field' => 'Viewable Record',
            'single_select_id' => $singleSelect->id,
            'created_by' => $admin->id,
        ]);

        $this->actingAsUser();

        $response = $this->getJson('/api/records');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * Test regular user can view dropdown options.
     */
    public function test_regular_user_can_view_dropdown_options(): void
    {
        DropdownOption::create([
            'type' => DropdownOption::TYPE_SINGLE_SELECT,
            'label' => 'Option A',
            'value' => 'option_a',
        ]);

        $this->actingAsUser();

        $response = $this->getJson('/api/dropdown-options');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * Test regular user cannot manage dropdown options.
     */
    public function test_regular_user_cannot_create_dropdown_options(): void
    {
        $this->actingAsUser();

        $response = $this->postJson('/api/dropdown-options', [
            'type' => DropdownOption::TYPE_SINGLE_SELECT,
            'label' => 'New Option',
            'value' => 'new_option',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test webadmin can manage dropdown options.
     */
    public function test_webadmin_can_create_dropdown_options(): void
    {
        $this->actingAsWebadmin();

        $response = $this->postJson('/api/dropdown-options', [
            'type' => DropdownOption::TYPE_SINGLE_SELECT,
            'label' => 'New Option',
            'value' => 'new_option',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);
    }
}
