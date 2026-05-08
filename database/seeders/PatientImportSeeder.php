<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;

class PatientImportSeeder extends Seeder
{
    public function run()
    {
        $file = database_path('seeders/Patient.csv');

        if (!file_exists($file) || !is_readable($file)) {
            return;
        }

        $header = null;
        if (($handle = fopen($file, 'r')) !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                if (!$header) {
                    $header = $row;
                    continue;
                }

                // Skip empty rows
                if (empty(array_filter($row))) continue;

                $data = array_combine($header, $row);

                // Fix date format
                if (!empty($data['date'])) {
                    $data['date'] = date('Y-m-d', strtotime($data['date']));
                }

                // Fix empty age
                $data['age'] = is_numeric($data['age']) ? (int)$data['age'] : null;

                Patient::create($data);
            }
            fclose($handle);
        }
    }
}