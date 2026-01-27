<?php

namespace Database\Seeders;

use App\Models\ComplianceRule;
use App\Models\StateConfiguration;
use Illuminate\Database\Seeder;

class StateConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        // Florida Configuration
        $florida = StateConfiguration::create([
            'state_code' => 'FL',
            'state_name' => 'Florida',
            'submission_method' => 'api',
            'api_endpoint' => 'https://api.flhsmv.gov/certificates',
            'api_credentials' => json_encode([
                'client_id' => 'your_client_id',
                'client_secret' => 'your_client_secret',
                'token' => 'your_api_token',
            ]),
            'certificate_template' => 'florida_template',
            'is_active' => true,
        ]);

        // Florida Compliance Rules
        $floridaRules = [
            [
                'rule_type' => 'timing',
                'rule_name' => 'minimum_course_time',
                'rule_value' => '240',
                'description' => 'Minimum 240 minutes (4 hours) course time required',
                'is_required' => true,
            ],
            [
                'rule_type' => 'grading',
                'rule_name' => 'passing_score',
                'rule_value' => '80',
                'description' => 'Minimum 80% passing score on final exam',
                'is_required' => true,
            ],
            [
                'rule_type' => 'submission',
                'rule_name' => 'submission_deadline_5day',
                'rule_value' => '5',
                'description' => 'Submit within 5 days for 5-day election',
                'is_required' => true,
            ],
            [
                'rule_type' => 'submission',
                'rule_name' => 'submission_deadline_3day',
                'rule_value' => '3',
                'description' => 'Submit within 3 days for 3-day election',
                'is_required' => true,
            ],
            [
                'rule_type' => 'content',
                'rule_name' => 'required_topics',
                'rule_value' => 'traffic_laws,defensive_driving,substance_abuse',
                'description' => 'Required course topics for Florida compliance',
                'is_required' => true,
            ],
        ];

        foreach ($floridaRules as $rule) {
            ComplianceRule::create(array_merge($rule, ['state_config_id' => $florida->id]));
        }

        // California Configuration
        $california = StateConfiguration::create([
            'state_code' => 'CA',
            'state_name' => 'California',
            'submission_method' => 'portal',
            'portal_url' => 'https://portal.dmv.ca.gov',
            'portal_credentials' => json_encode([
                'username' => 'your_username',
                'password' => 'your_password',
            ]),
            'certificate_template' => 'california_template',
            'is_active' => true,
        ]);

        // California Compliance Rules
        $californiaRules = [
            [
                'rule_type' => 'timing',
                'rule_name' => 'minimum_course_time',
                'rule_value' => '480',
                'description' => 'Minimum 480 minutes (8 hours) course time required',
                'is_required' => true,
            ],
            [
                'rule_type' => 'grading',
                'rule_name' => 'passing_score',
                'rule_value' => '83',
                'description' => 'Minimum 83% passing score on final exam',
                'is_required' => true,
            ],
        ];

        foreach ($californiaRules as $rule) {
            ComplianceRule::create(array_merge($rule, ['state_config_id' => $california->id]));
        }

        // Texas Configuration
        $texas = StateConfiguration::create([
            'state_code' => 'TX',
            'state_name' => 'Texas',
            'submission_method' => 'email',
            'email_recipient' => 'certificates@txdps.state.tx.us',
            'certificate_template' => 'texas_template',
            'is_active' => true,
        ]);

        // Texas Compliance Rules
        $texasRules = [
            [
                'rule_type' => 'timing',
                'rule_name' => 'minimum_course_time',
                'rule_value' => '360',
                'description' => 'Minimum 360 minutes (6 hours) course time required',
                'is_required' => true,
            ],
            [
                'rule_type' => 'grading',
                'rule_name' => 'passing_score',
                'rule_value' => '70',
                'description' => 'Minimum 70% passing score on final exam',
                'is_required' => true,
            ],
        ];

        foreach ($texasRules as $rule) {
            ComplianceRule::create(array_merge($rule, ['state_config_id' => $texas->id]));
        }
    }
}
