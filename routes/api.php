<?php

use Illuminate\Support\Facades\Route;

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

Route::prefix('user')->group(__DIR__ . '/User/User.routes.php');
Route::prefix('user-location')->group(__DIR__ . '/UserLocation/UserLocation.routes.php');
Route::prefix('surroundings')->group(__DIR__ . '/Surroundings/SurroundingApi.routes.php');
Route::prefix('in-house')->group(__DIR__ . '/Ads/Ads.routes.php');
Route::prefix('bugs')->group(__DIR__ . '/Bugs/Bugs.routes.php');
Route::prefix('business')->group(__DIR__ . '/Business/Business.routes.php');
Route::prefix('reward')->group(__DIR__ . '/Reward/Reward.routes.php');
Route::prefix('my-favorites')->group(__DIR__ . '/MyFavorites/MyFavorites.routes.php');
Route::prefix('loyalty-points')->group(__DIR__ . '/LoyaltyPoints/LoyaltyPoints.routes.php');
Route::prefix('customer-manager')->group(__DIR__ . '/CustomerManager/CustomerManager.routes.php');
Route::prefix('redeemable')->group(__DIR__ . '/Redeemable/Redeemable.routes.php');
Route::prefix('lp-tiers')->group(__DIR__ . '/LoyaltyTiers/LoyaltyTiers.routes.php');
Route::prefix('feedback')->group(__DIR__ . '/Feedback/Feedback.routes.php');
Route::prefix('business-app')->group(__DIR__ . '/AppDownload/BusinessApp.routes.php');
Route::prefix('promoter')->group(__DIR__ . '/Promoter/Promoter.routes.php');
Route::prefix('meet-ups')->group(__DIR__ . '/MeetUps/MeetUps.routes.php');
Route::prefix('my-friends')->group(__DIR__ . '/MyFriends/MyFriends.routes.php');
Route::prefix('cache-bust')->group(__DIR__ . '/CacheBust/CacheBust.routes.php');

