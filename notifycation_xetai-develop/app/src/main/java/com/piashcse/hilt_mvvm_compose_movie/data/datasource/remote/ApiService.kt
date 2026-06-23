package com.piashcse.hilt_mvvm_compose_movie.data.datasource.remote

import com.piashcse.hilt_mvvm_compose_movie.data.model.LoginResponse
import com.piashcse.hilt_mvvm_compose_movie.data.model.RechargeResponse
import retrofit2.http.Field
import retrofit2.http.FormUrlEncoded
import retrofit2.http.Header
import retrofit2.http.Headers
import retrofit2.http.POST

interface ApiService {
    @Headers("Content-Type: application/x-www-form-urlencoded; charset=utf-8")
    @FormUrlEncoded
    @POST("api/auth/login")
    suspend fun login(
        @Field("username") username: String,
        @Field("password") password: String
    ): LoginResponse

    @Headers("Content-Type: application/x-www-form-urlencoded; charset=utf-8")
    @FormUrlEncoded
    @POST("/api/pay-transaction/recharge")
    suspend fun postRecharge(
        @Field("id_pay_transaction") idPay: Long,
        @Field("phone") phone: String,
        @Field("money") money: String,
        @Field("type_bank") typeBank: Int,
        @Field("content_bank") msg: String,
        @Field("account_balance") accountBalance: String,
        @Field("user_id") user_id: Int = 2,
        @Header("Authorization") authHeader: String
    ): RechargeResponse

    @Headers("Content-Type: application/x-www-form-urlencoded; charset=utf-8")
    @FormUrlEncoded
    @POST("/api/pay-transaction/minus-system")
    suspend fun postSubRecharge(
        @Field("id_pay_transaction") idPay: Long,
        @Field("money") money: String,
        @Field("type_bank") typeBank: Int,
        @Field("content_bank") msg: String,
        @Field("account_balance") accountBalance: String,
        @Field("user_id") user_id: Int = 2,
        @Header("Authorization") authHeader: String
    ): RechargeResponse
}