import './bootstrap';
import { createApp } from 'vue';
import CreateCourse from './components/CreateCourse.vue';
import CourseList from './components/CourseList.vue';
import CoursePlayer from './components/CoursePlayer.vue';
import Login from './components/Login.vue';
import MyEnrollments from './components/MyEnrollments.vue';
import Register from './components/Register.vue';
import UserForm from './components/UserForm.vue';
import UserList from './components/UserList.vue';
import UserProfile from './components/UserProfile.vue';
import ChapterList from './components/ChapterList.vue';
import QuizComponent from './components/QuizComponent.vue';
import ProgressTracker from './components/ProgressTracker.vue';
import EnrollmentList from './components/EnrollmentList.vue';
import AdminDashboard from './components/AdminDashboard.vue';
import PaymentForm from './components/PaymentForm.vue';
import DicdsOrderManagement from './components/DicdsOrderManagement.vue';
import OrderAmendmentModal from './components/OrderAmendmentModal.vue';
import OrderReceiptGenerator from './components/OrderReceiptGenerator.vue';
import FloridaSecurityDashboard from './components/FloridaSecurityDashboard.vue';
import FloridaAuditTrailViewer from './components/FloridaAuditTrailViewer.vue';
import FloridaComplianceManager from './components/FloridaComplianceManager.vue';
import FloridaDataExportTool from './components/FloridaDataExportTool.vue';
import FloridaResponsiveLayout from './components/FloridaResponsiveLayout.vue';
import FloridaAccessibilitySettings from './components/FloridaAccessibilitySettings.vue';
import FloridaMobileCoursePlayer from './components/FloridaMobileCoursePlayer.vue';
import FloridaPWAPrompt from './components/FloridaPWAPrompt.vue';
import DicdsWelcomeScreen from './components/DicdsWelcomeScreen.vue';
import DicdsMainMenu from './components/DicdsMainMenu.vue';
import DicdsUserRoleAdmin from './components/DicdsUserRoleAdmin.vue';

// Mount Vue if app element exists
const appElement = document.getElementById('app');
if (appElement) {
    const app = createApp({});

    // Register all components globally
    app.component('create-course', CreateCourse);
    app.component('course-list', CourseList);
    app.component('course-player', CoursePlayer);
    app.component('login', Login);
    app.component('my-enrollments', MyEnrollments);
    app.component('register', Register);
    app.component('user-form', UserForm);
    app.component('user-list', UserList);
    app.component('user-profile', UserProfile);
    app.component('chapter-list', ChapterList);
    app.component('quiz-component', QuizComponent);
    app.component('progress-tracker', ProgressTracker);
    app.component('enrollment-list', EnrollmentList);
    app.component('admin-dashboard', AdminDashboard);
    app.component('payment-form', PaymentForm);
    app.component('dicds-order-management', DicdsOrderManagement);
    app.component('order-amendment-modal', OrderAmendmentModal);
    app.component('order-receipt-generator', OrderReceiptGenerator);
    app.component('florida-security-dashboard', FloridaSecurityDashboard);
    app.component('florida-audit-trail-viewer', FloridaAuditTrailViewer);
    app.component('florida-compliance-manager', FloridaComplianceManager);
    app.component('florida-data-export-tool', FloridaDataExportTool);
    app.component('florida-responsive-layout', FloridaResponsiveLayout);
    app.component('florida-accessibility-settings', FloridaAccessibilitySettings);
    app.component('florida-mobile-course-player', FloridaMobileCoursePlayer);
    app.component('florida-pwa-prompt', FloridaPWAPrompt);
    app.component('dicds-welcome-screen', DicdsWelcomeScreen);
    app.component('dicds-main-menu', DicdsMainMenu);
    app.component('dicds-user-role-admin', DicdsUserRoleAdmin);

    app.mount('#app');
}
