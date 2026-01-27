<?php

namespace Database\Seeders;

use App\Models\TransmissionErrorCode;
use Illuminate\Database\Seeder;

class TransmissionErrorCodeSeeder extends Seeder
{
    public function run(): void
    {
        $errorCodes = [
            // Florida Error Codes
            [
                'state' => 'FL',
                'error_code' => 'VALIDATION_ERROR',
                'error_category' => 'Validation',
                'technical_message' => 'One or more required fields are missing or invalid',
                'user_friendly_message' => 'Student information is incomplete. Please verify all required fields are filled correctly.',
                'resolution_steps' => 'Check driver license number, citation number, and court case number. Ensure all fields are properly formatted.',
                'is_retryable' => false,
            ],
            [
                'state' => 'FL',
                'error_code' => '400',
                'error_category' => 'Bad Request',
                'technical_message' => 'Invalid request format or parameters',
                'user_friendly_message' => 'The transmission data format is incorrect. This may require technical support.',
                'resolution_steps' => 'Review the payload structure. Contact technical support if the issue persists.',
                'is_retryable' => false,
            ],
            [
                'state' => 'FL',
                'error_code' => '401',
                'error_category' => 'Authentication',
                'technical_message' => 'Authentication failed - invalid credentials',
                'user_friendly_message' => 'Unable to authenticate with Florida DICDS. Please contact system administrator.',
                'resolution_steps' => 'Verify API credentials in environment configuration. Check FLORIDA_API_KEY, FLORIDA_USERNAME, and FLORIDA_PASSWORD.',
                'is_retryable' => false,
            ],
            [
                'state' => 'FL',
                'error_code' => '403',
                'error_category' => 'Authorization',
                'technical_message' => 'Access forbidden - insufficient permissions',
                'user_friendly_message' => 'Your school does not have permission to submit this type of data.',
                'resolution_steps' => 'Contact Florida DHSMV to verify school permissions and API access level.',
                'is_retryable' => false,
            ],
            [
                'state' => 'FL',
                'error_code' => '404',
                'error_category' => 'Not Found',
                'technical_message' => 'Resource not found',
                'user_friendly_message' => 'The requested resource could not be found. The API endpoint may have changed.',
                'resolution_steps' => 'Verify FLORIDA_API_URL is correct. Contact technical support.',
                'is_retryable' => false,
            ],
            [
                'state' => 'FL',
                'error_code' => '422',
                'error_category' => 'Validation',
                'technical_message' => 'Unprocessable entity - validation failed',
                'user_friendly_message' => 'Student data failed validation. Please check driver license number and citation details.',
                'resolution_steps' => 'Verify driver license format, citation number validity, and court case number. Ensure student has not already been reported.',
                'is_retryable' => false,
            ],
            [
                'state' => 'FL',
                'error_code' => '429',
                'error_category' => 'Rate Limit',
                'technical_message' => 'Too many requests - rate limit exceeded',
                'user_friendly_message' => 'Too many transmissions sent in a short time. The system will retry automatically.',
                'resolution_steps' => 'Wait before retrying. Consider implementing request throttling.',
                'is_retryable' => true,
            ],
            [
                'state' => 'FL',
                'error_code' => '500',
                'error_category' => 'Server Error',
                'technical_message' => 'Internal server error on Florida API',
                'user_friendly_message' => 'Florida DICDS system is experiencing technical difficulties. The system will retry automatically.',
                'resolution_steps' => 'Wait and retry. If the issue persists, contact Florida DHSMV technical support.',
                'is_retryable' => true,
            ],
            [
                'state' => 'FL',
                'error_code' => '502',
                'error_category' => 'Gateway Error',
                'technical_message' => 'Bad gateway - upstream server error',
                'user_friendly_message' => 'Connection to Florida DICDS failed. The system will retry automatically.',
                'resolution_steps' => 'This is usually temporary. The job will retry automatically.',
                'is_retryable' => true,
            ],
            [
                'state' => 'FL',
                'error_code' => '503',
                'error_category' => 'Service Unavailable',
                'technical_message' => 'Service temporarily unavailable',
                'user_friendly_message' => 'Florida DICDS is temporarily unavailable. The system will retry automatically.',
                'resolution_steps' => 'Wait for service to become available. Check Florida DHSMV status page.',
                'is_retryable' => true,
            ],
            [
                'state' => 'FL',
                'error_code' => '504',
                'error_category' => 'Timeout',
                'technical_message' => 'Gateway timeout - request took too long',
                'user_friendly_message' => 'The request timed out. The system will retry automatically.',
                'resolution_steps' => 'Increase FLORIDA_API_TIMEOUT if this happens frequently.',
                'is_retryable' => true,
            ],
            [
                'state' => 'FL',
                'error_code' => 'DUPLICATE_SUBMISSION',
                'error_category' => 'Duplicate',
                'technical_message' => 'This student completion has already been submitted',
                'user_friendly_message' => 'This course completion has already been reported to Florida DICDS.',
                'resolution_steps' => 'Verify if a previous submission was successful. No action needed if already reported.',
                'is_retryable' => false,
            ],
            [
                'state' => 'FL',
                'error_code' => 'INVALID_LICENSE',
                'error_category' => 'Validation',
                'technical_message' => 'Driver license number not found or invalid',
                'user_friendly_message' => 'The driver license number is not valid or not found in Florida records.',
                'resolution_steps' => 'Verify the driver license number with the student. Update enrollment record with correct license number.',
                'is_retryable' => false,
            ],
            [
                'state' => 'FL',
                'error_code' => 'INVALID_CITATION',
                'error_category' => 'Validation',
                'technical_message' => 'Citation number not found or invalid',
                'user_friendly_message' => 'The citation number is not valid or not found in court records.',
                'resolution_steps' => 'Verify the citation number with the student. Update enrollment record with correct citation number.',
                'is_retryable' => false,
            ],
            [
                'state' => 'FL',
                'error_code' => 'EXCEPTION',
                'error_category' => 'System Error',
                'technical_message' => 'Unexpected exception occurred during transmission',
                'user_friendly_message' => 'An unexpected error occurred. Technical support has been notified.',
                'resolution_steps' => 'Review error logs for details. Contact technical support.',
                'is_retryable' => true,
            ],
            [
                'state' => 'FL',
                'error_code' => 'JOB_FAILED',
                'error_category' => 'System Error',
                'technical_message' => 'Job failed after all retry attempts',
                'user_friendly_message' => 'The transmission failed after multiple attempts. Manual intervention required.',
                'resolution_steps' => 'Review the error details and resolve the underlying issue before retrying.',
                'is_retryable' => false,
            ],
        ];

        foreach ($errorCodes as $errorCode) {
            TransmissionErrorCode::updateOrCreate(
                [
                    'state' => $errorCode['state'],
                    'error_code' => $errorCode['error_code'],
                ],
                $errorCode
            );
        }

        $this->command->info('Transmission error codes seeded successfully.');
    }
}
