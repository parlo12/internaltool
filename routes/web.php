<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AICallController;
use App\Http\Controllers\AISalesPersonController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\callController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CSVProcessorController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\FreshleadController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\ShopifyProductSyncController;
use App\Http\Controllers\StepController;
use App\Http\Controllers\UnderContractController;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\WorkflowReportsController;
use App\Http\Controllers\WrongNumberController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Redirect;

Route::get('/', function () {
    return Redirect::route('create-workflow');
});


//CRSF EXCEPT ROUTES
Route::post('/answer-workflow-call', [ContactController::class, 'handleCall'])->name('answer-workflow-call');
Route::post('/transfer-workflow-call', [ContactController::class, 'transferCall'])->name('transfer-workflow-call');
Route::post('/amdStatus-workflow-call', [ContactController::class, 'amdStatus'])->name('amdStatus-workflow-call');
Route::post('/signalwire/redirect', [WorkflowController::class, 'redirect_signalwire_Call'])->name('redirect_signalwire_Call');
Route::post('/twilio/redirect', [WorkflowController::class, 'redirect_twilio_Call'])->name('redirect_twilio_Call');
Route::get('/make-call', [CallController::class, 'makeCall']);
Route::post('/answer', [CallController::class, 'handleCall'])->name('answer');
Route::post('/transfer', [CallController::class, 'transferCall'])->name('transfer');
Route::post('/amdStatus', [CallController::class, 'amdStatus'])->name('amdStatus');
Route::post('/end-of-call', [AICallController::class, 'handleEndOfCallWebhook'])->name('handleEndOfCallWebhook');
Route::get('/recent-calls', [AISalesPersonController::class, 'recentCalls'])->name('recentCalls');
Route::post('/inbound-retell-call', [AICallController::class, 'handleInboundRetellCall'])->name('handleInboundRetellCall');

//CONTACT CONTROLLER
Route::get('/calculate-cost', [ContactController::class, 'calculate_cost'])->name('calculate-cost');
Route::get('/response-check', [ContactController::class, 'response_check'])->name('response-check');
Route::get('/queaue-workflows-contacts', [ContactController::class, 'queaue_messages_from_workflows'])->name('queaue-workflows-contacts');
Route::get('/process-workflows', [ContactController::class, 'process_workflows'])->name('process-workflows');
Route::get('/contacts/export/{id}', [ContactController::class, 'export'])->name('contacts.export');
Route::get('/get_messages', [ContactController::class, 'get_messages']);
Route::get('/send-contact-email', [ContactController::class, 'sendContactEmail']);
Route::post('/contacts/search', [ContactController::class, 'contact_search'])->name('contacts.search');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', AdminMiddleware::class])->group(function () {
    //CSV CONTROLLER
    Route::get('/csv/upload', [CSVProcessorController::class, 'showForm'])->name('upload.csv');
    Route::post('/csv/process', [CSVProcessorController::class, 'processCSV'])->name('process.csv');

    //PROFILE CONTROLLER
    Route::get('/create', [CallController::class, 'create'])->name('create');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //REPORTS CONTROLLER
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');

    //CALL CONTROLLER
    Route::post('/call', [CallController::class, 'call'])->name('call');
    Route::post('/placeholders', [CallController::class, 'get_placeholders'])->name('placeholders');

    //ADMIN CONTROLLER
    Route::post('/users/{user}/toggle-admin', [AdminController::class, 'toggleAdmin'])->name('users.toggle-admin');
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::post('/store-spintax', [AdminController::class, 'store_spintax'])->name('store-spintax');
    Route::delete('/delete-spintax/{id}', [AdminController::class, 'delete_spintax']);
    Route::post('/store-number', [AdminController::class, 'store_number'])->name('store-number');
    Route::post('/store-number-pool', [AdminController::class, 'store_number_pool'])->name('store-number-pool');
    Route::delete('/delete-number/{id}', [AdminController::class, 'delete_number']);
    Route::delete('/delete-number-pool/{id}', [AdminController::class, 'delete_number_pool']);
    Route::post('/store-organisation', [AdminController::class, 'store_organisation'])->name('store-organisation');
    Route::post('/update-organisation', [AdminController::class, 'update_organisation'])->name('update-organisation');
    Route::get('/get_org/{id}', [AdminController::class, 'get_org'])->name('get-orgs');
    Route::post('/update-user-organisation', [AdminController::class, 'update_user_organisation']);
    Route::get('/switch-organisation/{orgId}', [AdminController::class, 'switch_organisation'])->name('switch-organisation');
    Route::post('/submit-api-key', [AdminController::class, 'submit_api_key']);
    Route::get('/no-godspeedoffers-apikey', [AdminController::class, 'no_godspeedoffers_apikey'])->name('no-godspeedoffers-apikey');
    Route::get('/get_server/{id}', [AdminController::class, 'get_server'])->name('get-server');
    Route::get('/get_number_pool/{id}', [AdminController::class, 'get_number_pool'])->name('get-number-pool');
    Route::get('/get_number/{id}', [AdminController::class, 'get_number'])->name('get-number');
    Route::post('/store-server', [AdminController::class, 'store_server'])->name('store-server');
    Route::post('/update-server', [AdminController::class, 'update_server'])->name('update-server');
    Route::post('/update-number-pool', [AdminController::class, 'update_number_pool'])->name('update-number-pool');
    Route::post('/update-number', [AdminController::class, 'update_number'])->name('update-number');

    //WORKFLOW CONTROLLER
    Route::get('/create-workflow', [WorkflowController::class, 'create'])->name('create-workflow');
    Route::get('/workflows/{workflow}/add-steps', [WorkflowController::class, 'add_steps'])->name('add_steps');
    Route::post('/store-workflow', [WorkflowController::class, 'store']);
    Route::get('/delete-workflow/{id}', [WorkflowController::class, 'destroy'])->name('delete-workflow');
    Route::put('/workflows/{id}', [WorkflowController::class, 'update'])->name('workflows.update');
    Route::post('/copy-workflow', [WorkflowController::class, 'copy']);

    //WORKFLOWREPORTS CONTROLLER
    Route::get('/workflow-reports', [WorkflowReportsController::class, 'index'])->name('workflow-reports.index');

    //STEP CONTROLLER
    Route::post('/store-step', [StepController::class, 'store'])->name('store-step');
    Route::post('/update-step', [StepController::class, 'update'])->name('update-step');
    Route::delete('/delete-step/{id}', [StepController::class, 'destroy']);
    Route::get('/step-responses/{id}', [StepController::class, 'step_responses'])->name('step-responses');

    //CONTACT CONTROLLER
    Route::get('/workflow-progress/{id}', [ContactController::class, 'index'])->name('contacts.index');
    Route::get('/start-workflow/{id}', [ContactController::class, 'start_workflow'])->name('start-workflow');
    Route::get('/pause-workflow/{id}', [ContactController::class, 'pause_workflow'])->name('pause-workflow');
    Route::get('/mark-lead/{id}', [ContactController::class, 'mark_lead'])->name('mark-lead');
    Route::get('mark-offer/{id}', [ContactController::class, 'mark_offer'])->name('mark-offer');
    Route::get('/execute-contract/{id}', [ContactController::class, 'execute_contract'])->name('execute-contract');
    Route::get('/cancel-contract/{id}', [ContactController::class, 'cancel_contract'])->name('cancel-contract');
    Route::get('/close-deal/{id}', [ContactController::class, 'close_deal'])->name('close-deal');
    Route::get('/test', [CSVProcessorController::class, 'test'])->name('test');

    //FOLDER CONTROLLER
    Route::post('/create-folder', [FolderController::class, 'create']);
    Route::delete('/delete-folder/{id}', [FolderController::class, 'delete']);
    Route::post('/assign-folder', [FolderController::class, 'assign']);
    Route::get('/folder-workflows/{id}', [FolderController::class, 'get_folder_workflows'])->name('folder-workflows');

    //AiSALESPERSON CONTROLLER
    Route::get('/ai-sales', [AISalesPersonController::class, 'index'])->name('ai.index');
    Route::get('/assistants/{id}/view', [AISalesPersonController::class, 'view'])->name('assistants.view');
    Route::delete('/assistants/{id}', [AISalesPersonController::class, 'destroy'])->name('assistants.delete');
    Route::post('/assistants/{assistant}/update', [AISalesPersonController::class, 'update']);
    Route::get('/assistants/create', [AISalesPersonController::class, 'create'])->name('assistants.create');
    Route::post('/assistants', [AISalesPersonController::class, 'store'])->name('assistants.store');
    Route::get('/assistant/delete/{id}', [AISalesPersonController::class, 'destroy'])->name('assistant.destroy');

    //WRONGNUMBERCONTROLLER
    Route::get('/wrong-numbers', [WrongNumberController::class, 'index'])->name('wrong-numbers.index');
    Route::get('/export-wrong-numbers', [WrongNumberController::class, 'export'])->name('wrongNumbers.export');

    //FOLLOWUPCONTROLLER
    Route::get('/follow-up', [FollowUpController::class, 'index'])->name('follow-ups.index');

    //UNDERCONTRACTCONTROLLER
    Route::get('/under-contract', [UnderContractController::class, 'index'])->name('under-contracts.index');
    //UNDERCONTRACTCONTROLLER
    Route::get('/fresh-lead', [FreshleadController::class, 'index'])->name('fresh-leads.index');
    
});

require __DIR__ . '/auth.php';
