package com.piashcse.hilt_mvvm_compose_movie.data.repository.room

import androidx.room.Database
import androidx.room.RoomDatabase
import com.piashcse.hilt_mvvm_compose_movie.data.repository.room.entity.Message

@Database(entities = [Message::class], version = 2, exportSchema = false)
abstract class LocalMessageDatabase : RoomDatabase() {
    abstract fun messageDao(): MessageDao
}