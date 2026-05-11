@extends('layout.frontend')

@section('content')
<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>Mass Screening Performa for LTBI</h1>
            </div>
        </div>
    </div>
</section>

<section class="ntpcsection py-4">
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card p-4">
            <form method="POST" action="{{ route('survey.submit') }}">
                @csrf

                <h4 class="mb-3">Basic Details / बुनियादी विवरण</h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>NAME (नाम) <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required minlength="2" maxlength="255" autocomplete="name">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>CONTACT DETAILS (संपर्क विवरण) <span class="text-danger">*</span></label>
                        <input type="text" name="contact_details" class="form-control" value="{{ old('contact_details') }}" required>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label>ADDRESS / BLOCK / TALUKA / TEHSIL (पता/ब्लॉक/तालुका/तहसील) <span class="text-danger">*</span></label>
                        <textarea name="address" class="form-control" rows="2" required>{{ old('address') }}</textarea>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>GENDER (लिंग)</label>
                        <select name="gender" class="form-control">
                            <option value="">Select</option>
                            <option value="Male" @selected(old('gender')==='Male')>Male (पुरुष)</option>
                            <option value="Female" @selected(old('gender')==='Female')>Female (महिला)</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>AGE (आयु)</label>
                        <input type="number" name="age" class="form-control" value="{{ old('age') }}" min="0" max="120" step="1" inputmode="numeric">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>REGISTRATION (UHID NUMBER / SCREENING NUMBER / AADHAR NUMBER) (पंजीकरण संख्या (विशिष्ट स्वास्थ्य पहचान नंबर) / स्क्रीनिंग नंबर / आधार नंबर) <span class="text-danger">*</span></label>
                        <input type="text" name="registration_number" class="form-control" value="{{ old('registration_number') }}" required maxlength="255" inputmode="text" aria-describedby="registration_number_hint">
                        <small id="registration_number_hint" class="form-text text-muted">Aadhaar must be exactly 12 digits (spaces optional). Other IDs may include letters as printed on the card.</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>DATE (तिथि)</label>
                        <input type="date" name="survey_date" class="form-control" value="{{ old('survey_date') }}">
                    </div>
                    <div class="col-md-8 mb-3">
                        <label>HISTORY OF ANY ILLNESS OR MEDICATION (किसी भी बीमारी या इलाज का इतिहास)</label>
                        <textarea name="illness_or_medication_history" class="form-control" rows="2">{{ old('illness_or_medication_history') }}</textarea>
                    </div>
                </div>

                <h4 class="mt-3 mb-3">PRESENT SYMPTOMS (वर्तमान लक्षण)</h4>
                <div class="row">
                    @php
                        $yesNoFields = [
                            'frequent_cold_or_respiratory_allergy' => '1. FREQUENT COLD/ RESPIRATORY ALLERGY (>= 4 TIMES/YEAR) (बार-बार सर्दी-जुकाम/श्वसन एलर्जी (>= 4 बार/वर्ष))',
                            'unable_to_gain_weight_or_weight_loss' => '3. INABILITY TO GAIN BODY WEIGHT OR RECENT SIGNIFICANT BODY WEIGHT REDUCTION (>= 4 KG/MONTH) (शरीर का वजन बढ़ने में असमर्थता/हाल ही में महत्वपूर्ण (>= 4 किलो/माह) शरीर के वजन में कमी)',
                            'excessive_anger_or_stress_irritability' => '4. EXCESSIVE ANGER / STRESS REFLECTED BY EASY IRRITABILITY (अत्यधिक गुस्सा /तनाव परिलक्षित चिड़चिड़ापन)',
                            'persistent_bodyache_or_fatigue' => '5. PERSISTENT BODYACHE / EXCESSIVE FATIGUE (लगातार बदनदर्द/अत्यधिक थकान)',
                            'lack_of_enthusiasm_or_energy' => '6. LACK OF ENTHUSIASM / LOSS OF ENERGY IN DAILY LIFE (दैनिक जीवन में उत्साह की कमी/ऊर्जा की कमी)',
                            'irregular_menses_amenorrhea_or_infertility' => '7. IRREGULAR MENSES >= 3 MONTHS/ AMENORRHEA >= 3 MONTHS/ INABILITY TO CONCEIVE DESPITE REGULAR COITUS FOR 2 YEARS (3 महीने या उससे अधिक तक अनियमित मासिक धर्म / 3 महीने या उससे अधिक तक मासिक धर्म का ना होना / 2 साल तक नियमित सहवास के बावजूद गर्भ धारण नहीं होना)',
                            'frequent_hospital_visits' => '8. FREQUENTLY HOSPITAL VISIT DUE TO VARIOUS REASONS (>= 6 TIMES/YEAR) (विभिन्न कारणों से बार-बार (6 बार या उससे अधिक/वर्ष) अस्पताल जाना)',
                            'difficulty_or_pain_in_joint_movements' => '9. DIFFICULTY / PAIN IN JOINT MOVEMENTS (<= 2 JOINTS) (जोड़ों में दर्द <= 2 जोड़)',
                            'frequent_headache_dizziness_lightheadedness' => '10. FREQUENT HEADACHE/ DIZZINESS/ LIGHTHEADEDNESS (>= 4 TIMES/MONTH) (बार-बार सिरदर्द /चक्कर आना (>= 4 बार/महीना))',
                        ];
                        $digestiveSelections = old('persistent_digestive_defecation_complaints', []);
                    @endphp

                    <div class="col-md-12 mb-3">
                        <label>2. PERSISTENT COMPLAINTS RELATED TO DIGESTION AND DEFECATION (PERSISTENT FOR MORE THAN 3 MONTHS CAN ONLY BE CONSIDERED AS POSITIVE)<br>पाचन और शौच से संबंधित लगातार शिकायतें (3 महीने से अधिक तक बने रहने को ही सकारात्मक माना जा सकता है।)</label>
                        <div class="border rounded p-3">
                            @foreach([
                                'LOSS_OF_APPETITE' => 'Loss of appetite',
                                'CHRONIC_CONSTIPATION' => 'Chronic constipation',
                                'EXCESSIVE_BELCHING_FLATUS' => 'Excessive belching/flatus',
                                'HARD_STOOL' => 'Hard stool',
                                'ALTERED_BOWEL_HABIT' => 'Altered bowel habit',
                                'BLOATING_HEAVINESS_AFTER_MEAL' => 'Bloating/heaviness in abdomen after meal'
                            ] as $value => $label)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="persistent_digestive_defecation_complaints[]" id="digestive_{{ $value }}" value="{{ $value }}" @checked(in_array($value, $digestiveSelections, true))>
                                    <label class="form-check-label" for="digestive_{{ $value }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @foreach($yesNoFields as $field => $label)
                        <div class="col-md-6 mb-3">
                            <label>{{ $label }}</label>
                            <select name="{{ $field }}" class="form-control">
                                <option value="">Select</option>
                                <option value="YES" @selected(old($field)==='YES')>YES</option>
                                <option value="NO" @selected(old($field)==='NO')>NO</option>
                            </select>
                        </div>
                    @endforeach

                    <div class="col-md-12 mb-2">
                        <label class="mb-0">11. Stages</label>
                        <ul class="mb-0">
                            <li>1-3 Low Risk</li>
                            <li>4-6 Moderate Risk</li>
                            <li>7-10 High Risk</li>
                        </ul>
                    </div>
                </div>

                <h4 class="mt-3 mb-3">K/C/O IMMUNOSUPPRESSION (इम्यूनोसप्रेशन का ज्ञात कारण)</h4>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        @php
                            $immunosuppressionSelections = old('known_immunosuppression', []);
                        @endphp
                        <div class="border rounded p-3">
                            @foreach([
                                'HIV' => 'HIV',
                                'DIRECT_CONTACT_WITH_TB_PATIENT' => 'Direct contact with TB patient',
                                'DRUG_ABUSER' => 'Drug abuser',
                                'MALNUTRITION' => 'Malnutrition',
                                'CHRONIC_KIDNEY_DISEASE' => 'Chronic Kidney Disease',
                                'DIABETES' => 'Diabetes',
                                'LIVER_CIRRHOSIS' => 'Liver cirrhosis'
                            ] as $value => $label)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="known_immunosuppression[]" id="immuno_{{ $value }}" value="{{ $value }}" @checked(in_array($value, $immunosuppressionSelections, true))>
                                    <label class="form-check-label" for="immuno_{{ $value }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <h4 class="mt-3 mb-3">PRESENCE OF SIGN AND SYMPTOMS OF ACTIVE TUBERCULOSIS (सक्रिय तपेदिक के संकेत और लक्षणों की उपस्थिति)</h4>
                <div class="row">
                    @php
                        $activeTbFields = [
                            'fever' => '1. FEVER (बुखार)',
                            'cough_with_sputum_more_than_3_weeks' => '2. COUGH WITH SPUTUM PERSISTENT FOR MORE THAN 3 WEEKS (बलगम के साथ खांसी 3 सप्ताह से अधिक समय तक बनी रहना)',
                            'difficulty_in_breathing' => '3. DIFFICULTY IN BREATHING (सांस लेने में कठिनाई)',
                            'blood_in_sputum' => '4. BLOOD IN SPUTUM (थूक में खून)',
                            'weight_loss_with_fever' => '5. WEIGHT LOSS WITH FEVER (बुखार के साथ वजन घटना)',
                            'chest_pain' => '6. CHEST PAIN (छाती में दर्द)',
                            'blood_in_urine' => '7. BLOOD IN URINE (पेशाब में खून आना)',
                            'recurrent_diarrhea_loss_of_appetite_abdominal_distension_pain' => '8. RECURRENT DIARRHEA, LOSS OF APPETITE, ABDOMINAL DISTENSION AND PAIN (बार-बार दस्त होना, भूख ना लगना, पेट का फूलना और दर्द होना)',
                        ];
                    @endphp

                    @foreach($activeTbFields as $field => $label)
                        <div class="col-md-6 mb-3">
                            <label>{{ $label }}</label>
                            <select name="{{ $field }}" class="form-control">
                                <option value="">Select</option>
                                <option value="YES" @selected(old($field)==='YES')>YES</option>
                                <option value="NO" @selected(old($field)==='NO')>NO</option>
                            </select>
                        </div>
                    @endforeach
                    <div class="col-md-12 mb-3">
                        <div class="alert alert-warning mb-0">
                            REFER TO NEAREST DOTS CENTER IF 1+2+4 SYMPTOMS PRESENT<br>
                            1+2+4 लक्षणों की उपस्थिति पर स्क्रीनिंग अधिकारी द्वारा निकटतम डॉट्स केंद्र पर भेजा जाना चाहिए।
                        </div>
                    </div>
                </div>

                <h4 class="mt-3 mb-3">HISTORY OF ANTI TUBERCULAR TREATMENT (टीबी रोगी के पूर्व उपचार का विवरण)</h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>1. PREVIOUS TREATMENT OF TB (IF YES, DURATION OF ATT TAKEN)<br>टीबी का पूर्व उपचार (यदि हां, उपचार की अवधि)</label>
                        <textarea name="previous_treatment_of_tb_and_duration" class="form-control">{{ old('previous_treatment_of_tb_and_duration') }}</textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>2. HISTORY OF EXTRA PULMONARY TB (IF YES, MENTION DETAIL)<br>फेफड़े के अतिरिक्त शरीर के अन्य अंगों की टीबी का विवरण (यदि हाँ, तो विवरण)</label>
                        <textarea name="history_of_extra_pulmonary_tb_details" class="form-control">{{ old('history_of_extra_pulmonary_tb_details') }}</textarea>
                    </div>

                    @foreach([
                        'family_history_of_tb' => '3. FAMILY HISTORY OF TB (टीबी का पारिवारिक इतिहास)',
                        'contact_to_tb_patient' => '4. CONTACT TO TB PATIENT (टीबी के रोगी से संपर्क)',
                        'contact_to_mdr_tb_patient' => '5. CONTACT TO MDR-TB PATIENT (एमडीआर-टीबी रोगी से संपर्क)',
                        'history_of_incomplete_tb_treatment' => '6. HISTORY OF INCOMPLETE TB TREATMENT (अधूरे टीबी उपचार का इतिहास)',
                    ] as $field => $label)
                        <div class="col-md-6 mb-3">
                            <label>{{ $label }}</label>
                            <select name="{{ $field }}" class="form-control">
                                <option value="">Select</option>
                                <option value="YES" @selected(old($field)==='YES')>YES</option>
                                <option value="NO" @selected(old($field)==='NO')>NO</option>
                            </select>
                        </div>
                    @endforeach

                    <div class="col-md-6 mb-3">
                        <label>TYPE OF CASE (मामले का प्रकार)</label>
                        <select name="type_of_case" class="form-control">
                            <option value="">Select</option>
                            <option value="MDR" @selected(old('type_of_case')==='MDR')>MDR (एमडीआर)</option>
                            <option value="XDR" @selected(old('type_of_case')==='XDR')>XDR (एक्सडीआर)</option>
                            <option value="PRIMARY_CASE" @selected(old('type_of_case')==='PRIMARY_CASE')>PRIMARY CASE (प्राथमिक मामला)</option>
                        </select>
                    </div>
                </div>

                <h4 class="mt-3 mb-3">REMARKS & FEEDBACK (टिप्पणियां और प्रतिक्रिया)</h4>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label>ANY OTHER RELEVANT INFORMATION GIVEN BY THE SCREENING OFFICER<br>स्क्रीनिंग अधिकारियों द्वारा दी गई अन्य महत्वपूर्ण जानकारी</label>
                        <textarea name="other_relevant_information_by_screening_officer" class="form-control" rows="2">{{ old('other_relevant_information_by_screening_officer') }}</textarea>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label>REMARKS (टिप्पणियां) / PLEDGE</label>
                        <textarea name="remarks" class="form-control" rows="6">{{ old('remarks', 'I pledge that I will give my active co-operation to make "Bharat" Tuberculosis free. I will faithfully discharge my responsibilities towards the health of my family and my society. Even if someone around me is suffering from TB and is not taking treatment, then I will encourage him/her to take proper treatment and give every possible help to fight against tuberculosis. I promise that I will use mask and do not spit in public places in case if suffering from respiratory illness. I repeat my pledge to make "Bharat" free of Tuberculosis.

मैं ये प्रतिज्ञा करता / करती हूँ कि मैं भारत को टी.बी. मुक्त करने के लिये अपनी सक्रिय सहयोगिता दूंगा/ दूंगी। मैं अपने परिवार तथा अपने समाज के स्वास्थ्य के प्रति अपनी जिम्मेदारी का पूरी ईमानदारी से निर्वाहन करूंगा/ करूंगी। मैं अपने आस-पास भी यदि कोई व्यक्ति टी.बी. से ग्रस्त है तथा उपचार नहीं ले रहा है तो मैं उसको उचित उपचार लेने हेतु प्रोत्साहित तथा उचित सहायता प्रदान करूंगा/ करूंगी। मैं सार्वजनिक जगहों पर थूकना तथा श्वसन संबंधित व्याधि होने पर इसके बचाव हेतु मास्क का उपयोग करूंगा/ करूंगी। मैं भारत वर्ष को टीबी मुक्त करने के अपने वचन को दोहराता/ दोहराती हूँ।') }}</textarea>
                    </div>

                    <div class="col-md-12"><h5>Patient Feedback Form</h5></div>
                    <div class="col-md-6 mb-3">
                        <label>1. Do you know about latent tuberculosis before 2023?<br>क्या आपको 2023 से पहले लटेंट ट्यूबरक्लोसिस के बारे में जानकारी थी?</label>
                        <select name="aware_of_latent_tb_before_2023" class="form-control">
                            <option value="">Select</option>
                            <option value="Yes" @selected(old('aware_of_latent_tb_before_2023')==='Yes')>Yes (हां)</option>
                            <option value="No" @selected(old('aware_of_latent_tb_before_2023')==='No')>No (नहीं)</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>2. Do you now aware about Latent Tuberculosis?<br>क्या अब आपको लटेंट ट्यूबरक्लोसिस के बारे में जानकारी हैं?</label>
                        <select name="aware_of_latent_tb_now" class="form-control">
                            <option value="">Select</option>
                            <option value="Yes" @selected(old('aware_of_latent_tb_now')==='Yes')>Yes (हां)</option>
                            <option value="No" @selected(old('aware_of_latent_tb_now')==='No')>No (नहीं)</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>3. Where did you find information about latent tuberculosis?<br>आपको लटेंट ट्यूबरक्लोसिस के बारे में जानकारी कहां से मिली?</label>
                        <select name="source_of_information_on_latent_tb" class="form-control">
                            <option value="">Select</option>
                            <option value="AIIA_HOSPITAL" @selected(old('source_of_information_on_latent_tb')==='AIIA_HOSPITAL')>From AIIA Hospital (एआईआईए अस्पताल से)</option>
                            <option value="NTPC_D_010_AIIA" @selected(old('source_of_information_on_latent_tb')==='NTPC_D_010_AIIA')>From NTPC D-010-AIIA (एनटीपीसी डी-010-एआईआईए अस्पताल से)</option>
                            <option value="LTBI_SURVEY_CAMPAIGN_2025" @selected(old('source_of_information_on_latent_tb')==='LTBI_SURVEY_CAMPAIGN_2025')>During LTBI Survey / Campaign 2025 (एलटीबीआई सर्वेक्षण के दौरान / अभियान 2025 के दौरान)</option>
                            <option value="OTHERS" @selected(old('source_of_information_on_latent_tb')==='OTHERS')>Others (अन्य)</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>4. Are you aware about the ongoing PHI Project on Latent Tuberculosis funded by Ministry of Ayush at AIIA Hospital?<br>क्या आप एआईआईए अस्पताल में आयुष मंत्रालय द्वारा वित्त पोषित Latent Tuberculosis रोग पर चल रही पीएचआई परियोजना के बारे में जानते हैं?</label>
                        <select name="aware_of_ongoing_phi_project" class="form-control">
                            <option value="">Select</option>
                            <option value="Yes" @selected(old('aware_of_ongoing_phi_project')==='Yes')>Yes (हां)</option>
                            <option value="No" @selected(old('aware_of_ongoing_phi_project')==='No')>No (नहीं)</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>5. Are you satisfied with the provided information related to latent tuberculosis?<br>क्या आप गुप्त क्षय रोग से संबंधित दी गई जानकारी से संतुष्ट हैं?</label>
                        <select name="satisfied_with_information_provided" class="form-control">
                            <option value="">Select</option>
                            <option value="Yes" @selected(old('satisfied_with_information_provided')==='Yes')>Yes (हां)</option>
                            <option value="No" @selected(old('satisfied_with_information_provided')==='No')>No (नहीं)</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>PRINCIPAL INVESTIGATOR (प्रमुख अन्वेषक)</label>
                        <input type="text" name="principal_investigator" class="form-control" value="{{ old('principal_investigator') }}">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Investigator Name, Designation and Affiliation, Email ID (अन्वेषक का नाम, पदनाम और संबद्धता, ईमेल आईडी) <span class="text-danger">*</span></label>
                        <textarea name="investigator_name_designation_affiliation_email" class="form-control" rows="2" required>{{ old('investigator_name_designation_affiliation_email') }}</textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Submit Screening Form</button>
            </form>
        </div>
    </div>
</section>
@endsection
