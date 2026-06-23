package com.piashcse.hilt_mvvm_compose_movie.navigation

import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Home
import androidx.compose.material3.Icon
import androidx.compose.runtime.Composable

sealed class Screen(
    val route: String,
    val title: String = "",
    val navIcon: (@Composable () -> Unit) = {
        Icon(
            Icons.Filled.Home, contentDescription = "home"
        )
    },
    val objectName: String = "",
    val objectPath: String = ""
) {
    object Login : Screen("login_screen")
    object Home : Screen("home_screen")
}