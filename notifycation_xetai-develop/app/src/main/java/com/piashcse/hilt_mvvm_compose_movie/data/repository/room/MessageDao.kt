package com.piashcse.hilt_mvvm_compose_movie.data.repository.room

import androidx.room.*
import com.piashcse.hilt_mvvm_compose_movie.data.repository.room.entity.Message

@Dao
interface MessageDao {
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    fun insertMessage(message: Message): Long

    @Update
    fun updateMessage(message: Message)

    @Delete
    fun deleteMessage(message: Message)

    @Query("DELETE FROM message_table")
    fun deleteAllMessage()

    @Query("SELECT * FROM message_table WHERE id = :id")
    fun getMessageById(id: Long): Message?

    @Query("SELECT * FROM message_table")
    fun getAllMessages(): List<Message>
}