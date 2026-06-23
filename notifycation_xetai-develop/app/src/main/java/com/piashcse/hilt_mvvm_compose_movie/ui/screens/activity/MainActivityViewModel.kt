package com.piashcse.hilt_mvvm_compose_movie.ui.screens.activity

import android.util.Log
import androidx.compose.runtime.mutableStateOf
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.piashcse.hilt_mvvm_compose_movie.data.model.NotificationData
import com.piashcse.hilt_mvvm_compose_movie.data.model.RechargeResponse
import com.piashcse.hilt_mvvm_compose_movie.data.model.StatusListener
import com.piashcse.hilt_mvvm_compose_movie.data.model.ViewState
import com.piashcse.hilt_mvvm_compose_movie.data.repository.api.NetworkRepository
import com.piashcse.hilt_mvvm_compose_movie.data.repository.room.LocalMessageRepository
import com.piashcse.hilt_mvvm_compose_movie.data.repository.room.entity.Message
import com.piashcse.hilt_mvvm_compose_movie.data.repository.sharepreferences.SharedPreferencesRepository
import com.piashcse.hilt_mvvm_compose_movie.utils.Utils
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.delay
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import javax.inject.Inject

@HiltViewModel
class MainActivityViewModel @Inject constructor(
    private val localMessageRepository: LocalMessageRepository,
    private val sharePreferencesRepositoryFactory: SharedPreferencesRepository,
    private val networkRepository: NetworkRepository
) :
    ViewModel() {
    private val _isLoading = MutableStateFlow(true)
    val isLoading get() = _isLoading.asStateFlow()

    private val _hasPermissionNotificationGranted = mutableStateOf(false)
    val hasPermissionNotificationGranted get() = _hasPermissionNotificationGranted

    private val _statusListening = mutableStateOf(StatusListener.STOP)
    val statusListening get() = _statusListening

    private val _listNotificationLocal = mutableStateOf<List<Message>>(listOf())
    val listNotificationLocal get() = _listNotificationLocal

    private   val TYPE_SMS = 1
    private   val TYPE_NOTYFY = 2

    init {
        viewModelScope.launch {
            delay(500)
            _isLoading.value = false
            loadLocalMessageWhenInit()
        }
    }

    fun setPermissionNotificationStatus(isGranted: Boolean) {
        _hasPermissionNotificationGranted.value = isGranted
    }

    fun setStatusListening(status: StatusListener) {
        _statusListening.value = status
    }

    fun addNotification(notification: HashMap<String, Any?>) {
        if (notification["content"] == null) return
// check trung id
        if (_listNotificationLocal.value.map { e -> e.id }.toList()
                .contains(notification["id"])
        ) {
            return
        }
// check trung content
        if (_listNotificationLocal.value.map { e -> e.message }.toList()
                .contains(notification["content"])
        ) {
            return
        }
        val contentA  = ArrayList<Message>()
        val messages = Message(
            id = notification["timeCreated"].toString().toLong(),
            message = notification["content"].toString(),
            packageName = notification["packageName"].toString(),
            status = ViewState.StatusProcessNotification.DOING,
            response = ""
        )
        contentA.add(messages)
        _listNotificationLocal.value += contentA
        viewModelScope.launch(Dispatchers.IO) {
            saveNotificationToLocalDatabase(notification = notification)
            sendDataNotificationToServer(notification = notification, type = TYPE_NOTYFY)
        }
    }




    fun addSMS(notification: HashMap<String, Any?>) {
        if (notification["content"] == null) return
// check trung id
        if (_listNotificationLocal.value.map { e -> e.id }.toList()
                .contains(notification["id"])
        ) {
            return
        }
// check trung content
//        if (_listNotificationLocal.value.map { e -> e.message }.toList()
//                .contains(notification["content"])
//        ) {
//            return
//        }
        val contentA  = ArrayList<Message>()
        val messages = Message(
            id = notification["timeCreated"].toString().toLong(),
            message = notification["content"].toString(),
            packageName = notification["packageName"].toString(),
            status = ViewState.StatusProcessNotification.DOING,
            response = ""
        )
        contentA.add(messages)
        _listNotificationLocal.value += contentA
        viewModelScope.launch(Dispatchers.IO) {
            saveNotificationToLocalDatabase(notification = notification)
            sendDataNotificationToServer(notification = notification,TYPE_SMS)
        }
    }

    fun logout() {
        sharePreferencesRepositoryFactory.setToken("")
    }

    fun deleteMessage() {
        viewModelScope.launch(Dispatchers.IO) {
            localMessageRepository.deleteAllMessage()
            val listLocalMessage = localMessageRepository.getAllMessages()
            withContext(Dispatchers.Main) {
                _listNotificationLocal.value = listLocalMessage
            }
        }
    }

    private fun saveNotificationToLocalDatabase(notification: HashMap<String, Any?>) {
        viewModelScope.launch(Dispatchers.IO) {
            val message = Message(
                id = notification["timeCreated"].toString().toLong(),
                message = notification["content"].toString(),
                packageName = notification["packageName"].toString(),
                status = ViewState.StatusProcessNotification.DOING,
                response =""
            )
            localMessageRepository.insertMessage(message)
            val listLocalMessage = localMessageRepository.getAllMessages()
            withContext(Dispatchers.Main) {
                _listNotificationLocal.value = listLocalMessage.reversed()
            }
        }
    }

    private fun sendDataNotificationToServer(notification: HashMap<String, Any?>,type : Int) {   // type =1  : sms , type = 2 : notify
        viewModelScope.launch(Dispatchers.IO) {
            try {
                val currentTime = notification["timeCreated"].toString().toLong()

                var notificationData: NotificationData? = null
                if(type == TYPE_NOTYFY) {
                    notificationData = Utils.getNotifyReceiver(
                        message = notification["content"].toString(),
                        sender = ViewState.BankValue.getBankValueFromPackageName(notification["packageName"].toString()),
                        time_created = currentTime
                    )
                }else  if(type == TYPE_SMS){
                    notificationData = Utils.getSMSReceiver(
                        message = notification["content"].toString(),
                        sender = ViewState.BankValue.getBankValueFromPackageName(notification["packageName"].toString()),
                        time_created = currentTime)
                }

                val resultRequest: RechargeResponse = if (notificationData!!.price.contains('-')) {
                    networkRepository.sendNotificationDataForSubRecharge(
                        notificationData = notificationData,
                        token = sharePreferencesRepositoryFactory.getToken() ?: ""
                    )
                } else {
                    networkRepository.sendNotificationDataForRecharge(
                        notificationData = notificationData,
                        token = sharePreferencesRepositoryFactory.getToken() ?: ""
                    )
                }
                val isSuccessRequestUpdate =
                    resultRequest.data != null && (resultRequest.data.error== false)
                updateLocalDataAfterSendToServer(
                    isSuccess = isSuccessRequestUpdate,
                    idPay = notification["timeCreated"].toString().toLong(),
                    notification = notification,
                    resultApi = resultRequest.message

                )

                Log.d("dongnd1", "Result send data notification to backend: $resultRequest")
            } catch (e: Exception) {
                Log.d("dongnd1", "Error when send data notification to backend: $e")
                updateLocalDataAfterSendToServer(
                    isSuccess = false,
                    idPay = notification["timeCreated"].toString().toLong(),
                    notification = notification,
                    resultApi = "Nạp BID HD không thành công, Lỗi server liên hệ với Việt Anh"
                )
            }


        }
    }

    private fun updateLocalDataAfterSendToServer(
        idPay: Long,
        isSuccess: Boolean,
        notification: HashMap<String, Any?>,
        resultApi : String
    ) {
        // update local database
        viewModelScope.launch(Dispatchers.IO) {
            val message = Message(
                id = idPay,
                message = notification["content"].toString(),
                packageName = notification["packageName"].toString(),
                status = if (isSuccess) ViewState.StatusProcessNotification.SUCCESS else ViewState.StatusProcessNotification.FAIL,
                response = resultApi
            )
            localMessageRepository.updateMessage(message)
            val listLocalMessage = localMessageRepository.getAllMessages()
            withContext(Dispatchers.Main) {
                _listNotificationLocal.value = listLocalMessage
            }
        }

    }


    private fun loadLocalMessageWhenInit() {
        viewModelScope.launch(Dispatchers.IO) {
            val listLocalMessage = localMessageRepository.getAllMessages()
            withContext(Dispatchers.Main) {
                _listNotificationLocal.value = listLocalMessage
            }
        }
    }
}
