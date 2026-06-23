package com.piashcse.hilt_mvvm_compose_movie.data.model

import com.google.gson.annotations.SerializedName

data class RechargeResponse(
    @SerializedName("status")
    val status: Int,
    @SerializedName("message")
    val message: String,
    @SerializedName("data")
    val data: Data?
) {
    companion object Factory {
        fun createErrorRechargeResponseWithMessage(message: String): RechargeResponse {
            return RechargeResponse(status = -1, message = message, data = null)
        }
    }
}

data class Data(
    @SerializedName("id")
    val id: Int?,
    @SerializedName("type")
    val type: Int?,
    @SerializedName("id_pay_transaction")
    val idPayTransaction: String?,
    @SerializedName("money")
    val money: String?,
    @SerializedName("phone")
    val phone: String?,
    @SerializedName("type_bank")
    val typeBank: String?,
    @SerializedName("content_bank")
    val contentBank: String?,
    @SerializedName("result")
    val result: String?,
    @SerializedName("error")
    val error: Boolean?,
    @SerializedName("status")
    val status: Int?
)