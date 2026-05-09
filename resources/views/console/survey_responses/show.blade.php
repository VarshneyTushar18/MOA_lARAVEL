@extends('layout.console')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Survey Response #{{ $response->id }}</h2>
        <a href="{{ route('console.survey_responses.index') }}" class="btn btn-secondary btn-sm">Back</a>
    </div>

    <div class="card p-3 mb-4">
        <h4>Basic Information</h4>
        <div class="row">
            <div class="col-md-6 mb-2"><strong>Name:</strong> {{ $response->name ?: '-' }}</div>
            <div class="col-md-6 mb-2"><strong>Contact:</strong> {{ $response->contact_details ?: '-' }}</div>
            <div class="col-md-6 mb-2"><strong>Address:</strong> {{ $response->address ?: '-' }}</div>
            <div class="col-md-6 mb-2"><strong>Gender:</strong> {{ $response->gender ?: '-' }}</div>
            <div class="col-md-6 mb-2"><strong>Age:</strong> {{ $response->age ?: '-' }}</div>
            <div class="col-md-6 mb-2"><strong>Registration Number:</strong> {{ $response->registration_number ?: '-' }}</div>
            <div class="col-md-6 mb-2"><strong>Date:</strong> {{ optional($response->survey_date)->format('d-m-Y') ?: '-' }}</div>
            <div class="col-md-6 mb-2"><strong>Submitted At:</strong> {{ optional($response->created_at)->format('d-m-Y h:i A') }}</div>
        </div>
    </div>

    @php
        $labels = [
            'illness_or_medication_history' => 'HISTORY OF ANY ILLNESS OR MEDICATION (किसी भी बीमारी या इलाज का इतिहास)',
            'frequent_cold_or_respiratory_allergy' => '1. FREQUENT COLD/ RESPIRATORY ALLERGY (बार-बार सर्दी-जुकाम/श्वसन एलर्जी)',
            'persistent_digestive_defecation_complaints' => '2. PERSISTENT COMPLAINTS RELATED TO DIGESTION AND DEFECATION (पाचन और शौच से संबंधित लगातार शिकायतें)',
            'unable_to_gain_weight_or_weight_loss' => '3. INABILITY TO GAIN BODY WEIGHT OR RECENT WEIGHT REDUCTION (शरीर का वजन बढ़ने में असमर्थता/वजन में कमी)',
            'excessive_anger_or_stress_irritability' => '4. EXCESSIVE ANGER / STRESS (अत्यधिक गुस्सा / तनाव)',
            'persistent_bodyache_or_fatigue' => '5. PERSISTENT BODYACHE / EXCESSIVE FATIGUE (लगातार बदनदर्द/अत्यधिक थकान)',
            'lack_of_enthusiasm_or_energy' => '6. LACK OF ENTHUSIASM / LOSS OF ENERGY (उत्साह/ऊर्जा की कमी)',
            'irregular_menses_amenorrhea_or_infertility' => '7. IRREGULAR MENSES / AMENORRHEA / INFERTILITY',
            'frequent_hospital_visits' => '8. FREQUENTLY HOSPITAL VISIT (बार-बार अस्पताल जाना)',
            'difficulty_or_pain_in_joint_movements' => '9. DIFFICULTY / PAIN IN JOINT MOVEMENTS (जोड़ों में दर्द)',
            'frequent_headache_dizziness_lightheadedness' => '10. FREQUENT HEADACHE/ DIZZINESS (बार-बार सिरदर्द/चक्कर)',
            'known_immunosuppression' => 'K/C/O IMMUNOSUPPRESSION (इम्यूनोसप्रेशन का ज्ञात कारण)',
            'fever' => '1. FEVER (बुखार)',
            'cough_with_sputum_more_than_3_weeks' => '2. COUGH WITH SPUTUM > 3 WEEKS',
            'difficulty_in_breathing' => '3. DIFFICULTY IN BREATHING (सांस लेने में कठिनाई)',
            'blood_in_sputum' => '4. BLOOD IN SPUTUM (थूक में खून)',
            'weight_loss_with_fever' => '5. WEIGHT LOSS WITH FEVER (बुखार के साथ वजन घटना)',
            'chest_pain' => '6. CHEST PAIN (छाती में दर्द)',
            'blood_in_urine' => '7. BLOOD IN URINE (पेशाब में खून)',
            'recurrent_diarrhea_loss_of_appetite_abdominal_distension_pain' => '8. RECURRENT DIARRHEA / LOSS OF APPETITE / ABDOMINAL DISTENSION',
            'other_relevant_information_by_screening_officer' => 'ANY OTHER RELEVANT INFORMATION BY SCREENING OFFICER',
            'previous_treatment_of_tb_and_duration' => '1. PREVIOUS TREATMENT OF TB (IF YES, DURATION)',
            'history_of_extra_pulmonary_tb_details' => '2. HISTORY OF EXTRA PULMONARY TB',
            'family_history_of_tb' => '3. FAMILY HISTORY OF TB',
            'contact_to_tb_patient' => '4. CONTACT TO TB PATIENT',
            'contact_to_mdr_tb_patient' => '5. CONTACT TO MDR-TB PATIENT',
            'history_of_incomplete_tb_treatment' => '6. HISTORY OF INCOMPLETE TB TREATMENT',
            'type_of_case' => 'TYPE OF CASE',
            'remarks' => 'REMARKS / PLEDGE',
            'patient_feedback_form_text' => 'PATIENT FEEDBACK FORM (TEXT)',
            'aware_of_latent_tb_before_2023' => '1. AWARE OF LATENT TB BEFORE 2023',
            'aware_of_latent_tb_now' => '2. NOW AWARE OF LATENT TB',
            'source_of_information_on_latent_tb' => '3. SOURCE OF LATENT TB INFORMATION',
            'aware_of_ongoing_phi_project' => '4. AWARE OF ONGOING PHI PROJECT',
            'satisfied_with_information_provided' => '5. SATISFIED WITH PROVIDED INFORMATION',
            'investigator_name_designation_affiliation_email' => 'INVESTIGATOR NAME, DESIGNATION, AFFILIATION, EMAIL',
            'principal_investigator' => 'PRINCIPAL INVESTIGATOR (प्रमुख अन्वेषक)',
        ];

        $optionMaps = [
            'persistent_digestive_defecation_complaints' => [
                'LOSS_OF_APPETITE' => 'LOSS OF APPETITE',
                'CHRONIC_CONSTIPATION' => 'CHRONIC CONSTIPATION',
                'EXCESSIVE_BELCHING_FLATUS' => 'EXCESSIVE BELCHING/ FLATUS',
                'HARD_STOOL' => 'HARD STOOL',
                'ALTERED_BOWEL_HABIT' => 'ALTERED BOWEL HABIT',
                'BLOATING_HEAVINESS_AFTER_MEAL' => 'BLOATING/ HEAVINESS IN ABDOMEN AFTER MEAL',
            ],
            'known_immunosuppression' => [
                'HIV' => 'HIV',
                'DIRECT_CONTACT_WITH_TB_PATIENT' => 'Direct contact with TB patient',
                'DRUG_ABUSER' => 'Drug abuser',
                'MALNUTRITION' => 'Malnutrition',
                'CHRONIC_KIDNEY_DISEASE' => 'Chronic Kidney Disease',
                'DIABETES' => 'Diabetes',
                'LIVER_CIRRHOSIS' => 'Liver cirrhosis',
            ],
            'source_of_information_on_latent_tb' => [
                'AIIA_HOSPITAL' => 'From AIIA Hospital',
                'NTPC_D_010_AIIA' => 'From NTPC D-010-AIIA',
                'LTBI_SURVEY_CAMPAIGN_2025' => 'During LTBI Survey / Campaign 2025',
                'OTHERS' => 'Others',
            ],
            'type_of_case' => [
                'MDR' => 'MDR',
                'XDR' => 'XDR',
                'PRIMARY_CASE' => 'PRIMARY CASE',
            ],
        ];
    @endphp

    <div class="card p-3">
        <h4>Submitted Field Data</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($labels as $key => $label)
                        @php
                            $rawValue = data_get($response->answers, $key, '-');
                            if (is_array($rawValue)) {
                                $map = $optionMaps[$key] ?? [];
                                $displayValue = collect($rawValue)
                                    ->map(fn($item) => $map[$item] ?? $item)
                                    ->join(', ');
                                $displayValue = $displayValue !== '' ? $displayValue : '-';
                            } else {
                                $map = $optionMaps[$key] ?? [];
                                $displayValue = $map[$rawValue] ?? $rawValue;
                                $displayValue = $displayValue ?: '-';
                            }
                        @endphp
                        <tr>
                            <td>{{ $label }}</td>
                            <td>{{ $displayValue }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
