<?php

use App\Http\Controllers\DicdsAuthController;
use App\Http\Controllers\DicdsController;
use App\Http\Controllers\WebAdminController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::get('/login', [DicdsAuthController::class, 'showLogin'])->name('dicds.login');
Route::post('/login', [DicdsAuthController::class, 'login']);
Route::get('/register', [DicdsAuthController::class, 'showRegister'])->name('dicds.register');
Route::post('/register', [DicdsAuthController::class, 'register']);
Route::get('/access-request', [DicdsAuthController::class, 'showAccessRequest'])->name('dicds.access-request');
Route::post('/access-request', [DicdsAuthController::class, 'accessRequest']);
Route::post('/logout', [DicdsAuthController::class, 'logout'])->name('dicds.logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/main-menu', [DicdsController::class, 'mainMenu'])->name('dicds.main-menu');
    Route::get('/welcome', [DicdsController::class, 'welcome'])->name('dicds.welcome');
    Route::get('/provider-menu', [DicdsController::class, 'providerMenu'])->name('dicds.provider-menu');

// School Management
Route::get('/schools/add', [DicdsController::class, 'addSchool'])->name('dicds.schools.add');
Route::post('/schools', [DicdsController::class, 'storeSchool'])->name('dicds.schools.store');
Route::get('/schools/maintain', [DicdsController::class, 'maintainSchool'])->name('dicds.schools.maintain');
Route::get('/schools/{id}/edit', [DicdsController::class, 'editSchool'])->name('dicds.schools.edit');
Route::put('/schools/{id}', [DicdsController::class, 'updateSchool'])->name('dicds.schools.update');
Route::delete('/schools/{id}', [DicdsController::class, 'destroySchool'])->name('dicds.schools.destroy');
Route::get('/schools/search', [DicdsController::class, 'searchSchool'])->name('dicds.schools.search');

// Course Management
Route::get('/courses/add', [DicdsController::class, 'addCourse'])->name('dicds.courses.add');
Route::post('/courses', [DicdsController::class, 'storeCourse'])->name('dicds.courses.store');

// Contact
Route::get('/contact', [DicdsController::class, 'contact'])->name('dicds.contact');
Route::post('/contact', [DicdsController::class, 'submitContact'])->name('dicds.contact.submit');

// Instructor Management
Route::get('/instructors/manage', [DicdsController::class, 'manageInstructors'])->name('dicds.instructors.manage');
Route::get('/instructors/add', [DicdsController::class, 'addInstructor'])->name('dicds.instructors.add');
Route::post('/instructors', [DicdsController::class, 'storeInstructor'])->name('dicds.instructors.store');
Route::get('/instructors/{id}/edit', [DicdsController::class, 'editInstructor'])->name('dicds.instructors.edit');
Route::put('/instructors/{id}', [DicdsController::class, 'updateInstructor'])->name('dicds.instructors.update');
Route::delete('/instructors/{id}', [DicdsController::class, 'destroyInstructor'])->name('dicds.instructors.destroy');

// Certificate Management
Route::get('/certificates/order', [DicdsController::class, 'orderCertificates'])->name('dicds.certificates.order');
Route::post('/certificates/order', [DicdsController::class, 'storeOrder'])->name('dicds.certificates.store-order');
Route::get('/certificates/distribute', [DicdsController::class, 'distributeCertificates'])->name('dicds.certificates.distribute');
Route::post('/certificates/distribute', [DicdsController::class, 'storeDistribution'])->name('dicds.certificates.store-distribution');
Route::get('/certificates/reclaim', [DicdsController::class, 'reclaimCertificates'])->name('dicds.certificates.reclaim');
Route::get('/certificates/maintain', [DicdsController::class, 'maintainCertificates'])->name('dicds.certificates.maintain');

// Reports
Route::get('/reports/schools-certificates', [DicdsController::class, 'schoolsCertificates'])->name('dicds.reports.schools-certificates');
Route::get('/reports/menu', [DicdsController::class, 'certificateReports'])->name('dicds.reports.menu');
Route::get('/reports/certificate-lookup', [DicdsController::class, 'certificateLookup'])->name('dicds.reports.certificate-lookup');
Route::get('/reports/school-activity', [DicdsController::class, 'schoolActivity'])->name('dicds.reports.school-activity');
Route::get('/web-service-info', [DicdsController::class, 'webServiceInfo'])->name('dicds.web-service-info');

// Web Administration
Route::get('/admin', [WebAdminController::class, 'index'])->name('dicds.admin.index');
Route::get('/admin/user-role-admin', [WebAdminController::class, 'userRoleAdmin'])->name('dicds.admin.user-role-admin');
Route::get('/admin/search-users', [WebAdminController::class, 'searchUsers'])->name('dicds.admin.search-users');
Route::get('/admin/users/{id}', [WebAdminController::class, 'showUser'])->name('dicds.admin.show-user');
Route::put('/admin/users/{id}/status', [WebAdminController::class, 'updateUserStatus'])->name('dicds.admin.update-status');
Route::put('/admin/users/{id}/password', [WebAdminController::class, 'resetPassword'])->name('dicds.admin.reset-password');
Route::put('/admin/users/{id}/role', [WebAdminController::class, 'updateUserRole'])->name('dicds.admin.update-role');
});
