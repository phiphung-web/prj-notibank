package com.piashcse.hilt_mvvm_compose_movie.data.model


import com.google.gson.annotations.SerializedName

data class BaseModel(
    @SerializedName("page")
    val page: Int,
    @SerializedName("total_pages")
    val totalPages: Int,
    @SerializedName("total_results")
    val totalResults: Int
)