# State Integrations Roadmap

## Overview

This document outlines all state certificate submission integrations for the traffic school system, based on the old Java system.

---

## ✅ Completed Integrations

### 1. Florida FLHSMV/DICDS ✅
- **Status**: Production Ready
- **Type**: SOAP API
- **Features**: Automatic submission, retry logic, admin interface
- **Location**: `/admin/fl-transmissions`

### 2. California TVCC ✅
- **Status**: Ready for Testing
- **Type**: SOAP API
- **Features**: Automatic submission, retry logic, admin interface
- **Location**: `/admin/ca-transmissions`

---

## ⏳ Pending Integrations

### 3. California CTSI (Traffic School Interface)
**Priority**: HIGH (complements CA TVCC)

**Type**: XML Callback System

**How It Works**:
- Student registers through CTSI portal
- CTSI sends XML POST to result URL
- System parses XML and stores results
- Updates student completion status

**Implementation Needed**:
- Result URL endpoint: `/api/ctsi/result`
- XML parser service
- CTSI result model and table
- Court code mapping

**Old System Files**:
- `ctsiresulturl.jsp` - Receives XML POST
- `com.dummies.payment.merchantprocessor.parseCTSIXML()` - Parser

**Database Table**: `ctsi_results`
```sql
- id
- enrollment_id
- key_response (status key)
- save_data (message)
- process_date
- raw_xml
```

**Complexity**: Medium
**Estimated Time**: 4-6 hours

---

### 4. Nevada NTSA (Traffic Safety Association)
**Priority**: MEDIUM

**Type**: Redirect-Based Registration

**How It Works**:
- Student completes course
- System redirects to NTSA with form data
- NTSA processes and sends callback
- System receives completion confirmation

**Implementation Needed**:
- Redirect service
- Form auto-submit page
- Result URL endpoint: `/api/ntsa/result`
- NTSA record model and table

**Old System Files**:
- `ntsaredirect.jsp` - Auto-submit form
- `resultsurl.jsp` - Receives callback

**API Endpoint**: `https://secure.ntsa.us/cgi-bin/register.cgi`

**Request Parameters**:
```php
[
    'Name' => 'John Doe',
    'Email' => 'john@example.com',
    'License' => 'D1234567',
    'DOB' => '1990-01-15',
    'Court' => 'Las Vegas Municipal Court',
    'CaseNum' => 'ABC123456',
    'DueDate' => '2025-12-31',
    'School' => 'DUMMIES TRAFFIC SCHOOL.COM',
    'TestName' => 'DUMMIES TRAFFIC SCHOOL.COM - CA',
    'Telephone' => '555-1234',
    'UniqueID' => '12345',
    'Encrypt' => '0',
    'Demo' => '0',
    'Language' => 'English'
]
```

**Database Table**: `ntsa_records`
```sql
- id
- enrollment_id
- percentage
- test_date
- certificate_sent_date
- status
```

**Complexity**: Medium
**Estimated Time**: 4-6 hours

---

### 5. CCS (Court Compliance System)
**Priority**: LOW (multi-state, less common)

**Type**: Redirect-Based Registration

**How It Works**:
- Similar to NTSA
- Student redirects to CCS portal
- CCS processes and sends callback
- System updates completion status

**Implementation Needed**:
- Redirect service
- Form auto-submit page
- Result URL endpoint: `/api/ccs/result`
- CCS record model and table

**Old System Files**:
- `ccsredirect.jsp` - Auto-submit form
- `ccsresultsurl.jsp` - Receives callback

**API Endpoint**: `http://testingprovider.com/ccs/register.jsp`

**Request Parameters**:
```php
[
    'StudentName' => 'John Doe',
    'StudentEmail' => 'john@example.com',
    'StudentDriLicNum' => 'D1234567',
    'StudentBirthday' => '01/15/1990',
    'StudentCourtName' => 'Municipal Court',
    'StudentCaseNum' => 'ABC123456',
    'StudentCourtDueDate' => '12/31/2025',
    'StudentSchoolName' => 'dummiests',
    'StudentSignUpDate' => '12/01/2025',
    'StudentAddress' => '123 Main St',
    'StudentCity' => 'Las Vegas',
    'StudentState' => 'NV',
    'StudentPostalCode' => '89101',
    'StudentTelephoneNum' => '555-1234',
    'StudentUserID' => '12345',
    'StudentLanguage' => 'English'
]
```

**Database Table**: `ccs_records`
```sql
- id
- enrollment_id
- percentage
- test_date
- status
- certificate_sent_date
```

**Complexity**: Medium
**Estimated Time**: 4-6 hours

---

## Implementation Priority

### Phase 1: California CTSI ⭐ RECOMMENDED NEXT
**Why First**:
- Complements existing CA TVCC
- California is a major market
- XML callback is straightforward
- No external redirect needed

**Benefits**:
- Complete California integration
- Handle both TVCC and CTSI courts
- Unified California certificate system

### Phase 2: Nevada NTSA
**Why Second**:
- Nevada is an active market
- Redirect pattern can be reused for CCS
- Simpler than multi-state CCS

### Phase 3: CCS (Court Compliance System)
**Why Last**:
- Multi-state system (less common)
- Can reuse Nevada redirect pattern
- Lower priority market

---

## Architecture Patterns

### SOAP API Pattern (FL, CA TVCC)
```
CourseCompleted Event
    ↓
CreateStateTransmission Listener
    ↓
StateTransmission Record
    ↓
Queue Job (SOAP API Call)
    ↓
Update Certificate & Transmission
```

### XML Callback Pattern (CA CTSI)
```
External System
    ↓
POST XML to Result URL
    ↓
Parse XML
    ↓
Store Result
    ↓
Update Enrollment Status
```

### Redirect Pattern (NV NTSA, CCS)
```
Course Completion
    ↓
Generate Form Data
    ↓
Auto-Submit Form to External URL
    ↓
External Processing
    ↓
Callback to Result URL
    ↓
Update Enrollment Status
```

---

## Database Schema Overview

### Shared Tables
- `state_transmissions` - All SOAP-based transmissions (FL, CA TVCC)
- `user_course_enrollments` - Links to all certificate types

### State-Specific Tables
- `florida_certificates` - FL DICDS data
- `california_certificates` - CA TVCC data
- `ctsi_results` - CA CTSI callbacks
- `ntsa_records` - NV NTSA data
- `ccs_records` - CCS data

---

## Configuration Needed

### California CTSI
```env
CA_CTSI_ENABLED=true
CA_CTSI_RESULT_URL=https://yourdomain.com/api/ctsi/result
```

### Nevada NTSA
```env
NV_NTSA_ENABLED=true
NV_NTSA_ENDPOINT=https://secure.ntsa.us/cgi-bin/register.cgi
NV_NTSA_SCHOOL_NAME="DUMMIES TRAFFIC SCHOOL.COM"
NV_NTSA_RESULT_URL=https://yourdomain.com/api/ntsa/result
```

### CCS
```env
CCS_ENABLED=true
CCS_ENDPOINT=http://testingprovider.com/ccs/register.jsp
CCS_SCHOOL_NAME=dummiests
CCS_RESULT_URL=https://yourdomain.com/api/ccs/result
```

---

## Estimated Timeline

| Integration | Complexity | Time | Priority |
|-------------|-----------|------|----------|
| CA CTSI | Medium | 4-6 hrs | HIGH ⭐ |
| NV NTSA | Medium | 4-6 hrs | MEDIUM |
| CCS | Medium | 4-6 hrs | LOW |

**Total**: 12-18 hours for all three

---

## Recommendation

**Start with California CTSI** because:
1. ✅ Complements existing CA TVCC
2. ✅ California is your largest market
3. ✅ XML callback is straightforward
4. ✅ No complex redirect flow
5. ✅ Can reuse existing CA infrastructure

Would you like me to implement **California CTSI** next?

---

## Files to Reference

### Old System Admin Files
- `dummiestrafficschool.com - Copy/admin/`
- `ctsiresulturl.jsp` - CTSI callback handler
- `ntsaredirect.jsp` - NTSA redirect
- `resultsurl.jsp` - NTSA callback
- `ccsredirect.jsp` - CCS redirect
- `ccsresultsurl.jsp` - CCS callback

### Old System Source Files
- `src/com/dummies/payment/merchantprocessor/` - XML parsers
- `src/com/dummies/tvccc/` - CA TVCC client
- `src/com/dummies/fltvcc/` - FL client

---

## Success Criteria

Each integration should have:
- ✅ Service class for API/redirect logic
- ✅ Model and database table
- ✅ Result URL endpoint (for callbacks)
- ✅ Admin interface for monitoring
- ✅ Logging and error handling
- ✅ Documentation
- ✅ Test command

---

**Current Status**: 2 of 5 integrations complete (40%)
**Next Step**: Implement California CTSI
**Goal**: Complete all state integrations for full multi-state support
