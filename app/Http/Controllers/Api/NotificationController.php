<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helper\ResponseBuilder;
use App\Models\Notification;
use App\Http\Resources\Admin\NotificationCollection;
use Auth;
class NotificationController extends Controller
{
    public function notificationList(){
        try {
            $user = Auth::guard('api')->user();
            $data = Notification::getNotificationByuser($user->id);
            $data = new NotificationCollection($data);

            $notificationData['count'] = count($data);
            $notificationData['notificationData']  =  $data;

            return ResponseBuilder::success(trans('global.notification_list'), $this->success,$notificationData);
        } catch (\Exception $e) {
            return ResponseBuilder::error(trans('global.SOMETHING_WENT'),$this->badRequest);
        }
    }
}
