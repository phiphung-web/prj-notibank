package com.piashcse.hilt_mvvm_compose_movie.ui.screens.login

import android.content.Intent
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.gestures.detectTapGestures
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.wrapContentSize
import androidx.compose.foundation.text.KeyboardActions
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Visibility
import androidx.compose.material.icons.filled.VisibilityOff
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.material3.TopAppBar
import androidx.compose.material3.TopAppBarDefaults
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.remember
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.input.pointer.pointerInput
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.platform.LocalFocusManager
import androidx.compose.ui.platform.LocalSoftwareKeyboardController
import androidx.compose.ui.res.colorResource
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.text.TextStyle
import androidx.compose.ui.text.font.FontStyle
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.ImeAction
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.input.VisualTransformation
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.hilt.navigation.compose.hiltViewModel
import androidx.navigation.NavController
import com.piashcse.hilt_mvvm_compose_movie.R
import com.piashcse.hilt_mvvm_compose_movie.ui.screens.activity.MainActivity
import com.piashcse.hilt_mvvm_compose_movie.utils.network.DataState

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun LoginScreen(navController: NavController, viewModel: LoginViewModel = hiltViewModel()) {

    val email by viewModel.email
    val password by viewModel.password
    val isPasswordVisible by viewModel.isPasswordVisible
    val errorMessage by viewModel.errorMessage
    val context = LocalContext.current

    val LightBlue = Color(0xFF4A90E2)
    val DarkBlue = Color(0xFF003399)

    val loginState = remember {
        viewModel.loginState
    }
    val localFocusManager = LocalFocusManager.current

    val keyboardController = LocalSoftwareKeyboardController.current

    Scaffold(
        topBar = {
            TopAppBar(colors = TopAppBarDefaults.topAppBarColors(
                containerColor = DarkBlue,
                titleContentColor = MaterialTheme.colorScheme.primary,
            ), title = {
            })
        },
    ) { innerPadding ->
        Column(
            modifier = Modifier
                .padding(innerPadding)
                .pointerInput(Unit) {
                    detectTapGestures(onTap = {
                        keyboardController?.hide()
                        localFocusManager.clearFocus()
                    })
                },
            verticalArrangement = Arrangement.spacedBy(16.dp),
        ) {
            Box(
                modifier = Modifier
                    .fillMaxSize()
                    .background(Brush.verticalGradient(colors = listOf(DarkBlue, LightBlue)))
            ) {
                Column(
                    modifier = Modifier
                        .fillMaxSize()
                        .padding(16.dp),
                    verticalArrangement = Arrangement.Center,
                    horizontalAlignment = Alignment.CenterHorizontally
                ) {
                    Image(
                        painter = painterResource(id = R.drawable.ic_splash),
                        contentDescription = "App Logo",
                        modifier = Modifier
                            .size(100.dp)
                            .padding(bottom = 24.dp)
                    )

                    Text(
                        text = context.getString(R.string.app_name),
                        fontSize = 24.sp,
                        fontWeight = FontWeight.Bold,
                        color = Color.White,
                        textAlign = TextAlign.Center
                    )

                    Spacer(modifier = Modifier.height(16.dp))

                    OutlinedTextField(
                        value = email,
                        onValueChange = { viewModel.onEmailChange(it) },
                        label = { Text(context.getString(R.string.user_name), color = Color.White) },
                        isError = errorMessage.contains("Username"),
                        modifier = Modifier
                            .fillMaxWidth()
                            .background(Color.Transparent)
                            .clip(MaterialTheme.shapes.small),
                        textStyle = TextStyle(color = Color.White, fontSize =  16.sp)
                    )

                    Spacer(modifier = Modifier.height(8.dp))

                    OutlinedTextField(
                        value = password,
                        onValueChange = { viewModel.onPasswordChange(it) },
                        label = { Text(context.getString(R.string.pass), color = Color.White) },
                        isError = errorMessage.contains("Password"),
                        textStyle = TextStyle(color = Color.White,fontSize =  16.sp),
                        visualTransformation = if (isPasswordVisible) VisualTransformation.None else PasswordVisualTransformation(),
                        trailingIcon = {
                            val image =
                                if (isPasswordVisible) Icons.Filled.Visibility else Icons.Filled.VisibilityOff
                            IconButton(onClick = { viewModel.togglePasswordVisibility() }) {
                                Icon(
                                    imageVector = image,
                                    contentDescription = "Toggle password visibility"
                                )
                            }
                        },
                        keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Password,imeAction =  ImeAction.Done),
                        modifier = Modifier
                            .fillMaxWidth()
                            .background(Color.Transparent)
                            .clip(MaterialTheme.shapes.small)
                            .pointerInput(Unit) {
                                detectTapGestures(onTap = {
                                    localFocusManager.clearFocus()
                                })
                            },

                        keyboardActions = KeyboardActions(
                            onDone = {  viewModel.validateLogin() }
                        ),
                        )

                    Spacer(modifier = Modifier.height(16.dp))

                    if (errorMessage.isNotEmpty()) {
                        Text(
                            text = errorMessage,
                            color = MaterialTheme.colorScheme.error,
                            style = MaterialTheme.typography.labelMedium
                        )
                    }

                    Spacer(modifier = Modifier.height(16.dp))

                    Button(
                        onClick = { viewModel.validateLogin() },
                        colors = ButtonDefaults.buttonColors(),
                        modifier = Modifier
                            .fillMaxWidth()
                            .clip(MaterialTheme.shapes.large)
                    ) {
                        Text(context.getString(R.string.login_btn), color = Color.White, style = MaterialTheme.typography.labelMedium,fontSize =  20.sp)
                    }
                }

                when (loginState.value) {
                    is DataState.Loading -> Box(
                        modifier = Modifier
                            .fillMaxSize()
                            .wrapContentSize(Alignment.Center),
                        contentAlignment = Alignment.Center
                    ) {
                        CircularProgressIndicator(color = Color.White)
                    }

                    is DataState.Error -> {
                        AlertDialog(
                            onDismissRequest = { },
                            title = { Text("Error") },
                            text = { Text((loginState.value as DataState.Error).message) },
                            confirmButton = {
                                Button(onClick = {
                                    viewModel.resetStateLogin()
                                }) {
                                    Text("OK")
                                }
                            },
                            modifier = Modifier.padding(16.dp)
                        )
                    }

                    is DataState.Success -> {
                        context.startActivity(Intent(context, MainActivity::class.java))
                    }

                    else -> Box() {

                    }
                }
            }
        }
    }

}
