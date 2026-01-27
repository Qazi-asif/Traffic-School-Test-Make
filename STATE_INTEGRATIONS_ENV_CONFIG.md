# State Integrations - Environment Configuration

## Complete .env Configuration

Based on the actual Java implementation from your old system, here are the exact credentials and endpoints:

```env
# ============================================
# STATE TRANSMISSION CONFIGURATION
# ============================================

# Execution Mode (true = synchronous, false = queued)
STATE_TRANSMISSION_SYNC=true

# ============================================
# CALIFORNIA TVCC (Traffic Violator Certificate Completion)
# ============================================
CALIFORNIA_TVCC_ENABLED=true
CALIFORNIA_TVCC_URL=https://xsg.dmv.ca.gov/tvcc/tvccservice
CALIFORNIA_TVCC_USER=Support@dummiestrafficschool.com
# Password stored in database table: tvcc_passwords

# ============================================
# CALIFORNIA CTSI (California Traffic School Interface)
# ============================================
CALIFORNIA_CTSI_ENABLED=true
# CTSI is callback-only, receives XML POST from CTSI system

# ============================================
# FLORIDA FLHSMV/DICDS (Multiple Schools)
# ============================================

# School 1 Credentials (Primary)
FLORIDA_SCHOOL1_USERNAME=NMNSEdits
FLORIDA_SCHOOL1_PASSWORD=LoveFL2025!
FLORIDA_SCHOOL1_INSTRUCTOR=76397
FLORIDA_SCHOOL1_ID=30981
FLORIDA_SCHOOL1_COURSE=40585

# School 2 Credentials (Secondary)
FLORIDA_SCHOOL2_USERNAME=KatMor1IES#2
FLORIDA_SCHOOL2_PASSWORD=Ies2024!
FLORIDA_SCHOOL2_INSTRUCTOR=75005
FLORIDA_SCHOOL2_ID=27243
FLORIDA_SCHOOL2_COURSE=36349

# Florida API Endpoint
FLORIDA_DICDS_WSDL=https://services.flhsmv.gov/DriverSchoolWebService/DriverSchoolWebService.asmx?WSDL
FLORIDA_API_TIMEOUT=30

# ============================================
# NEVADA NTSA (Nevada Traffic Safety Association)
# ============================================
NEVADA_NTSA_ENABLED=true
NEVADA_NTSA_URL=https://secure.ntsa.us/cgi-bin/register.cgi
NEVADA_NTSA_SCHOOL_NAME="DUMMIES TRAFFIC SCHOOL.COM"
NEVADA_NTSA_TEST_NAME="DUMMIES TRAFFIC SCHOOL.COM - CA"
NEVADA_NTSA_ENCRYPT=0
NEVADA_NTSA_DEMO=0

# ============================================
# CCS (Court Compliance System)
# ============================================
CCS_ENABLED=true
CCS_URL=http://testingprovider.com/ccs/register.jsp
CCS_SCHOOL_NAME=dummiests
CCS_RESULT_URL=${APP_URL}/api/ccs/result
```

## API Authentication Details

### 1. California TVCC
**From**: `Client.java` line 145-146
```java
obj1.setPassword(tvcc_password);  // From database
obj1.setUserId("Support@dummiestrafficschool.com");
```

**Authentication Method**: UserDto object with userId and password
- User ID: `Support@dummiestrafficschool.com` (hardcoded)
- Password: Retrieved from `tvcc_password` table (dynamic)

**Endpoint**: `https://xsg.dmv.ca.gov/tvcc/tvccservice`

**Request Format**: SOAP/XML with CourseCompletionAddRequest object

### 2. Florida FLHSMV

**From**: `WebServiceClient.java` - Multiple school configurations

**School 1** (used in `sendfltvcc.jsp`):
```java
Username: "NMNSEdits"
Password: "LoveFL2025!"
School Instructor: "76397"
School ID: "30981"
School Course: "40585"
```

**School 2** (used in `sendfltvcclist.jsp`, `resendfltvcc.jsp`):
```java
Username: "KatMor1IES#2"
Password: "Ies2024!"
School Instructor: "75005"
School ID: "27243"
School Course: "36349"
```

**Authentication Method**: Username/Password passed as parameters to `wsVerifyData()` method

**All Parameters Sent** (from line 234):
```java
port.wsVerifyData(
    mvUserid,           // Username
    mvPassword,         // Password
    mvSchoolid,         // School ID
    mvClassDate,        // Format: MMddYYYY
    mvStartTime,        // Format: HHmm
    mvSchoolIns,        // School Instructor ID
    mvSchoolCourse,     // School Course ID
    mvFirstName,
    mvMiddleName,
    mvLastName,
    mvSuffix,
    mvDob,              // Format: MMddYYYY
    mvSex,
    mvSocialSN,
    mvCitationDate,
    mvCitationCounty,
    mvCitationNumber,
    mvReasonAttending,
    mvDriversLicense,
    mvdlStateOfRecordCode,
    mvAlienNumber,
    mvNonAlien,
    mvStreet,
    mvApartment,
    mvCity,
    mvState,
    mvZipCode,
    mvZipPlus,
    mvPhone,
    mvEmail
)
```

### 3. Nevada NTSA
**From**: `ntsaredirect.jsp`

**Authentication**: None (public form submission)

**Fixed Parameters**:
- School: "DUMMIES TRAFFIC SCHOOL.COM"
- TestName: "DUMMIES TRAFFIC SCHOOL.COM - CA"
- Encrypt: "0"
- Demo: "0"

**Endpoint**: `https://secure.ntsa.us/cgi-bin/register.cgi`

**Method**: HTTP POST form auto-submit

### 4. CCS (Court Compliance System)
**From**: `ccsredirect.jsp`

**Authentication**: None (public form submission)

**Fixed Parameters**:
- StudentSchoolName: "dummiests"

**Endpoint**: `http://testingprovider.com/ccs/register.jsp`

**Method**: HTTP POST form auto-submit

## Database Password Storage

### TVCC Password
**From**: `Client.java` line 119-128
```java
PreparedStatement pstmt1 = conn.prepareStatement("select tvcc_password from tvcc_password");
ResultSet rst1 = pstmt1.executeQuery();

if(rst1.next()){
    tvcc_password = rst1.getString("tvcc_password");		  
}
```

**Storage**: Plain text in `tvcc_password` table
**Usage**: Retrieved dynamically for each TVCC API call

## Date Format Requirements

### California TVCC
- Completion Date: `yyyy-MM-dd` (e.g., "2025-12-09")
- Date of Birth: `yyyy-MM-dd` (e.g., "1990-01-15")

### Florida FLHSMV
- Class Date: `MMddYYYY` (e.g., "12092025")
- Start Time: `HHmm` (e.g., "1430" for 2:30 PM)
- Date of Birth: `MMddYYYY` (e.g., "01151990")

### Nevada NTSA
- DOB: `yyyy-MM-dd` (e.g., "1990-01-15")
- Due Date: `yyyy-MM-dd` (e.g., "2025-12-31")

### CCS
- Birthday: `MM/dd/yyyy` (e.g., "01/15/1990")
- Due Date: `MM/dd/yyyy` (e.g., "12/31/2025")
- Sign Up Date: `MM/dd/yyyy` (e.g., "12/09/2025")

## Response Handling

### California TVCC Success Response
```java
rs.getCcSeqNbr()      // Certificate sequence number
rs.getCcStatCd()      // Status code
rs.getCcSubTstamp()   // Submission timestamp
```

### Florida FLHSMV Success Codes
From `WebServiceClient.java` line 247:
- Success codes: `VL000`, `ST000-ST005`, `VC000-VC003`, `VI000`, `VS000`, `VS010`
- Error codes: Looked up in `fl_error` table

### Nevada NTSA Callback
Receives: `percentage`, `testDate`, `certificateSentDate`

### CCS Callback
Receives: `Status`, `Percentage`, `TestDate`, `CertificateSentDate`

## SSL/TLS Configuration

### California TVCC
**From**: `Client.java` line 35-42
```java
SSLContext ctx = SSLContext.getInstance("TLS");
ctx.init(null, new TrustManager[] { new BlindTrustManager() }, null);
```
Uses custom SSL trust manager (accepts all certificates)

## Implementation Notes

1. **TVCC Password**: Must be set in database before use
   ```bash
   php artisan tvcc:password
   ```

2. **Florida Multiple Schools**: System supports multiple school credentials
   - Use School 1 for primary transmissions
   - Use School 2 for list/batch transmissions

3. **Court Codes**: Must be configured in `courts` table
   - `tvcc_court_code` for California TVCC
   - `ctsi_court_id` for California CTSI
   - `ntsa_court_name` for Nevada NTSA

4. **Timezone Handling**: Florida uses timezone conversion
   ```java
   CONVERT_TZ(finish_date, '+00:00', '-07:00')
   ```

5. **Email Handling**: Florida splits email on `@@` separator
   ```java
   temp = (rst.getString("vsemailaddress")).split("@@");
   mvEmail = temp[0];
   ```

## Security Considerations

⚠️ **IMPORTANT**: The credentials shown above are from your old system. For production:

1. Verify all credentials are still valid
2. Consider encrypting sensitive credentials
3. Use environment-specific credentials (dev/staging/prod)
4. Rotate passwords regularly
5. Monitor API access logs
6. Implement rate limiting
7. Use HTTPS for all API calls

## Testing Checklist

- [ ] Verify TVCC password is set in database
- [ ] Test California TVCC with sample data
- [ ] Test Florida School 1 credentials
- [ ] Test Florida School 2 credentials
- [ ] Verify Nevada NTSA callback URL is accessible
- [ ] Verify CCS callback URL is accessible
- [ ] Test all date format conversions
- [ ] Verify court code mappings
- [ ] Test error handling for each system
- [ ] Monitor logs for successful transmissions
