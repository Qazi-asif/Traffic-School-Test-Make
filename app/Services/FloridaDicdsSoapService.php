<?php

namespace App\Services;

use App\Models\DicdsSubmissionLog;
use App\Models\UserCourseEnrollment;
use SoapClient;

class FloridaDicdsSoapService
{
    private $soapClient;

    private $wsdl = 'https://services.flhsmv.gov/DriverSchoolWebService/DriverSchoolWebService.asmx?WSDL';

    public function __construct()
    {
        $this->soapClient = new SoapClient($this->wsdl, [
            'trace' => 1,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
        ]);
    }

    public function submitCompletion(UserCourseEnrollment $enrollment)
    {
        $soapData = $this->buildSoapData($enrollment);

        try {
            $response = $this->soapClient->SubmitCourseCompletion($soapData);

            $log = DicdsSubmissionLog::create([
                'enrollment_id' => $enrollment->id,
                'soap_request' => $this->soapClient->__getLastRequest(),
                'soap_response' => $this->soapClient->__getLastResponse(),
                'certificate_number' => $response->CertificateNumber ?? null,
                'status_code' => $response->StatusCode,
                'status_message' => $response->StatusMessage,
                'submitted_at' => now(),
            ]);

            if ($response->StatusCode === 'CC000') {
                $enrollment->update([
                    'dicds_submission_status' => 'approved',
                    'dicds_certificate_number' => $response->CertificateNumber,
                    'dicds_response_data' => (array) $response,
                ]);
            }

            return $response;

        } catch (\Exception $e) {
            DicdsSubmissionLog::create([
                'enrollment_id' => $enrollment->id,
                'soap_request' => $this->soapClient->__getLastRequest(),
                'soap_response' => $e->getMessage(),
                'status_code' => 'ERROR',
                'status_message' => $e->getMessage(),
                'submitted_at' => now(),
            ]);

            throw $e;
        }
    }

    private function buildSoapData(UserCourseEnrollment $enrollment)
    {
        return [
            'mvUserid' => config('services.florida_dicds.username'),
            'mvPassword' => config('services.florida_dicds.password'),
            'mvSchoolid' => $enrollment->floridaSchool->school_id,
            'mvClassDate' => $enrollment->completion_date->format('Y-m-d'),
            'mvStartTime' => '09:00',
            'mvSchoolIns' => $enrollment->floridaInstructor->id,
            'mvSchoolCourse' => $enrollment->floridaCourse->dicds_course_id,
            'mvFirstName' => $enrollment->user->first_name,
            'mvLastName' => $enrollment->user->last_name,
            'mvSuffix' => $enrollment->user->suffix ?? '',
            'mvDob' => $enrollment->user->date_of_birth,
            'mvSex' => $enrollment->user->gender,
            'mvSocialSN' => $enrollment->user->ssn_last_four,
            'mvCitationDate' => $enrollment->citation_date,
            'mvCitationCounty' => $enrollment->citation_county,
            'mvCitationNumber' => $enrollment->citation_number,
            'mvReasonAttending' => $enrollment->reason_attending,
            'mvDriversLicense' => $enrollment->user->drivers_license_number,
            'mvdlStateOfRecordCode' => $enrollment->user->dl_state ?? 'FL',
            'mvAlienNumber' => $enrollment->alien_number ?? '',
            'mvNonAlien' => $enrollment->non_alien_number ?? '',
            'mvStreet' => $enrollment->user->address,
            'mvCity' => $enrollment->user->city,
            'mvState' => $enrollment->user->state,
            'mvZipCode' => $enrollment->user->zip_code,
        ];
    }
}
