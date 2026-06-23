package com.piashcse.hilt_mvvm_compose_movie.data.repository.room.entity

import androidx.room.ColumnInfo
import androidx.room.Entity
import androidx.room.PrimaryKey

@Entity(tableName = "message_table")
data class Message(
    @PrimaryKey() val id: Long,
    @ColumnInfo(name = "package_name") val packageName: String,
    @ColumnInfo(name = "message") val message: String,
    @ColumnInfo(name = "status") val status: Int,
    @ColumnInfo(name = "response") val response : String
)