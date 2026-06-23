package com.piashcse.hilt_mvvm_compose_movie.data.model

data class NotificationData(
    val timeCreated: Long,
    val time: String?,
    val sender: String,
    val phone: String,
    val price: String,
    val message: String,
    val status: Int,
    val typeBank: Int,
    val note: String?,
    val monthReceiver: String?,
    val accountBalance: String
)