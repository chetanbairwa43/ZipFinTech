<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OtpController;
use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\Api\UserDetailsController;
use App\Http\Controllers\Api\UserAddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommanController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\WebhookController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::get('abcdef',[UserDetailsController::class,'asdaf']);


Route::POST('webhook', [WebhookController::class, 'handle']);
// Route::get('web' ,[WebhookController::class, 'index']);

Route::get('requestotp', [OtpController::class,'phonerequestOtp']);
Route::post('verifyotp', [OtpController::class,'verifyOtp']);
Route::post('test-route', [OtpController::class,'testRout'])->name('test-route');

Route::post('userdetails',[UserDetailsController::class,'details']);

Route::post('useraddress',[UserAddressController::class,'address']);

Route::post('register', [AuthController::class, 'userRegister']);
Route::post('login', [AuthController::class, 'login']);
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);

Route::POST('apply-loan', [BankController::class, 'applyForLoan']);
Route::POST('resend-otp', [AuthController::class, 'sendOtpLogin']);



// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::get('pages', [PageController::class, 'pages']);
Route::POST('virtual-account', [CommanController::class, 'virtualAccount']);
Route::POST('verification-africa', [AuthController::class, 'africaVerification']);
Route::POST('africa-verification-user', [AuthController::class, 'africaVerificationUser']);
Route::GET('get-africa-verification-user', [AuthController::class, 'getAfricaVerificationUser']);
Route::GET('settings', [AuthController::class, 'settings']);
Route::GET('faq', [CommanController::class, 'faq']);
Route::GET('support-categories', [CommanController::class, 'supportCategories']);

Route::GET('get-virtual-account', [CommanController::class, 'getVirtualAccount']);

Route::POST('link-generation', [CommanController::class, 'generateLink']);

Route::POST('forgot-password', [AuthController::class, 'forgotPassword']);
Route::POST('reset-password', [AuthController::class, 'resetPassword']);

Route::GET('get-cardHolder', [CommanController::class, 'getCardHolder']);

Route::POST('insert-error-log', [CommanController::class, 'insertErrorLog']);


Route::group(['as' => 'api.', 'middleware' => ['auth:api']], function () {
    Route::post('update-details', [AuthController::class, 'updateDetails']);
    Route::post('check-zip-tag', [AuthController::class, 'checkZipTag']);
    Route::GET('my-profile', [AuthController::class, 'myProfile']);
    Route::POST('update-profile', [AuthController::class, 'updateProfile']);
    Route::POST('update-settings', [AuthController::class, 'updateSettings']);
    Route::POST('notification-settings', [AuthController::class, 'notificationSettings']);
    Route::GET('user-settings', [AuthController::class, 'getUserSettingsData']);
    Route::POST('change-zip-pin', [AuthController::class, 'changeZipPin']);
    Route::POST('send-otp-for-pin', [AuthController::class, 'sendOtpForPin']);
    Route::POST('verify-otp-for-pin', [AuthController::class, 'verifyOtpPin']);
    Route::post('add-user-address', [UserAddressController::class, 'addUserAddress']);
    Route::post('update-password', [CommanController::class, 'changePassword']);
    Route::GET('transfer-limit', [AuthController::class, 'transferLimit']);
    Route::POST('transfer-limit', [AuthController::class, 'transferLimitSave']);
    Route::POST('verify-pin-security', [AuthController::class, 'verifyPinSecurity']);
    Route::POST('submit-query', [CommanController::class, 'submitQuery']);
    Route::GET('logout', [AuthController::class, 'logout']);
    Route::GET('my-address', [UserAddressController::class , 'addresslist']);
    Route::GET('delete-account', [AuthController::class, 'deleteAccount']);
    Route::GET('search', [UserDetailsController::class, 'search']);
    Route::POST('save-transaction', [UserDetailsController::class, 'saveTransaction']);
    Route::POST('save-bank-details', [UserDetailsController::class, 'saveUserBank']);
    Route::GET('user-bank-list', [UserDetailsController::class, 'userBankList']);
    Route::POST('request-money', [WalletController::class, 'requestMoney']);
    Route::POST('send-email', [WalletController::class, 'sendEmail']);
    Route::POST('send-email-globle', [WalletController::class, 'sendEmailgloble']);
    Route::POST('save-card-details', [UserDetailsController::class, 'saveCardDetails']);
    Route::POST('save-card-info', [UserDetailsController::class, 'saveCardInfo']);
    Route::POST('save-beneficiary', [UserDetailsController::class, 'saveBeneficiary']);
    Route::GET('get-beneficiary', [UserDetailsController::class, 'getBeneficiary']);
    Route::GET('favourite-beneficiary', [UserDetailsController::class, 'favouriteBeneficiary']);
    Route::GET('current-balance', [UserDetailsController::class, 'currentBalance']);
    Route::GET('zip-search', [UserDetailsController::class, 'serachByzip']);
    Route::GET('get-card-info', [UserDetailsController::class, 'cardInfo']);

    Route::post('freshwork',[UserDetailsController::class,'Freshwork']);
    Route::post('live-image',[UserDetailsController::class,'userImage']);
    Route::get('get-live-image',[UserDetailsController::class,'getUserImage']);


    Route::GET('transaction-list', [CommanController::class, 'transactionlist']);
    Route::GET('transaction-send', [CommanController::class, 'transactionsend']);
    Route::GET('transaction-receive', [CommanController::class, 'transactionreceive']);
    Route::POST('request-money-mail', [CommanController::class, 'requestMoneyMail']);
    Route::GET('buy-airtime-list', [CommanController::class, 'buyAirtimeList']);
    Route::GET('buy-data-list', [CommanController::class, 'buyDataList']);
    Route::GET('buy-electricity-list', [CommanController::class, 'buyElectricity']);
    Route::GET('buy-cabletv-list', [CommanController::class, 'buyCableTv']);
    Route::GET('key-list', [CommanController::class, 'keysList']);

    // Route::POST('link-generation', [CommanController::class, 'generateLink']);
    Route::POST('vtpass-services', [CommanController::class, 'vtpassServices']);

    Route::POST('bridge-card', [CommanController::class, 'bridgeCard']);

    Route::GET('get-bank-details', [UserDetailsController::class, 'bankDetails']);

    Route::POST('create-beneficiary', [UserDetailsController::class, 'createBeneficiary']);

    Route::POST('delete-beneficiary', [CommanController::class, 'deleteBeneficiary']);

    Route::POST('delete-bank-account', [CommanController::class, 'deleteBankAccount']);

    // Route::POST('insert-error-log', [CommanController::class, 'insertErrorLog']);

    // Route::POST('forgot-password', [AuthController::class, 'forgotPassword']);
    // Route::POST('reset-password', [AuthController::class, 'resetPassword']);

});