package com.piashcse.hilt_mvvm_compose_movie.data.service

import android.annotation.SuppressLint
import android.app.Notification
import android.content.Intent
import android.os.Bundle
import android.service.notification.NotificationListenerService
import android.service.notification.StatusBarNotification
import android.util.Log
import androidx.core.app.NotificationCompat
import com.piashcse.hilt_mvvm_compose_movie.utils.AppConstant
import com.piashcse.hilt_mvvm_compose_movie.utils.NotificationConstants


@SuppressLint("OverrideAbstract")
class NotificationListener : NotificationListenerService() {
    companion object {
        val TAG = "NotificationListener"
        val LIST_PACKAGE_LISTENER =
            listOf("com.tpb.mb.gprsandroid", "com.vnpay.bidv", "com.mbmobile","vn.com.msb.smartBanking")
    }

    override fun onNotificationPosted(notification: StatusBarNotification) {
        handleNotification(notification, false)

    }
    private var contentTemp : String? = null

    @SuppressLint("LogNotTimber")
    private fun handleNotification(notification: StatusBarNotification, isRemoved: Boolean) {
        val packageName = notification.packageName
        Log.d("dongnd1", "notification incomming : $packageName")
        if (packageName.contains("com.tpb.mb.gprsandroid")|| packageName.contains("com.mbmobile")
            || packageName.contains("com.vnpay.bidv")|| packageName.contains("vn.com.msb.smartBanking")
            ||  packageName.contains("com.sacombank.ewallet") ||   packageName.contains("com.mbcorp")  ||   packageName.contains("com.mbbank.biz.prod")){
//            || packageName.contains("com.vnpay.bidv") || packageName.contains("com.mbmobile") || packageName.contains("org.telegram.messenger")) {
//            Log.d(TAG, "packagename $packageName include in list to listener => start process")
            val isGroupSummary = NotificationCompat.isGroupSummary(notification.notification)
            if (isGroupSummary) {
                return
            }
            val extras = notification.notification.extras

            val intent = Intent(NotificationConstants.INTENT_NOTIFY)
            intent.putExtra(NotificationConstants.PACKAGE_NAME, packageName)

            if (extras != null) {
                val title = extras.getCharSequence(Notification.EXTRA_TITLE)
                val text = extras.getCharSequence(Notification.EXTRA_TEXT)
                val content = extractNotificationContent(extras, title, text)
                val isOtpNotification = content.contains("OTP", ignoreCase = true) ||
                    packageName.contains(AppConstant.MB_ONLINE_OTP_PACKAGE)

                Log.d("dongnd1", "notification title : $title")
                Log.d("dongnd1", "notification text : $text")
                Log.d("dongnd1", "notification content : $content")
                if(contentTemp == content){
                    return
                }
                contentTemp = content
                val modifiyedUniq = notification.key + content
                intent.putExtra(NotificationConstants.ID, modifiyedUniq)
                intent.putExtra(NotificationConstants.NOTIFICATION_TITLE, title?.toString())
                intent.putExtra(NotificationConstants.NOTIFICATION_CONTENT, content)
                if (isOtpNotification) {
                    sendBroadcast(intent)
                    cancelNotification(notification.key)
                    return
                }
                if(packageName.contains(AppConstant.MB_BANK_PACKAGE) && !title?.equals("Thông báo biến động số dư")!!){
                    return
                }
                if(packageName.contains(AppConstant.MB_BANK_BIZ_PACKAGE) && !title?.equals("Thông báo biến động số dư")!!){
                    return
                }
                if(packageName.contains(AppConstant.MSB_PACKAGE) && !title?.equals("Thông báo biến động số dư")!!){
                    return
                }
            }
            sendBroadcast(intent)
            cancelNotification(notification.key)
        } else {
            Log.d(TAG, "packagename $packageName not include in list to listener")
        }

    }

    private fun extractNotificationContent(
        extras: Bundle,
        title: CharSequence?,
        text: CharSequence?
    ): String {
        val candidates = mutableListOf<String>()

        text?.toString()?.takeIf { it.isNotBlank() }?.let(candidates::add)
        extras.getCharSequence(Notification.EXTRA_BIG_TEXT)
            ?.toString()
            ?.takeIf { it.isNotBlank() }
            ?.let(candidates::add)
        extras.getCharSequenceArray(Notification.EXTRA_TEXT_LINES)
            ?.map { it.toString() }
            ?.filter { it.isNotBlank() }
            ?.joinToString("\n")
            ?.takeIf { it.isNotBlank() }
            ?.let(candidates::add)

        return candidates.maxByOrNull { it.length }
            ?: title?.toString().orEmpty()
    }

    override fun onStartCommand(intent: Intent?, flags: Int, startId: Int): Int {
        return START_STICKY
    }

    override fun onDestroy() {
        super.onDestroy()
        Log.d("NotificationListener", "Service destroyed")
        // Restart service by sending broadcast
//        val broadcastIntent = Intent(this, RestartServiceReceiver::class.java)
//        sendBroadcast(broadcastIntent)
    }
}
