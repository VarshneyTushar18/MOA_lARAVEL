<?php

namespace App\Exports;

use App\Models\SurveyResponse;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SurveyResponsesExport implements FromCollection, WithHeadings, WithMapping
{
    private Collection $rows;

    protected array $answerKeys = [
        'illness_or_medication_history',
        'frequent_cold_or_respiratory_allergy',
        'persistent_digestive_defecation_complaints',
        'unable_to_gain_weight_or_weight_loss',
        'excessive_anger_or_stress_irritability',
        'persistent_bodyache_or_fatigue',
        'lack_of_enthusiasm_or_energy',
        'irregular_menses_amenorrhea_or_infertility',
        'frequent_hospital_visits',
        'difficulty_or_pain_in_joint_movements',
        'frequent_headache_dizziness_lightheadedness',
        'known_immunosuppression',
        'fever',
        'cough_with_sputum_more_than_3_weeks',
        'difficulty_in_breathing',
        'blood_in_sputum',
        'weight_loss_with_fever',
        'chest_pain',
        'blood_in_urine',
        'recurrent_diarrhea_loss_of_appetite_abdominal_distension_pain',
        'other_relevant_information_by_screening_officer',
        'previous_treatment_of_tb_and_duration',
        'history_of_extra_pulmonary_tb_details',
        'family_history_of_tb',
        'contact_to_tb_patient',
        'contact_to_mdr_tb_patient',
        'history_of_incomplete_tb_treatment',
        'type_of_case',
        'remarks',
        'patient_feedback_form_text',
        'aware_of_latent_tb_before_2023',
        'aware_of_latent_tb_now',
        'source_of_information_on_latent_tb',
        'aware_of_ongoing_phi_project',
        'satisfied_with_information_provided',
        'investigator_name_designation_affiliation_email',
        'principal_investigator',
    ];

    public function __construct(?Collection $rows = null)
    {
        $this->rows = $rows ?? SurveyResponse::latest()->get();
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return array_merge(
            [
                'id',
                'name',
                'address',
                'contact_details',
                'gender',
                'age',
                'registration_number',
                'survey_date',
                'submitted_at',
            ],
            $this->answerKeys
        );
    }

    public function map($response): array
    {
        $answers = is_array($response->answers) ? $response->answers : [];

        $answerValues = array_map(function ($key) use ($answers) {
            $value = data_get($answers, $key, '');

            if (is_array($value)) {
                return implode(', ', $value);
            }

            return (string) $value;
        }, $this->answerKeys);

        return array_merge(
            [
                $response->id,
                $response->name,
                $response->address,
                $response->contact_details,
                $response->gender,
                $response->age,
                $response->registration_number,
                optional($response->survey_date)->format('Y-m-d'),
                optional($response->created_at)->format('Y-m-d H:i:s'),
            ],
            $answerValues
        );
    }
}

