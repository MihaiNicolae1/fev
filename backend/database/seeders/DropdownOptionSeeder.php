<?php

namespace Database\Seeders;

use App\Models\DropdownOption;
use Illuminate\Database\Seeder;

class DropdownOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Single select options
        $singleSelectOptions = [
            ['label' => 'Option A', 'value' => 'option_a'],
            ['label' => 'Option B', 'value' => 'option_b'],
            ['label' => 'Option C', 'value' => 'option_c'],
        ];

        foreach ($singleSelectOptions as $option) {
            DropdownOption::firstOrCreate(
                [
                    'type' => DropdownOption::TYPE_SINGLE_SELECT,
                    'value' => $option['value'],
                ],
                [
                    'label' => $option['label'],
                    'is_active' => true,
                ]
            );
        }

        // Multi select options
        $multiSelectOptions = [
            ['label' => 'Tag 1', 'value' => 'tag_1'],
            ['label' => 'Tag 2', 'value' => 'tag_2'],
            ['label' => 'Tag 3', 'value' => 'tag_3'],
            ['label' => 'Tag 4', 'value' => 'tag_4'],
        ];

        foreach ($multiSelectOptions as $option) {
            DropdownOption::firstOrCreate(
                [
                    'type' => DropdownOption::TYPE_MULTI_SELECT,
                    'value' => $option['value'],
                ],
                [
                    'label' => $option['label'],
                    'is_active' => true,
                ]
            );
        }
    }
}
