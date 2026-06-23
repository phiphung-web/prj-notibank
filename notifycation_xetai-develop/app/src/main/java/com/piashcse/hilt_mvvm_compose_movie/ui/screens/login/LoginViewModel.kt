package com.piashcse.hilt_mvvm_compose_movie.ui.screens.login

import androidx.compose.runtime.State
import androidx.compose.runtime.mutableStateOf
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.piashcse.hilt_mvvm_compose_movie.data.model.LoginResponse
import com.piashcse.hilt_mvvm_compose_movie.data.repository.api.NetworkRepository
import com.piashcse.hilt_mvvm_compose_movie.data.repository.sharepreferences.SharedPreferencesRepository
import com.piashcse.hilt_mvvm_compose_movie.utils.network.DataState
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class LoginViewModel @Inject constructor(
    private val networkRepository: NetworkRepository,
    private val sharedPreferencesRepository: SharedPreferencesRepository
) :
    ViewModel() {
    private val _email = mutableStateOf("")
    val email: State<String> = _email

    private val _password = mutableStateOf("")
    val password: State<String> = _password

    private val _isPasswordVisible = mutableStateOf(false)
    val isPasswordVisible: State<Boolean> = _isPasswordVisible

    private val _errorMessageValidate = mutableStateOf("")
    val errorMessage: State<String> = _errorMessageValidate

    private val _loginState = mutableStateOf<DataState<LoginResponse>>(DataState.Init)
    val loginState get() = _loginState

    fun onEmailChange(newEmail: String) {
        _email.value = newEmail
    }

    fun onPasswordChange(newPassword: String) {
        _password.value = newPassword
    }

    fun togglePasswordVisibility() {
        _isPasswordVisible.value = !_isPasswordVisible.value
    }

    fun validateLogin() {
        if (_email.value.isEmpty() || _password.value.isEmpty()) {
            _errorMessageValidate.value =  "Bạn kiểm tra lại thông tin đăng nhập"
        } else {
            _errorMessageValidate.value = ""
            login()
        }
    }

    fun login() {
        viewModelScope.launch(Dispatchers.IO) {
            _loginState.value = DataState.Loading
            val response =
                networkRepository.loginUser(username = email.value.trim(), password = password.value.trim())
            if (response.data != null) {
                _loginState.value = DataState.Success(response)
                sharedPreferencesRepository.setToken(response.data.token)
                sharedPreferencesRepository.setUserName(email.value.trim())
                println("Login successful: ${response.message}")
            } else {
                _loginState.value = DataState.Error(response?.message ?: "Unknown Error")
            }
        }
    }

    fun resetStateLogin() {
        _loginState.value = DataState.Init
    }

    fun hasLoginBefore() : Boolean {
        return !sharedPreferencesRepository.getToken().isNullOrEmpty()
    }
}