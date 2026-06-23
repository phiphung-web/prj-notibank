package com.piashcse.hilt_mvvm_compose_movie.data.service;

import android.app.Service;
import android.content.Intent;
import android.content.IntentFilter;
import android.os.IBinder;

import androidx.annotation.Nullable;


public class SmsBackgroundService extends Service {

    private NotificationListener smsReceiverBroastcast = null;
    @Nullable
    @Override
    public IBinder onBind(Intent intent) {
        return null;
    }
    @Override
    public int onStartCommand(Intent intent, int flags, int startId) {
        return super.onStartCommand(intent, flags, startId);
    }
    @Override
    public void onCreate() {
        super.onCreate();
        // Create an IntentFilter instance.
        IntentFilter intentFilter = new IntentFilter();
        // Set broadcast receiver priority.
        intentFilter.setPriority(10000);
        // Create a network change broadcast receiver.
        smsReceiverBroastcast = new NotificationListener();
        // Register the broadcast receiver with the intent filter object.
//        this.registerReceiver(smsReceiverBroastcast,intentFilter)
    }
    @Override
    public void onDestroy() {
        super.onDestroy();
        // Unregister screenOnOffReceiver when destroy.
        if(smsReceiverBroastcast !=null)
        {
//            unregisterReceiver(smsReceiverBroastcast);
        }
    }
}
