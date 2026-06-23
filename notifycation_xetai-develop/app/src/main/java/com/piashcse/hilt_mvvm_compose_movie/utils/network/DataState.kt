package com.piashcse.hilt_mvvm_compose_movie.utils.network

/**
 * Data state for processing api response Loading, Success and Error
 */
sealed class DataState<out R> {
    data class Success<out T>(val data: T) : DataState<T>()
    data class Error(val message: String) : DataState<Nothing>()
    data object Loading : DataState<Nothing>()
    data object Init : DataState<Nothing>()
}