package com.piashcse.hilt_mvvm_compose_movie.data.repository.room

import com.piashcse.hilt_mvvm_compose_movie.data.repository.room.entity.Message

class LocalMessageRepository(private val messageDao: MessageDao) {

    suspend fun insertMessage(message: Message): Long {
        return messageDao.insertMessage(message)
    }

    suspend fun updateMessage(message: Message) {
        messageDao.updateMessage(message)
    }

    suspend fun deleteMessage(message: Message) {
        messageDao.deleteMessage(message)
    }

    suspend fun deleteAllMessage() {
        messageDao.deleteAllMessage()
    }

    suspend fun getMessageById(id: Long): Message? {
        return messageDao.getMessageById(id)
    }

    suspend fun getAllMessages(): List<Message> {
        return messageDao.getAllMessages()
    }
}