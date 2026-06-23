package com.piashcse.hilt_mvvm_compose_movie.data.model

import com.google.gson.annotations.SerializedName

data class LoginResponse(
    @SerializedName("status")
    val status: Int,
    @SerializedName("message")
    val message: String,
    @SerializedName("data")
    val data: UserData?
) {
    companion object Factory {
        fun createErrorLoginResponseWithMessage(message: String): LoginResponse {
            return LoginResponse(status = -1, message = message, data = null)
        }
    }
}

data class UserData(
    @SerializedName("id")
    val id: String,
    @SerializedName("token")
    val token: String,
    @SerializedName("username")
    val username: String,
    @SerializedName("email")
    val email: String,
    @SerializedName("phone")
    val phone: String
)