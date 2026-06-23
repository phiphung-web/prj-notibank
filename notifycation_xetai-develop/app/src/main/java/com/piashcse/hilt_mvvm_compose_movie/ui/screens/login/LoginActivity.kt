package com.piashcse.hilt_mvvm_compose_movie.ui.screens.login

import android.content.Intent
import android.os.Bundle
import androidx.activity.compose.setContent
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.navigation.compose.rememberNavController
import com.piashcse.hilt_mvvm_compose_movie.ui.screens.activity.MainActivity
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class LoginActivity : AppCompatActivity() {

    private val loginViewModel: LoginViewModel by viewModels()

    @OptIn(ExperimentalMaterial3Api::class)
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        if (loginViewModel.hasLoginBefore()) {
            startActivity(Intent(this, MainActivity::class.java))
        } else {
            setContent {
                val navController = rememberNavController()
                LoginScreen(navController, viewModel = loginViewModel)
            }
        }


    }
}