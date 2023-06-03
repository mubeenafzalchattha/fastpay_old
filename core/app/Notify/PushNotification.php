<?php

namespace App\Notify;

use App\Models\DeviceToken;
use App\Models\UserNotification;
use App\Notify\NotifyProcess;

class PushNotification extends NotifyProcess {
    /**
     * Assign value to properties
     *
     * @return void
     */
    public function __construct() {
        $this->statusField = 'push_notification_status';
        $this->body = 'push_notification_body';
        $this->globalTemplate = 'push_notification_body';
        $this->notifyConfig = 'push_notification_config';
    }

    /**
     * Send notification
     *
     * @return void|bool
     */


    public function send() {
        $message = $this->getMessage();
        $subject = $this->subject;
        $remark = $this->template->act;
        $clickValue = $this->clickValue;

        if ($this->setting->pn && $message) {
            try {
                $serverKey = $this->setting->push_notification_config;
                if ($this->user) {
                    $data['icon'] = getImage('assets/images/logoIcon/logo.png');
                    $url = 'https://fcm.googleapis.com/fcm/send';
                    $data['priority'] = 'high';
                    $FcmToken = DeviceToken::where('user_id', $this->user->id)->pluck('token')->toArray();
                    if (count($FcmToken) > 0) {
                        $data = [
                            "registration_ids" => $FcmToken,
                            "notification" => [
                                'title' => $subject,
                                'body' => $message,
                            ],
                            'data'  => [
                                'remark' => $remark,
                                'click_value' => $clickValue
                            ]
                        ];

                        $encodedData = json_encode($data);
                        $headers = [
                            'Authorization:key=' . $serverKey,
                            'Content-Type: application/json',
                            'priority:high'
                        ];

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                        // Disabling SSL Certificate support temporary
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
                        // Execute post
                        $result = curl_exec($ch);
                        $result = json_decode($result);

                        if (@$result->success) {
                            $userNotification = new UserNotification();
                            $userNotification->title = $subject;
                            $userNotification->user_id = $this->user->id;
                            $userNotification->remark = $remark;
                            $userNotification->click_value = $clickValue;
                            $userNotification->save();
                            $this->createLog('push_notification');
                        } else {
                            $this->createErrorLog('Push Notification Error: ' . $result->results[0]->error);
                            session()->flash('push_notification_error', $result->results[0]->error);
                        }

                        // Close connection
                        curl_close($ch);
                        // FCM response
                    }
                }
            } catch (\Exception $e) {
                $this->createErrorLog('Push Notification Error: ' . $e->getMessage());
                session()->flash('push_notification_error', $e->getMessage());
            }
        }
    }

    /**
     * Configure some properties
     *
     * @return void
     */
    protected function prevConfiguration() {
    }
}
