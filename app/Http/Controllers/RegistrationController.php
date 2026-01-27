<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class RegistrationController extends Controller
{
    public function showStep(Request $request, $step = 1)
    {
        // Regenerate CSRF token for step 1 to handle external redirects
        if ($step == 1 || ! $step) {
            $request->session()->regenerateToken();
        }

        $step = (int) $step;
        if ($step < 1 || $step > 4) {
            $step = 1;
        }

        // Validate that previous steps are completed before allowing access to current step
        if ($step > 1) {
            // Check if previous step data exists
            for ($i = 1; $i < $step; $i++) {
                if (! session()->has('registration_step_'.$i)) {
                    // Previous step not completed, redirect to step 1
                    return redirect()->route('register.step', 1)
                        ->with('error', 'Please complete the registration steps in order.');
                }
            }
        }

        // Store course enrollment parameters in session if provided (only on step 1)
        if ($step === 1 && $request->has('course_id') && $request->has('course_enroll')) {
            $region = $request->input('region', 'florida');
            $table = strtolower($region) === 'missouri' ? 'courses' : 'florida_courses';

            $enrollmentData = [
                'course_id' => $request->course_id,
                'course_enroll' => $request->course_enroll === 'true',
                'region' => $region,
                'table' => $table,
            ];

            session(['pending_course_enrollment' => $enrollmentData]);

            \Log::info('Pending course enrollment stored in session', $enrollmentData);
        }

        return view('registration.step'.$step, compact('step'));
    }

    public function processStep(Request $request, $step)
    {
        $step = (int) $step;
        
        \Log::info('=== processStep START ===', ['step' => $step]);
        \Log::info('Request data: ' . json_encode($request->all()));

        try {
            // Validate based on step
            $validatedData = $this->validateStep($request, $step);
            \Log::info('Validation passed for step ' . $step);
            \Log::info('Validated data: ' . json_encode($validatedData));

            // Store step data in session
            $sessionKey = 'registration_step_'.$step;
            session([$sessionKey => $validatedData]);
            \Log::info('Session data stored: ' . $sessionKey);

            // Move to next step or complete registration
            if ($step < 4) {
                \Log::info('Redirecting to step ' . ($step + 1));
                return redirect()->route('register.step', $step + 1);
            }

            // Complete registration (step 4)
            \Log::info('Completing registration');
            return $this->completeRegistration();
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('=== Validation Error ===', [
                'step' => $step,
                'errors' => $e->errors(),
                'input_data' => $request->all()
            ]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('=== processStep ERROR ===', [
                'step' => $step,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function validateStep(Request $request, $step)
    {
        switch ($step) {
            case 1:
                return $request->validate([
                    'first_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\-\']+$/'],
                    'last_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\-\']+$/'],
                    'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                    'email_confirmation' => ['required', 'string', 'email', 'same:email'],
                    'password' => [
                        'required',
                        'string',
                        'min:8',
                        'regex:/[a-z]/',      // must contain at least one lowercase letter
                        'regex:/[A-Z]/',      // must contain at least one uppercase letter
                        'regex:/[0-9]/',      // must contain at least one digit
                        'regex:/[@$!%*#?&()]/', // must contain a special character
                    ],
                    'password_confirmation' => ['required', 'string', 'same:password'],
                ], [
                    'first_name.regex' => 'First name can only contain letters, spaces, hyphens, and apostrophes.',
                    'last_name.regex' => 'Last name can only contain letters, spaces, hyphens, and apostrophes.',
                    'email.unique' => 'This email is already registered.',
                    'email_confirmation.same' => 'Email addresses must match.',
                    'password.min' => 'Password must be at least 8 characters long.',
                    'password.regex' => 'Password must contain uppercase, lowercase, number, and special character (!@#$&*()).',
                    'password_confirmation.same' => 'Passwords must match.',
                ]);

            case 2:
                $rules = [
                    'mailing_address' => ['required', 'string', 'max:500'],
                    'city' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\-\']+$/'],
                    'state' => ['required', 'string', 'max:100'],
                    'zip' => ['required', 'string', 'regex:/^\d{5}(-\d{4})?$/', 'max:10'],
                    'phone_1' => ['required', 'string', 'regex:/^\d{3}$/', 'size:3'],
                    'phone_2' => ['required', 'string', 'regex:/^\d{3}$/', 'size:3'],
                    'phone_3' => ['required', 'string', 'regex:/^\d{4}$/', 'size:4'],
                    'gender' => ['required', 'string', 'in:male,female'],
                    'birth_month' => ['required', 'integer', 'between:1,12'],
                    'birth_day' => ['required', 'integer', 'between:1,31'],
                    'birth_year' => ['required', 'integer', 'between:1950,'.date('Y')],
                    'driver_license' => ['required', 'string', 'max:50'],
                    'license_state' => ['required', 'string', 'max:50'],
                    'license_class' => ['required', 'string', 'max:50'],
                    'insurance_discount_only' => ['nullable', 'boolean'],
                ];

                $messages = [
                    'city.regex' => 'City name can only contain letters, spaces, hyphens, and apostrophes.',
                    'zip.regex' => 'Zip code must be 5 digits or 5+4 format (e.g., 12345 or 12345-6789).',
                    'phone_1.regex' => 'First part of phone must be exactly 3 digits.',
                    'phone_2.regex' => 'Second part of phone must be exactly 3 digits.',
                    'phone_3.regex' => 'Third part of phone must be exactly 4 digits.',
                    'birth_month.between' => 'Birth month must be between 1 and 12.',
                    'birth_day.between' => 'Birth day must be between 1 and 31.',
                    'birth_year.between' => 'Birth year must be between 1950 and current year.',
                    'due_month.between' => 'Due date month must be between 1 and 12.',
                    'due_day.between' => 'Due date day must be between 1 and 31.',
                    'due_year.between' => 'Due date year must be current year or within next 2 years.',
                ];

                // Only require court fields if insurance discount is not selected
                if (!$request->has('insurance_discount_only') || !$request->input('insurance_discount_only')) {
                    $rules['court_selected'] = ['required', 'string', 'max:500'];
                    $rules['citation_number'] = ['required', 'string', 'max:100'];
                    $rules['due_month'] = ['required', 'integer', 'between:1,12'];
                    $rules['due_day'] = ['required', 'integer', 'between:1,31'];
                    $rules['due_year'] = ['required', 'integer', 'between:'.date('Y').','.(date('Y') + 2)];
                }

                return $request->validate($rules, $messages);

            case 3:
                return $request->validate([
                    'q1' => ['required', 'string', 'regex:/^[0-9]{4}$/', 'size:4'],
                    'q2' => ['required', 'string', 'regex:/^[0-9]+$/', 'max:10'],
                    'q3' => ['required', 'string', 'regex:/^[0-9]+$/', 'max:5'],
                    'q4' => ['required', 'string', 'regex:/^[0-9]{4}$/', 'size:4'],
                    'q5' => ['required', 'string', 'regex:/^[0-9]+$/', 'max:3'],
                    'q6' => ['required', 'string', 'regex:/^[0-9]+$/', 'max:3'],
                    'q7' => ['required', 'string', 'regex:/^[0-9]{5}$/', 'size:5'],
                    'q8' => ['required', 'string', 'regex:/^[0-9]{4}$/', 'size:4'],
                    'q9' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z\s\'-]+$/'],
                    'q10' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\'-]+$/'],
                ], [
                    'q1.regex' => 'License expiration year must be exactly 4 digits (e.g., 2025).',
                    'q2.regex' => 'Weight must be numbers only (e.g., 162).',
                    'q3.regex' => 'Number of cars must be numbers only (e.g., 1).',
                    'q4.regex' => 'Last four digits must be exactly 4 digits (e.g., 6374).',
                    'q5.regex' => 'Age must be numbers only (e.g., 31).',
                    'q6.regex' => 'Age when got license must be numbers only (e.g., 16).',
                    'q7.regex' => 'Zip code must be exactly 5 digits (e.g., 90210).',
                    'q8.regex' => 'Birth year must be exactly 4 digits (e.g., 1980).',
                    'q9.regex' => 'Hair color can only contain letters, spaces, hyphens, and apostrophes.',
                    'q10.regex' => 'City name can only contain letters, spaces, hyphens, and apostrophes.',
                ]);

            case 4:
                return $request->validate([
                    'agreement_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\-\']+$/'],
                    'terms_agreement' => ['required', 'accepted'],
                ], [
                    'agreement_name.regex' => 'Name can only contain letters, spaces, hyphens, and apostrophes.',
                    'terms_agreement.accepted' => 'You must agree to the terms and conditions.',
                ]);

            default:
                return [];
        }
    }

    private function completeRegistration()
    {
        // Get all step data from session
        $step1 = session('registration_step_1', []);
        $step2 = session('registration_step_2', []);
        $step3 = session('registration_step_3', []);
        $step4 = session('registration_step_4', []);

        // Validate that all required steps are completed
        if (empty($step1) || ! isset($step1['first_name']) || ! isset($step1['email'])) {
            return redirect()->route('register.step', 1)
                ->with('error', 'Please complete step 1 first.');
        }

        // Create user with all registration data
        $user = User::create([
            // Step 1 - Basic Info
            'first_name' => $step1['first_name'],
            'last_name' => $step1['last_name'],
            'email' => $step1['email'],
            'password' => bcrypt($step1['password']),
            'role_id' => 4, // Student role
            'status' => 'active',

            // Step 2 - Personal Info (existing fields)
            'mailing_address' => $step2['mailing_address'] ?? null,
            'city' => $step2['city'] ?? null,
            'state' => $step2['state'] ?? null,
            'zip' => $step2['zip'] ?? null,
            'phone_1' => $step2['phone_1'] ?? null,
            'phone_2' => $step2['phone_2'] ?? null,
            'phone_3' => $step2['phone_3'] ?? null,
            'gender' => $step2['gender'] ?? null,
            'birth_month' => $step2['birth_month'] ?? null,
            'birth_day' => $step2['birth_day'] ?? null,
            'birth_year' => $step2['birth_year'] ?? null,
            'driver_license' => $step2['driver_license'] ?? null,

            // Step 2 - New fields
            'license_state' => $step2['license_state'] ?? null,
            'license_class' => $step2['license_class'] ?? null,
            'insurance_discount_only' => isset($step2['insurance_discount_only']) ? (bool)$step2['insurance_discount_only'] : false,
            
            // Court information (only if not insurance discount only)
            'court_selected' => (!isset($step2['insurance_discount_only']) || !$step2['insurance_discount_only']) ? ($step2['court_selected'] ?? null) : null,
            'citation_number' => (!isset($step2['insurance_discount_only']) || !$step2['insurance_discount_only']) ? ($step2['citation_number'] ?? null) : null,
            'due_month' => (!isset($step2['insurance_discount_only']) || !$step2['insurance_discount_only']) ? ($step2['due_month'] ?? null) : null,
            'due_day' => (!isset($step2['insurance_discount_only']) || !$step2['insurance_discount_only']) ? ($step2['due_day'] ?? null) : null,
            'due_year' => (!isset($step2['insurance_discount_only']) || !$step2['insurance_discount_only']) ? ($step2['due_year'] ?? null) : null,

            // Step 3 - Security Questions
            'security_q1' => $step3['q1'] ?? null,
            'security_q2' => $step3['q2'] ?? null,
            'security_q3' => $step3['q3'] ?? null,
            'security_q4' => $step3['q4'] ?? null,
            'security_q5' => $step3['q5'] ?? null,
            'security_q6' => $step3['q6'] ?? null,
            'security_q7' => $step3['q7'] ?? null,
            'security_q8' => $step3['q8'] ?? null,
            'security_q9' => $step3['q9'] ?? null,
            'security_q10' => $step3['q10'] ?? null,

            // Step 4 - Agreement
            'agreement_name' => $step4['agreement_name'] ?? null,
            'terms_agreement' => isset($step4['terms_agreement']),
            'registration_completed_at' => now(),
        ]);

        // Send welcome email
        Mail::to($user->email)->send(new WelcomeMail($user));

        // Record user consent if terms were agreed
        if (isset($step4['terms_agreement']) && $step4['terms_agreement']) {
            \App\Models\UserLegalConsent::create([
                'user_id' => $user->id,
                'document_id' => 1, // Terms & Conditions document ID
                'agreed_at' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        // Check if there's a pending course enrollment
        $pendingEnrollment = session('pending_course_enrollment');

        \Log::info('Checking pending enrollment', ['pending' => $pendingEnrollment]);

        if ($pendingEnrollment && isset($pendingEnrollment['course_enroll']) && $pendingEnrollment['course_enroll']) {
            // Log the user in
            auth()->login($user);

            \Log::info('User logged in, redirecting to payment', [
                'user_id' => $user->id,
                'course_id' => $pendingEnrollment['course_id'],
                'table' => $pendingEnrollment['table'],
            ]);

            // Clear registration session data
            session()->forget(['registration_step_1', 'registration_step_2', 'registration_step_3', 'registration_step_4']);

            // Redirect to payment page with course ID and table
            return redirect()->route('payment.show', [
                'course_id' => $pendingEnrollment['course_id'],
                'table' => $pendingEnrollment['table'],
            ])->with('success', 'Registration completed! Please complete payment to enroll in the course.');
        }

        // Clear session data
        session()->forget(['registration_step_1', 'registration_step_2', 'registration_step_3', 'registration_step_4', 'pending_course_enrollment']);

        return redirect()->route('login')->with('success', 'Registration completed successfully! Please login with your credentials.');
    }
}
