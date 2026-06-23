package com.piashcse.hilt_mvvm_compose_movie.data.service

import android.annotation.SuppressLint
import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.provider.Telephony
import android.util.Log
import com.piashcse.hilt_mvvm_compose_movie.data.model.ViewState
import com.piashcse.hilt_mvvm_compose_movie.utils.AppConstant.MB_BANK_PACKAGE
import com.piashcse.hilt_mvvm_compose_movie.utils.NotificationConstants


@SuppressLint("OverrideAbstract")
class SMSReceiver : BroadcastReceiver() {
    override fun onReceive(context: Context, intent: Intent) {
        if (intent.action == Telephony.Sms.Intents.SMS_RECEIVED_ACTION) {
            val messages = Telephony.Sms.Intents.getMessagesFromIntent(intent)
            val firstMessage = messages.firstOrNull() ?: return
            val sender = firstMessage.originatingAddress ?: return
            val body = messages.joinToString(separator = "") { it.messageBody ?: "" }
            val time = firstMessage.timestampMillis

            handleSMS(context, body, sender, time, false)
        }
    }
    private var contentTemp : CharSequence? = null

    private fun isOtpMessage(message: String): Boolean {
        val otpAfterKeyword = """(?:ma\s+(?:so\s+xac\s+thuc\s+)?OTP|OTP)\s*(?:la)?\s*:?\s*[0-9][0-9\s-]{3,12}[0-9]"""
        val otpBeforeKeyword = """[0-9][0-9\s-]{3,12}[0-9]\s+(?:la\s+)?ma\s+xac\s+thuc\s+OTP"""
        return listOf(otpAfterKeyword, otpBeforeKeyword)
            .any { it.toRegex(RegexOption.IGNORE_CASE).containsMatchIn(message) }
    }

    @SuppressLint("LogNotTimber")
    private fun handleSMS(context: Context, message: String, sender: String, time_created: Long, isRemoved: Boolean) {
        Log.d("dongnd1", "sms incomming : $sender")
        if(sender == ViewState.BankValue.MBBANK || sender == ViewState.BankValue.GOOGLE || sender == ViewState.BankValue.MISA || isOtpMessage(message)){
            val intent = Intent(NotificationConstants.INTENT_SMS)
            intent.putExtra(NotificationConstants.PACKAGE_NAME, MB_BANK_PACKAGE)
            val title = "Bank"
            val text = message

            Log.d("dongnd1", "notification text : $text")
            if(contentTemp == text){
                return
            }
            contentTemp = text
            val modifiyedUniq = time_created
            intent.putExtra(NotificationConstants.ID, modifiyedUniq.toString())
            intent.putExtra(NotificationConstants.NOTIFICATION_TITLE, title)
            intent.putExtra(NotificationConstants.NOTIFICATION_CONTENT, text)
            context.sendBroadcast(intent)
        }

    }

}
