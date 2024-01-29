<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\BankController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AppSettingController;
use App\Http\Controllers\Admin\DomainSettingController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\SupportCategoriesController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\BankLoanController;
use App\Http\Controllers\Admin\VendorProductController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Admin\VirtualCardController;
use App\Http\Controllers\Admin\UserCardController;
use App\Http\Controllers\Admin\SupportQueriesController;
use App\Http\Controllers\Admin\WithdrawalRequestController;
use App\Http\Controllers\Admin\WebhookController;
use App\Http\Controllers\Admin\NotificationsController;
use App\Http\Controllers\Admin\VirtualCardHolderController;
use App\Http\Controllers\Admin\FeesController;

Auth::routes();

Route::redirect('/', '/login');
// Route::GET('/{slug}', [PageController::class, 'viewPage']);

// // Route::POST('otp-admin',[App\Http\Controllers\UserDetailsController::class,'otpAdmin']);
// Route::get('/2fa', [App\Http\Controllers\UserDetailsController::class,'showTwoFactorForm']);
// // Route::post('2fa', [App\Http\Controllers\UserDetailsController::class,'verifyTwoFactor']);
Route::get('verify/resend', [TwoFactorController::class,'resend'])->name('verify.resend');
Route::resource('verify', TwoFactorController::class)->only(['index', 'store']);

Route::get('/webhook', [WebhookController::class, 'handle']);
Route::get('/web' ,[WebhookController::class, 'index']);
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth','twofactor']], function () {

    Route::GET('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
    Route::GET('users/change-status/{id}', [UserController::class, 'changeStatus'])->name('users.change-status');
    Route::POST('users/add-fund/{id}', [UserController::class, 'addFund'])->name('users.add-fund');
    Route::POST('users/revoke-fund/{id}', [UserController::class, 'revokeFund'])->name('users.revoke-fund');
    Route::GET('fincra-user', [UserController::class, 'fincraUser'])->name('fincra-user');
    Route::GET('fincra-beneficiaries', [UserController::class, 'getBeneficiariesApiData'])->name('fincra-beneficiaries');

   

    Route::RESOURCE('users', UserController::class);
    Route::RESOURCE('app-setting', AppSettingController::class);
    Route::RESOURCE('domain-setting', DomainSettingController::class);
    Route::RESOURCE('pages', PageController::class);
    Route::RESOURCE('admin-products', ProductController::class);
    Route::RESOURCE('email-templates', EmailTemplateController::class);
    Route::GET('admin-products/change-status/{id}', [ProductController::class, 'changeStatus'])->name('admin-products.change-status');
    Route::GET('email-templates/change-status/{id}', [EmailTemplateController::class, 'changeStatus'])->name('email-templates.change-status');
    Route::RESOURCE('faqs', FaqController::class);
    Route::RESOURCE('fees', FeesController::class);
    //Route::put('admin-fees/{key}', [FeesController::class, 'update'])->name('admin.fees.update');
    Route::POST('admin-fees', [FeesController::class, 'update'])->name('fees.update');


    Route::RESOURCE('notifications', NotificationsController::class);
    Route::RESOURCE('permissions', PermissionController::class);
    Route::RESOURCE('roles', RoleController::class);
    Route::RESOURCE('bankloan', BankLoanController::class);
    Route::RESOURCE('virtualCard', UserCardController::class);
    Route::RESOURCE('transactions', TransactionController::class);
    Route::RESOURCE('support-categories', SupportCategoriesController::class);
    Route::GET('customer-balance', [TransactionController::class,'customerBalance'])->name('customer-balance');
    Route::get('add-balance/{id}', [TransactionController::class, 'addBalance'])->name('add-balance');
    Route::GET('transaction-utilities', [TransactionController::class,'transactionUtilities'])->name('transaction-utilities');
    Route::GET('single-user-transaction/{id}', [TransactionController::class,'singleUserTransaction'])->name('single-user-transaction');
    Route::GET('sending-money', [TransactionController::class,'sendingMoney'])->name('sending-money');
    Route::GET('request-money', [TransactionController::class,'requestMoney'])->name('request-money');
    Route::DELETE('transaction-delete/{id}', [TransactionController::class,'tDelete'])->name('tDelete');
    // Route::GET('support-queries', [SupportQueriesController::class, 'index'])->name('support-queries');
    Route::RESOURCE('support-queries', SupportQueriesController::class);
    Route::RESOURCE('virtual-card', VirtualCardController::class);
    Route::RESOURCE('virtual-card-holder', VirtualCardHolderController::class);
    Route::GET('withdrawal-requests', [WithdrawalRequestController::class, 'index'])->name('withdrawal-requests.index');
    Route::GET('withdrawal-requests/action/{id}', [WithdrawalRequestController::class, 'withdrawalAction'])->name('withdrawal-requests.action');
    // Route::GET('bankloan', [BankLoanController::class,'index'])->name('bankloan.index');
    // Route::GET('bankloan-show/{id}', [BankLoanController::class,'show'])->name('bankloan.show');
    Route::GET('faqs/change-status/{id}', [FaqController::class, 'changeStatus'])->name('faqs.change-status');
    Route::GET('loan-status-update', [BankLoanController::class, 'loanStatusUpdate']);

    Route::post('customer-update-balance/{id}', [TransactionController::class, 'customerUpdateBalance'])->name('customer-update-balance');

});
