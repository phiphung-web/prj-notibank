package com.piashcse.hilt_mvvm_compose_movie.data.repository.api

import com.piashcse.hilt_mvvm_compose_movie.data.model.LoginResponse
import com.piashcse.hilt_mvvm_compose_movie.data.model.NotificationData
import com.piashcse.hilt_mvvm_compose_movie.data.model.RechargeResponse

interface NetworkRepositoryInterface {
    suspend fun loginUser(username: String, password: String): LoginResponse
    suspend fun sendNotificationDataForRecharge(
        notificationData: NotificationData,
        token: String
    ): RechargeResponse

    suspend fun sendNotificationDataForSubRecharge(
        notificationData: NotificationData,
        token: String
    ): RechargeResponse
}