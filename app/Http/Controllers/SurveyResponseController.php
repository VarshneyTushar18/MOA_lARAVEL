<?php

namespace App\Http\Controllers;

use App\Models\SurveyResponse;
use Illuminate\Http\Request;

class SurveyResponseController extends Controller
{
    public function create()
    {
        return view('pages.screening_performa');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'address' => 'required|string|max:1000',
            'contact_details' => 'required|string|max:30',
            'gender' => 'nullable|in:Male,Female',
            'age' => 'nullable|integer|min:0|max:120',
            'registration_number' => 'required|string|max:255',
            'survey_date' => 'nullable|date',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'nullable|string|max:2000',

            'illness_or_medication_history' => 'nullable|string|max:3000',
            'frequent_cold_or_respiratory_allergy' => 'nullable|in:YES,NO',
            'persistent_digestive_defecation_complaints' => 'nullable|array',
            'persistent_digestive_defecation_complaints.*' => 'in:LOSS_OF_APPETITE,CHRONIC_CONSTIPATION,EXCESSIVE_BELCHING_FLATUS,HARD_STOOL,ALTERED_BOWEL_HABIT,BLOATING_HEAVINESS_AFTER_MEAL',
            'unable_to_gain_weight_or_weight_loss' => 'nullable|in:YES,NO',
            'excessive_anger_or_stress_irritability' => 'nullable|in:YES,NO',
            'persistent_bodyache_or_fatigue' => 'nullable|in:YES,NO',
            'lack_of_enthusiasm_or_energy' => 'nullable|in:YES,NO',
            'irregular_menses_amenorrhea_or_infertility' => 'nullable|in:YES,NO',
            'frequent_hospital_visits' => 'nullable|in:YES,NO',
            'difficulty_or_pain_in_joint_movements' => 'nullable|in:YES,NO',
            'frequent_headache_dizziness_lightheadedness' => 'nullable|in:YES,NO',
            'known_immunosuppression' => 'nullable|array',
            'known_immunosuppression.*' => 'in:HIV,DIRECT_CONTACT_WITH_TB_PATIENT,DRUG_ABUSER,MALNUTRITION,CHRONIC_KIDNEY_DISEASE,DIABETES,LIVER_CIRRHOSIS',
            'fever' => 'nullable|in:YES,NO',
            'cough_with_sputum_more_than_3_weeks' => 'nullable|in:YES,NO',
            'difficulty_in_breathing' => 'nullable|in:YES,NO',
            'blood_in_sputum' => 'nullable|in:YES,NO',
            'weight_loss_with_fever' => 'nullable|in:YES,NO',
            'chest_pain' => 'nullable|in:YES,NO',
            'blood_in_urine' => 'nullable|in:YES,NO',
            'recurrent_diarrhea_loss_of_appetite_abdominal_distension_pain' => 'nullable|in:YES,NO',
            'other_relevant_information_by_screening_officer' => 'nullable|string|max:3000',
            'previous_treatment_of_tb_and_duration' => 'nullable|string|max:1000',
            'history_of_extra_pulmonary_tb_details' => 'nullable|string|max:1000',
            'family_history_of_tb' => 'nullable|in:YES,NO',
            'contact_to_tb_patient' => 'nullable|in:YES,NO',
            'contact_to_mdr_tb_patient' => 'nullable|in:YES,NO',
            'history_of_incomplete_tb_treatment' => 'nullable|in:YES,NO',
            'type_of_case' => 'nullable|in:MDR,XDR,PRIMARY_CASE',
            'remarks' => 'nullable|string|max:4000',
            'patient_feedback_form_text' => 'nullable|string|max:2000',
            'aware_of_latent_tb_before_2023' => 'nullable|in:Yes,No',
            'aware_of_latent_tb_now' => 'nullable|in:Yes,No',
            'source_of_information_on_latent_tb' => 'nullable|in:AIIA_HOSPITAL,NTPC_D_010_AIIA,LTBI_SURVEY_CAMPAIGN_2025,OTHERS',
            'aware_of_ongoing_phi_project' => 'nullable|in:Yes,No',
            'satisfied_with_information_provided' => 'nullable|in:Yes,No',
            'investigator_name_designation_affiliation_email' => 'required|string|max:1000',
            'principal_investigator' => 'nullable|string|max:255',
        ]);

        $answers = $request->except([
            '_token',
            'name',
            'address',
            'contact_details',
            'gender',
            'age',
            'registration_number',
            'survey_date',
            'email',
            'phone',
            'message',
        ]);

        SurveyResponse::create([
            'name' => $validated['name'] ?? null,
            'address' => $validated['address'] ?? null,
            'contact_details' => $validated['contact_details'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'age' => $validated['age'] ?? null,
            'registration_number' => $validated['registration_number'] ?? null,
            'survey_date' => $validated['survey_date'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'message' => $validated['message'] ?? null,
            'answers' => $answers,
        ]);

        return redirect()->route('survey.form')->with('success', 'Survey submitted successfully.');
    }
}
