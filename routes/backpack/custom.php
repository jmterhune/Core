<?php

use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.
 Route::domain(env('APP_URL'))->group(function() {
     Route::group([
         'prefix' => config('backpack.base.route_prefix', 'admin'),
         'middleware' => array_merge(
             (array)config('backpack.base.web_middleware', 'web'),
             (array)config('backpack.base.middleware_key', 'admin')
         ),
         'namespace' => 'App\Http\Controllers\Admin\Api',
     ], function () { // custom admin routes
         Route::get('/api/attorney', 'AttorneyController@index');
         Route::get('/api/judge', 'JudgeController@index');
         Route::get('/api/user', 'UserController@index');
         Route::get('/api/calendarevents', 'CalendarEventsController@index');
         Route::get('/api/courtmotions', 'CourtMotionsController@index');
         Route::get('/api/courteventtypes', 'CourtEventTypesController@index');

     }); // this should be the absolute last line of this file

     Route::group([
         'prefix' => config('backpack.base.route_prefix', 'admin'),
         'middleware' => array_merge(
             (array)config('backpack.base.web_middleware', 'web'),
             (array)config('backpack.base.middleware_key', 'admin')
         ),
         'namespace' => 'App\Http\Controllers\Admin',
     ], function () { // custom admin routes
         Route::delete('timeslot/multi', 'TimeslotController@destroy_multi');
         Route::delete('timeslot/temp_multi', 'TimeslotController@template_destroy_multi');
         Route::post('timeslot/temp_copy', 'TimeslotController@temp_copy');
         Route::post('timeslot/copy', 'TimeslotController@copy');
         Route::crud('user', 'UserCrudController');
         Route::crud('attorney', 'AttorneyCrudController');
         Route::crud('judge', 'JudgeCrudController');
         Route::crud('court', 'CourtCrudController');
         Route::crud('mediation/instructions', 'MediationInstructionCrudController');
         Route::get('court/{id}/category', 'CourtCrudController@getCategory')->name('court.category');
         Route::get('court/{id}/category', 'CourtCrudController@getCategory')->name('court.category');
         Route::crud('court-type', 'CourtTypeCrudController');
         Route::get('attorney/{id}/reset', 'AttorneyCrudController@reset');
         Route::get('attorney/details/{id}', 'AttorneyCrudController@getAttroneyDetails');
         Route::crud('motion', 'MotionCrudController');
         Route::post('motion/getCourtTimeSlotMotions', 'MotionCrudController@getCourtTimeSlotMotions');
         Route::crud('holiday', 'HolidayCrudController');
         Route::crud('court-permission', 'CourtPermissionCrudController');
         Route::crud('category', 'CategoryCrudController');
         Route::crud('event', 'EventCrudController');
         Route::crud('mediation', 'MediationCrudController');
         Route::crud('mediationfamily', 'MediationFamilyCrudController');
         Route::post('mediationfamily/case/search', 'MediationFamilyCrudController@searchCaseNumber');

         Route::post('mediationfamily/event/search', 'MediationFamilyCrudController@searchEvents');
         Route::post('mediationfamily/availabeTimings', 'MediationFamilyCrudController@availableTimings');
         Route::post('mediationfamily/event/store', 'MediationFamilyCrudController@eventStore');
         Route::delete('mediationfamily/event/delete', 'MediationFamilyCrudController@eventDelete');
         Route::get('mediationfamily/event/{eventId}/edit', 'MediationFamilyCrudController@editEventSchedule');
         Route::post('mediationfamily/event/{eventId}/update', 'MediationFamilyCrudController@updateEventSchedule');
         Route::get('mediationfamily/case/print/{caseId}', 'MediationFamilyCrudController@printCase');
         Route::get('mediationfamily/payments/{caseNo}', 'MediationCaseEventPaymentsController@familyindex');
         Route::post('mediationfamily/payments/add', 'MediationCaseEventPaymentsController@familyaddPayment');
         Route::get('mediationfamily/payments/edit/{paymentId}', 'MediationCaseEventPaymentsController@familyeditPayment');
         Route::post('mediationfamily/payments/update', 'MediationCaseEventPaymentsController@familyupdatePayment');
         Route::delete('mediationfamily/payments/delete', 'MediationCaseEventPaymentsController@familypaymentDelete');
         Route::get('mediationfamily/outcome', 'MediationFamilyCrudController@outcomeList');

         Route::post('mediation/case/search', 'MediationCrudController@searchCaseNumber');
         Route::get('mediation/case/scformlist', 'MediationCrudController@scFormList');
         Route::post('mediation/case/scformapprove', 'MediationCrudController@scFormApprove');
         Route::post('mediation/case/scformdelete', 'MediationCrudController@scFormDelete');
         Route::post('mediation/event/search', 'MediationCrudController@searchEvents');
         Route::post('mediation/availabeTimings', 'MediationCrudController@availableTimings');
         Route::post('mediation/event/store', 'MediationCrudController@eventStore');
         Route::delete('mediation/event/delete', 'MediationCrudController@eventDelete');
         Route::get('mediation/event/{eventId}/edit', 'MediationCrudController@editEventSchedule');
         Route::post('mediation/event/{eventId}/update', 'MediationCrudController@updateEventSchedule');
         Route::crud('mediation/events', 'MediationEventsCrudController');
         Route::get('mediation/outcome', 'MediationCrudController@outcomeList');
         Route::crud('mediation/availableschedule', 'MediationAvailableScheduleCrudController');
         Route::crud('mediation/notavailableschedule', 'MediationNotAvailableScheduleCrudController');
         Route::crud('mediation/mediator', 'MediationMediatorCrudController');

         Route::crud('mediation/documents', 'MediationDocumentsCrudController');
         Route::get('mediation/document/download/{docId}', 'MediationDocumentsCrudController@downloadFile');
         Route::get('mediation/case/documents/{caseId}', 'MediationCaseDocumentsCrudController@index');
         Route::post('mediation/case/document/build', 'MediationCaseDocumentsCrudController@buildCaseDocument');
         Route::post('mediation/case/document/delete', 'MediationCaseDocumentsCrudController@deleteCaseDocuments');

         Route::get('mediation/case/print/{caseId}', 'MediationCrudController@printCase');
         Route::get('mediation/payments/{caseNo}', 'MediationCaseEventPaymentsController@index');
         Route::post('mediation/payments/add', 'MediationCaseEventPaymentsController@addPayment');
         Route::get('mediation/payments/edit/{paymentId}', 'MediationCaseEventPaymentsController@editPayment');
         Route::post('mediation/payments/update', 'MediationCaseEventPaymentsController@updatePayment');
         Route::delete('mediation/payments/delete', 'MediationCaseEventPaymentsController@paymentDelete');
         Route::get('mediation/report/week', 'MediationReportsController@index');
         Route::post('mediation/report/week/search', 'MediationReportsController@searchReport');
         Route::get('mediation/report/countystats', 'MediationReportsController@countyStatsReport');
         Route::post('mediation/report/getcountystats', 'MediationReportsController@getCountyStats');
         Route::get('mediation/report/mediator', 'MediationReportsController@mediatorReport');
         Route::post('mediation/report/getmediatorstats', 'MediationReportsController@getMediatorStats');

         Route::post('event/future/bulk-delete', 'EventCrudController@bulkDeleteEvent');
         Route::post('event/casenum', 'EventCrudController@caseNumSearch');
//         Route::get('/mediation/{form}', 'FormsController@show')->name('forms.sc-form');
//         Route::post('/mediation/{form}', 'FormsController@submit')->name('forms.submit');
         Route::crud('county', 'CountyCrudController');
         Route::resource('fullcal', 'FullCalendarController');
         Route::resource('timeslot-events', 'TimeslotEventsController');
         Route::resource('court-timeslots', 'CourtTimeslotsController');
         Route::get('court-timeslots/print/{court}/{cal_from_date}/{cal_to_date}', 'CourtTimeslotsController@printPDF');
         Route::resource('timeslot', 'TimeslotController');
         Route::crud('event-type', 'EventTypeCrudController');
         Route::crud('timeslot-crud', 'TimeslotCrudController');
         Route::crud('event-status', 'EventStatusCrudController');


         Route::get('court/event/calendar/download/{courdId}/{fromDate}/{toDate}','EventReminderController@downloadCourtEventCalendar');

         Route::get('available-timeslots/{court}', 'CourtTimeslotsController@available_timeslots')
             ->name('available-timeslots.show');


         Route::get('court-blocked-timeslots/{court}', 'CourtBlockedTimeslotsController@index')->name('court.blocked');

         Route::get('calendar/{calendar}', 'CalendarController@show')->name('calendar.show');
         Route::get('docket', 'DocketController@index')->name('docket.index');
         Route::post('docket/print', 'DocketController@print')->name('docket.print');

         Route::get('/email_instruction/{case_id}', 'MediationEmailInstrunctionController@show')->name('email_instruction.show');
         Route::post('/email_instruction/email', 'MediationEmailInstrunctionController@emailInstructions')->name('email_instruction.email');

         Route::get('/mediation_invoice/{event_id}', 'MediationInvoiceController@generateInvoice')->name('mediation.invoice');

         Route::crud('template', 'TemplateCrudController');
         Route::resource('court_template', 'TemplateController');

         Route::get('template_config/{template}', 'TemplateCrudController@configure')->name('template.configure');

         Route::crud('court-template-order', 'CourtTemplateOrderCrudController');
         Route::resource('user_defined_fields', 'UserDefinedController');
         Route::get('user_defined/fields', 'UserDefinedController@fields')->name('user_defined.fields');
         Route::get('user_defined_fields/{user_defined_fields}', 'TemplateController@create')->name('user_defined_fields.create');

         Route::get('udf/fields', 'UserDefinedFieldsController@fields')->name('udf.fields');

         Route::resource('udf', 'UserDefinedFieldsController');
         Route::get('udf/{udf}', 'UserDefinedFieldsController@create')->name('udf.create');
	    Route::get('dashboard', 'AdminController@dashboard')->name('backpack.dashboard');
	    Route::get('quick-reference', 'AdminController@quickReference')->name('backpack.quick-reference');
         Route::get('quick-reference', 'AdminController@quickReference')->name('backpack.quick-reference');
         Route::get('/', 'AdminController@redirect')->name('backpack');
         Route::crud('tickets', 'TicketsCrudController');
         Route::get('/api/tickets_status', 'TicketsCrudController@ticketsStatusSelect');
         Route::get('/api/priority', 'TicketsCrudController@ticketsPrioritySelect');

         Route::get('calendar/{calendar}/truncate', 'CalendarController@truncate')->name('calendar.truncate');
         Route::post('calendar/{calendar}/truncate_timeslots', 'CalendarController@truncate_timeslots')->name('truncate_timeslots');

         Route::get('calendar/{calendar}/extend', 'CalendarController@extend')->name('calendar.extend');
         Route::post('calendar/{calendar}/extend_calendar', 'CalendarController@extend_calendar')->name('extend_calendar');
         Route::get('calendar/{calendar}/extend_manual', 'CalendarController@extend_manual')->name('extend_calendar_manual');

         Route::get('court-timeslots-month/{court_timeslot}', 'CourtTimeslotsController@month')->name('court-timeslots.month');

         Route::get('calendar/{calendar}/upload', 'CalendarController@upload')->name('calendar.upload');
         Route::post('calendar/{calendar}/upload_data', 'CalendarController@upload_data')->name('upload_data');


     }); // this should be the absolute last line of this file
     Route::group([
         'prefix' => config('backpack.base.route_prefix', 'admin'),
         'namespace' => 'App\Http\Controllers\Admin',
     ], function () {
         Route::get('event-reminder-link/{eventid}/{email}', 'EventReminderController@eventReminderEnable');
     });

 });
