package com.piashcse.hilt_mvvm_compose_movie.data.repository.api

import com.piashcse.hilt_mvvm_compose_movie.data.datasource.remote.ApiService
import com.piashcse.hilt_mvvm_compose_movie.data.model.LoginResponse
import com.piashcse.hilt_mvvm_compose_movie.data.model.NotificationData
import com.piashcse.hilt_mvvm_compose_movie.data.model.RechargeResponse
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import javax.inject.Inject

class NetworkRepository @Inject constructor(
    private val apiService: ApiService
) : NetworkRepositoryInterface {

    override suspend fun loginUser(username: String, password: String): LoginResponse {
        return withContext(Dispatchers.IO) {
            try {
                val response = apiService.login(username, password)
                response
            } catch (e: Exception) {
                e.printStackTrace()
                LoginResponse.createErrorLoginResponseWithMessage(e.message ?: "Unknown Error")
            }
        }
    }

    override suspend fun sendNotificationDataForRecharge(
        notificationData: NotificationData,
        token: String
    ): RechargeResponse {
        return withContext(Dispatchers.IO) {
            try {
                val response = apiService.postRecharge(
                    idPay = notificationData.timeCreated,
                    phone = notificationData.phone,
                    money = notificationData.price,
                    typeBank = notificationData.typeBank,
                    msg = notificationData.message,
                    accountBalance = notificationData.accountBalance,
                    user_id = 1,//1 of mrDung, 2 mrHuy
                    authHeader = "Bearer $token"
                )
                response
            } catch (e: Exception) {
                e.printStackTrace()
                RechargeResponse.createErrorRechargeResponseWithMessage(message = e.message ?: "")
            }
        }
    }

    override suspend fun sendNotificationDataForSubRecharge(
        notificationData: NotificationData,
        token: String
    ): RechargeResponse {
        return withContext(Dispatchers.IO) {
            try {
                val response = apiService.postSubRecharge(
                    idPay = notificationData.timeCreated,
                    money = notificationData.price,
                    typeBank = notificationData.typeBank,
                    msg = notificationData.message,
                    accountBalance = notificationData.accountBalance,
                    user_id = 1,//1 of mrDung, 2 mrHuy
                    authHeader = "Bearer $token"
                )
                response
            } catch (e: Exception) {
                e.printStackTrace()
                RechargeResponse.createErrorRechargeResponseWithMessage(message = e.message ?: "")
            }
        }
    }

}
