# System URLs Reference

## 🚀 **New Modules Added**

### **📝 Student Feedback System**

#### **Admin URLs:**

- **Main Dashboard**: `/admin/student-feedback`
- **Review Student**: `/admin/student-feedback/{enrollmentId}`
- **Grade Free Response**: `/admin/student-feedback/grade-answer/{answerId}` (AJAX)
- **Quiz Feedback**: `/admin/student-feedback/quiz-feedback/{enrollmentId}/{chapterId}` (AJAX)
- **Question Feedback**: `/admin/student-feedback/question-feedback/{questionId}` (AJAX)

#### **Student URLs:**

- **View Feedback**: `/student/feedback?enrollment_id={enrollmentId}`

#### **Debug URLs:**

- **Quiz Data Debug**: `/debug/quiz-data/{enrollmentId}`

### **⏸️ Chapter Break System**

#### **Admin URLs:**

- **Regular Courses**: `/admin/courses/{courseId}/chapter-breaks`
- **Florida Courses**: `/admin/florida-courses/{courseId}/chapter-breaks`
- **Create Break**: `/admin/{courseType}/{courseId}/chapter-breaks/create`
- **Edit Break**: `/admin/{courseType}/{courseId}/chapter-breaks/{breakId}/edit`
- **Toggle Break**: `/admin/{courseType}/{courseId}/chapter-breaks/{breakId}/toggle` (PATCH)
- **Delete Break**: `/admin/{courseType}/{courseId}/chapter-breaks/{breakId}` (DELETE)

#### **Student URLs:**

- **Check Break Required**: `/student/break/check?enrollment_id={id}&chapter_id={id}`
- **Break Screen**: `/student/break/{sessionId}`
- **Break Status**: `/student/break/{sessionId}/status` (AJAX)
- **Complete Break**: `/student/break/{sessionId}/complete` (AJAX)
- **Skip Break**: `/student/break/{sessionId}/skip` (AJAX)

## 📋 **Complete System URLs**

### **🏠 Admin Dashboard**

- **Main Dashboard**: `/admin/dashboard`
- **Enrollments**: `/admin/enrollments`
- **Users**: `/admin/users`
- **User Access**: `/admin/user-access`
- **Final Exam Attempts**: `/admin/final-exam-attempts`
- **Certificates**: `/admin/certificates`
- **Support Tickets**: `/admin/support-tickets`
- **FAQs**: `/admin/faqs`
- **Reports**: `/admin/reports`

### **📚 Online Courses**

- **Manage Courses**: `/admin/manage-courses`
- **Florida Courses**: `/admin/florida-courses`
- **Courses (Advanced)**: `/admin/courses-advanced`
- **Course Timers**: `/admin/course-timers`

### **📖 Course Booklets**

- **Manage Booklets**: `/admin/manage-booklets`
- **Booklet Orders**: `/admin/booklet-orders`
- **Booklet Templates**: `/admin/booklet-templates`

### **📧 Email & Notifications**

- **Email Templates**: `/admin/email-templates`
- **Notifications**: `/admin/notifications`
- **Newsletter Subscribers**: `/admin/newsletter-subscribers`

### **🔗 Integration & Transmissions**

- **State Integration**: `/admin/state-integration`
- **All State Transmissions**: `/admin/all-transmissions`
- **FL Transmissions**: `/admin/fl-transmissions`
- **State Stamps**: `/admin/state-stamps`

### **💳 Payments & Revenue**

- **Revenue Dashboard**: `/admin/revenue-dashboard`
- **Payments**: `/admin/payments`
- **Pricing Rules**: `/admin/pricing-rules`
- **Coupons**: `/admin/coupons`
- **Invoices**: `/admin/invoices`

### **🔒 Accessibility, Security & Data**

- **Security Questions**: `/admin/security-questions`
- **Final Exam Questions**: `/admin/final-exam-questions`
- **Free Response Quiz**: `/admin/free-response-quiz`
- **Student Feedback**: `/admin/student-feedback` ⭐ **NEW**
- **Data Export**: `/admin/data-export`

### **⚖️ Legal & Compliance**

- **Legal Documents**: `/admin/legal-documents`
- **User Consents**: `/admin/user-consents`

### **🌴 Florida DICDS**

- **Florida Dashboard**: `/admin/florida-dashboard`
- **FLHSMV Submissions**: `/admin/flhsmv-submissions`
- **Florida Certificates**: `/admin/florida-certificates`
- **Compliance Reports**: `/admin/compliance-reports`

### **🏜️ Nevada State**

- **Nevada Dashboard**: `/admin/nevada-dashboard`
- **Nevada Students**: `/admin/nevada-students`

## 🎯 **Quick Access URLs**

### **Most Used Admin URLs:**

```
/admin/dashboard
/admin/manage-courses
/admin/florida-courses
/admin/student-feedback
/admin/enrollments
/admin/users
/admin/certificates
/admin/payments
```

### **New Feature URLs:**

```
# Student Feedback System
/admin/student-feedback

# Chapter Breaks (Regular Courses)
/admin/courses/{courseId}/chapter-breaks

# Chapter Breaks (Florida Courses)
/admin/florida-courses/{courseId}/chapter-breaks

# Free Response Quiz Management
/admin/free-response-quiz
```

## 🔄 **URL Patterns**

### **Chapter Breaks Pattern:**

```
/admin/{courseType}/{courseId}/chapter-breaks
```

Where:

- `{courseType}` = `courses` or `florida-courses`
- `{courseId}` = actual course ID number

**Examples:**

- `/admin/courses/1/chapter-breaks`
- `/admin/florida-courses/5/chapter-breaks`

### **Student Feedback Pattern:**

```
/admin/student-feedback/{enrollmentId}
```

Where:

- `{enrollmentId}` = actual enrollment ID number

**Example:**

- `/admin/student-feedback/12`

### **Student Break Pattern:**

```
/student/break/{sessionId}
```

Where:

- `{sessionId}` = break session ID number

**Example:**

- `/student/break/1`

## 📱 **Mobile-Friendly URLs**

All URLs are mobile-responsive and work on:

- Desktop browsers
- Tablet devices
- Mobile phones
- All modern browsers

## 🔐 **Authentication Requirements**

### **Admin URLs:**

- Require admin authentication
- Role-based access control
- CSRF protection on forms

### **Student URLs:**

- Require student authentication
- Enrollment verification
- Session validation

## 🚀 **How to Access New Features**

### **1. Student Feedback System:**

1. **Login as Admin**
2. **Go to**: `/admin/student-feedback`
3. **Or click**: "Student Feedback" in sidebar
4. **Select student**: Click "Review" button
5. **Provide feedback**: Use the comprehensive interface

### **2. Chapter Break System:**

1. **Login as Admin**
2. **Go to**: `/admin/manage-courses` or `/admin/florida-courses`
3. **Select course**: Find your course
4. **Access breaks**: Go to `/admin/{courseType}/{courseId}/chapter-breaks`
5. **Create break**: Click "Add Chapter Break"

### **3. Free Response Quiz:**

1. **Login as Admin**
2. **Go to**: `/admin/free-response-quiz`
3. **Or click**: "Free Response Quiz" in sidebar
4. **Manage questions**: Create, edit, or delete questions

## 📊 **URL Testing**

### **Test URLs:**

```bash
# Test student feedback
curl -X GET "http://your-domain.com/admin/student-feedback"

# Test chapter breaks
curl -X GET "http://your-domain.com/admin/courses/1/chapter-breaks"

# Test free response quiz
curl -X GET "http://your-domain.com/admin/free-response-quiz"
```

## 🎯 **Navigation Tips**

### **Sidebar Navigation:**

- **Student Feedback**: Under "ACCESSIBILITY, SECURITY & DATA"
- **Chapter Breaks**: Access through course management
- **Free Response Quiz**: Under "ACCESSIBILITY, SECURITY & DATA"

### **Breadcrumb Navigation:**

- All admin pages include breadcrumb navigation
- Easy back navigation to parent sections
- Clear page hierarchy

The system now has comprehensive URL structure with easy access to all new features through the sidebar navigation!
