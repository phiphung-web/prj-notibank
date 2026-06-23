package com.piashcse.hilt_mvvm_compose_movie.di

import android.content.Context
import androidx.room.Room
import com.piashcse.hilt_mvvm_compose_movie.data.datasource.remote.ApiService
import com.piashcse.hilt_mvvm_compose_movie.data.repository.api.NetworkRepository
import com.piashcse.hilt_mvvm_compose_movie.data.repository.room.LocalMessageDatabase
import com.piashcse.hilt_mvvm_compose_movie.data.repository.room.LocalMessageRepository
import com.piashcse.hilt_mvvm_compose_movie.data.repository.sharepreferences.SharedPreferencesRepository
import dagger.Module
import dagger.Provides
import dagger.hilt.InstallIn
import dagger.hilt.android.qualifiers.ApplicationContext
import dagger.hilt.components.SingletonComponent
import javax.inject.Singleton

@Module
@InstallIn(SingletonComponent::class)
object RepositoryModule {

    @Singleton
    @Provides
    fun provideMovieRepository(
        apiService: ApiService,
    ): NetworkRepository {
        return NetworkRepository(
            apiService
        )
    }

    @Singleton
    @Provides
    fun provideLocalDatabase(@ApplicationContext appContext: Context): LocalMessageDatabase {
        return Room.databaseBuilder(
            appContext,
            LocalMessageDatabase::class.java,
            "local_message_database"
        ).build()
    }

    @Singleton
    @Provides
    fun provideLocalMessageRepository(
        database: LocalMessageDatabase
    ): LocalMessageRepository {
        return LocalMessageRepository(database.messageDao())
    }

    @Singleton
    @Provides
    fun provideSharePreferencesRepository(
        @ApplicationContext appContext: Context
    ): SharedPreferencesRepository {
        return SharedPreferencesRepository(appContext)
    }

}