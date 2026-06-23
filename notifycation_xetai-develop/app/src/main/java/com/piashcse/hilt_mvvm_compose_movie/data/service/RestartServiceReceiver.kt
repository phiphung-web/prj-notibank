package com.piashcse.hilt_mvvm_compose_movie.data.service

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.provider.Settings
import android.util.Log

class RestartServiceReceiver : BroadcastReceiver() {
    override fun onReceive(context: Context, intent: Intent) {
        Log.d("RestartServiceReceiver", "Attempting to restart NotificationListenerService")
        restartNotificationListenerService(context)

        val serviceIntent = Intent(context, NotificationListener::class.java)
        context.startService(serviceIntent)
    }

    private fun restartNotificationListenerService(context: Context) {
        // Open notification access settings
        context.startActivity(Intent(Settings.ACTION_NOTIFICATION_LISTENER_SETTINGS).apply {
            flags = Intent.FLAG_ACTIVITY_NEW_TASK
        })
    }

}