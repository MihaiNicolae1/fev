<?php

namespace Database\Seeders;

use App\Models\DropdownOption;
use App\Models\Record;
use App\Models\User;
use Illuminate\Database\Seeder;

class RecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $singleSelectOptions = DropdownOption::singleSelectOptions();
        $multiSelectOptions = DropdownOption::multiSelectOptions();

        if (!$admin || $singleSelectOptions->isEmpty()) {
            return;
        }

        // Create sample records
        $records = [
            [
                'text_field' => 'Sample Record 1',
                'single_select_id' => $singleSelectOptions->first()->id,
                'multi_select_ids' => $multiSelectOptions->take(2)->pluck('id')->toArray(),
            ],
            [
                'text_field' => 'Sample Record 2',
                'single_select_id' => $singleSelectOptions->skip(1)->first()->id,
                'multi_select_ids' => $multiSelectOptions->skip(1)->take(2)->pluck('id')->toArray(),
            ],
            [
                'text_field' => 'Sample Record 3',
                'single_select_id' => $singleSelectOptions->last()->id,
                'multi_select_ids' => $multiSelectOptions->take(3)->pluck('id')->toArray(),
            ],
        ];

        foreach ($records as $recordData) {
            $multiSelectIds = $recordData['multi_select_ids'];
            unset($recordData['multi_select_ids']);

            $record = Record::firstOrCreate(
                ['text_field' => $recordData['text_field']],
                array_merge($recordData, ['created_by' => $admin->id])
            );

            // Sync multi-select options
            $record->syncMultiSelectOptions($multiSelectIds);
        }
    }
}
