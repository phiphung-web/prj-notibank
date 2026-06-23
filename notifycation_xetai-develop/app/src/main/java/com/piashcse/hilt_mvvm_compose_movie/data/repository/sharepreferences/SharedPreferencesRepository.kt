package com.piashcse.hilt_mvvm_compose_movie.data.repository.sharepreferences

import android.content.Context

class SharedPreferencesRepository(context: Context) {

    private val sharedPreferences = context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
    private val editor = sharedPreferences.edit()

    companion object {
        private const val PREFS_NAME = "MyAppPreferences"
        private const val KEY_TOKEN = "key_token"
        private const val KEY_USER_NAME = "key_username"
    }

    fun setToken(name: String) {
        editor.putString(KEY_TOKEN, name).apply()
    }

    fun getToken(): String? {
        return sharedPreferences.getString(KEY_TOKEN, null)
    }


    fun setUserName(name: String) {
        editor.putString(KEY_USER_NAME, name).apply()
    }

    fun getUserName(): String? {
        return sharedPreferences.getString(KEY_USER_NAME, null)
    }
    fun remove(key: String) {
        editor.remove(key).apply()
    }

    // Xóa tất cả dữ liệu
    fun clear() {
        editor.clear().apply()
    }
}