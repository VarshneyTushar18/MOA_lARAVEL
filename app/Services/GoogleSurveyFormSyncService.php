<?php

namespace App\Services;

use App\Models\SurveyResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleSurveyFormSyncService
{
    private const FORM_ID = '1FAIpQLSeqoSaOscrrpXqqcqoFY58_MaY4o6-9DEdzP8qs0A9ak-Ujew';

    /** @var array<string, string> */
    private const DIGESTIVE_MAP = [
        'LOSS_OF_APPETITE' => 'LOSS OF APPETITE (भूख न लगना)',
        'CHRONIC_CONSTIPATION' => 'CHRONIC CONSTIPATION(पुराना कब्ज)',
        'EXCESSIVE_BELCHING_FLATUS' => 'EXCESSIVE BELCHING/ FLATUS(अत्यधिक डकार आना/पेट फूलना)',
        'HARD_STOOL' => 'HARD STOOL(/कठोर मल)',
        'ALTERED_BOWEL_HABIT' => 'ALTERED BOWEL HABIT(परिवर्तित आंत्र आदत)',
        'BLOATING_HEAVINESS_AFTER_MEAL' => 'BLOATING/ HEAVINESS IN ABDOMEN AFTER MEAL(खाने के बाद पेट फुलना/भारीपन)',
    ];

    /** @var array<string, string> */
    private const IMMUNO_MAP = [
        'HIV' => 'HIV(एचआईवी)',
        'DIRECT_CONTACT_WITH_TB_PATIENT' => 'Direct contact with TB patient(टीबी रोगी से सीधा संपर्क)',
        'DRUG_ABUSER' => 'Drug abuser(नशा करने वाला)',
        'MALNUTRITION' => 'Malnutrition(कुपोषण)',
        'CHRONIC_KIDNEY_DISEASE' => 'Chronic Kidney Disease (दीर्घकालिक वृक्क रोग)',
        'DIABETES' => 'Diabetes(मधुमेह)',
        'LIVER_CIRRHOSIS' => 'Liver cirrhosis (लीवर सिरोसिस)',
    ];

    /** @var array<string, string> */
    private const SOURCE_MAP = [
        'AIIA_HOSPITAL' => 'From AIIA Hospital (एआईआईए अस्पताल से)',
        'NTPC_D_010_AIIA' => 'From NTPC D-010-AIIA (एनटीपीसी डी-010-एआईआईए अस्पताल से)',
        'LTBI_SURVEY_CAMPAIGN_2025' => 'During LTBI Survey / Campaign 2025 (एलटीबीआई सर्वेक्षण के दौरान / अभियान 2025 के दौरान)',
        'OTHERS' => 'Others (अन्य)',
    ];

    /** @var array<string, string> */
    private const TYPE_OF_CASE_MAP = [
        'MDR' => 'MDR (एमडीआर)',
        'XDR' => 'XDR(एक्सडीआर)',
        'PRIMARY_CASE' => 'PRIMARY CASE (प्राथमिक मामला)',
    ];

    /** @var array<string, string> */
    private const RISK_STAGE_MAP = [
        'LOW_RISK_1_3' => '1-3 Low Risk',
        'MODERATE_RISK_4_6' => '4-6 Moderate Risk',
        'HIGH_RISK_7_10' => '7-10 High Risk',
    ];

    public function isEnabled(): bool
    {
        return (bool) config('services.google_survey_form.enabled', true);
    }

    public function sync(SurveyResponse $response): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        $payload = $this->buildPayload($response);
        $url = sprintf(
            'https://docs.google.com/forms/d/e/%s/formResponse',
            config('services.google_survey_form.form_id', self::FORM_ID)
        );

        try {
            $body = $this->encodeFormBody($payload);
            $httpResponse = Http::timeout(20)
                ->withHeaders([
                    'User-Agent' => 'MOA-Laravel-Survey-Sync/1.0',
                ])
                ->withBody($body, 'application/x-www-form-urlencoded')
                ->post($url);

            if ($httpResponse->successful() || $httpResponse->status() === 302) {
                Log::info('Google survey form synced', ['survey_response_id' => $response->id]);

                return true;
            }

            Log::warning('Google survey form sync failed', [
                'survey_response_id' => $response->id,
                'status' => $httpResponse->status(),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Google survey form sync error', [
                'survey_response_id' => $response->id,
                'message' => $exception->getMessage(),
            ]);
        }

        return false;
    }

    /** @return array<string, string|array<int, string>> */
    private function buildPayload(SurveyResponse $response): array
    {
        $answers = is_array($response->answers) ? $response->answers : [];
        $payload = [];

        $this->set($payload, 'entry.818280189', $response->name);
        $this->set($payload, 'entry.2087023483', $response->address);
        $this->set($payload, 'entry.1780582522', $response->contact_details);
        $this->set($payload, 'entry.254991916', $this->genderValue($response->gender));
        $this->set($payload, 'entry.143994797', $response->age !== null ? (string) $response->age : null);
        $this->set($payload, 'entry.848301501', $response->registration_number);

        $date = $response->survey_date ?? $response->created_at;
        if ($date) {
            $carbon = Carbon::parse($date);
            $payload['entry.2027714422_year'] = (string) $carbon->year;
            $payload['entry.2027714422_month'] = (string) $carbon->month;
            $payload['entry.2027714422_day'] = (string) $carbon->day;
        }

        $this->set($payload, 'entry.1970391224', data_get($answers, 'illness_or_medication_history'));
        $this->set($payload, 'entry.185654897', $this->yesNo(data_get($answers, 'frequent_cold_or_respiratory_allergy'), false));
        $this->setMany($payload, 'entry.400030395', $this->mapOptions(data_get($answers, 'persistent_digestive_defecation_complaints', []), self::DIGESTIVE_MAP));
        $this->set($payload, 'entry.960699154', $this->yesNo(data_get($answers, 'unable_to_gain_weight_or_weight_loss')));
        $this->set($payload, 'entry.1706910457', $this->yesNo(data_get($answers, 'excessive_anger_or_stress_irritability')));
        $this->set($payload, 'entry.954546546', $this->yesNo(data_get($answers, 'persistent_bodyache_or_fatigue')));
        $this->set($payload, 'entry.218537758', $this->yesNo(data_get($answers, 'lack_of_enthusiasm_or_energy')));
        $this->set($payload, 'entry.551379558', $this->yesNo(data_get($answers, 'irregular_menses_amenorrhea_or_infertility')));
        $this->set($payload, 'entry.1786671991', $this->yesNo(data_get($answers, 'frequent_hospital_visits')));
        $this->set($payload, 'entry.932272647', $this->yesNo(data_get($answers, 'difficulty_or_pain_in_joint_movements')));
        $this->set($payload, 'entry.928896554', $this->yesNo(data_get($answers, 'frequent_headache_dizziness_lightheadedness')));
        $this->set($payload, 'entry.1111141736', $this->mapSingle(data_get($answers, 'risk_stage'), self::RISK_STAGE_MAP));
        $this->setMany($payload, 'entry.444051890', $this->mapOptions(data_get($answers, 'known_immunosuppression', []), self::IMMUNO_MAP));
        $this->set($payload, 'entry.908980752', $this->yesNo(data_get($answers, 'fever')));
        $this->set($payload, 'entry.2006542312', $this->yesNo(data_get($answers, 'cough_with_sputum_more_than_3_weeks')));
        $this->set($payload, 'entry.321010577', $this->yesNo(data_get($answers, 'difficulty_in_breathing')));
        $this->set($payload, 'entry.456377108', $this->yesNo(data_get($answers, 'blood_in_sputum')));
        $this->set($payload, 'entry.1638397640', $this->yesNo(data_get($answers, 'weight_loss_with_fever')));
        $this->set($payload, 'entry.1478538495', $this->yesNo(data_get($answers, 'chest_pain')));
        $this->set($payload, 'entry.588954727', $this->yesNo(data_get($answers, 'blood_in_urine')));
        $this->set($payload, 'entry.197331347', $this->yesNo(data_get($answers, 'recurrent_diarrhea_loss_of_appetite_abdominal_distension_pain')));
        $this->set($payload, 'entry.2139973584', data_get($answers, 'other_relevant_information_by_screening_officer'));
        $this->set($payload, 'entry.853246067', data_get($answers, 'previous_treatment_of_tb_and_duration'));
        $this->set($payload, 'entry.61568210', data_get($answers, 'history_of_extra_pulmonary_tb_details'));
        $this->set($payload, 'entry.863591656', $this->yesNo(data_get($answers, 'family_history_of_tb')));
        $this->set($payload, 'entry.1562947629', $this->yesNo(data_get($answers, 'contact_to_tb_patient')));
        $this->set($payload, 'entry.929675608', $this->yesNo(data_get($answers, 'contact_to_mdr_tb_patient')));
        $this->set($payload, 'entry.2041721192', $this->yesNo(data_get($answers, 'history_of_incomplete_tb_treatment')));
        $this->set($payload, 'entry.487712477', $this->mapSingle(data_get($answers, 'type_of_case'), self::TYPE_OF_CASE_MAP));
        $this->set($payload, 'entry.565556797', data_get($answers, 'remarks'));
        $this->set($payload, 'entry.687916395', data_get($answers, 'patient_feedback_form_text'));
        $this->set($payload, 'entry.1243174206', $this->feedbackYesNo(data_get($answers, 'aware_of_latent_tb_before_2023')));
        $this->set($payload, 'entry.374003122', $this->feedbackYesNo(data_get($answers, 'aware_of_latent_tb_now')));
        $this->set($payload, 'entry.1512068036', $this->mapSingle(data_get($answers, 'source_of_information_on_latent_tb'), self::SOURCE_MAP));
        $this->set($payload, 'entry.392425474', $this->feedbackYesNo(data_get($answers, 'aware_of_ongoing_phi_project')));
        $this->set($payload, 'entry.657680517', $this->feedbackYesNo(data_get($answers, 'satisfied_with_information_provided')));

        $investigator = trim((string) data_get($answers, 'investigator_name_designation_affiliation_email', ''));
        $this->set($payload, 'entry.1742826180', $investigator !== '' ? $investigator : null);

        return $payload;
    }

    /** @param array<string, string|array<int, string>> $payload */
    private function set(array &$payload, string $key, mixed $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $payload[$key] = (string) $value;
    }

    /** @param array<string, string|array<int, string>> $payload @param array<int, string> $values */
    private function setMany(array &$payload, string $key, array $values): void
    {
        if ($values === []) {
            return;
        }

        $payload[$key] = $values;
    }

    private function genderValue(?string $gender): ?string
    {
        return match ($gender) {
            'Male' => 'Male (पुरुष)',
            'Female' => 'Female (महिला)',
            default => null,
        };
    }

    private function yesNo(?string $value, bool $useTitleCaseNo = true): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = strtoupper(trim($value));

        if ($normalized === 'YES') {
            return 'YES';
        }

        if ($normalized === 'NO') {
            return $useTitleCaseNo ? 'NO' : 'No';
        }

        return $value;
    }

    private function feedbackYesNo(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match (strtolower(trim($value))) {
            'yes' => 'Yes हां',
            'no' => 'No नहीं',
            default => $value,
        };
    }

    /** @param array<int, string> $values @param array<string, string> $map @return array<int, string> */
    private function mapOptions(mixed $values, array $map): array
    {
        if (! is_array($values)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($value) => $map[$value] ?? null,
            $values
        )));
    }

    /** @param array<string, string> $map */
    private function mapSingle(?string $value, array $map): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $map[$value] ?? $value;
    }

    /** @param array<string, string|array<int, string>> $payload */
    private function encodeFormBody(array $payload): string
    {
        $parts = [];

        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $item) {
                    $parts[] = rawurlencode($key).'='.rawurlencode($item);
                }

                continue;
            }

            $parts[] = rawurlencode($key).'='.rawurlencode($value);
        }

        return implode('&', $parts);
    }
}
