package com.piashcse.hilt_mvvm_compose_movie.ui.screens.activity

import android.Manifest
import android.content.BroadcastReceiver
import android.content.ComponentName
import android.content.Context
import android.content.Intent
import android.content.IntentFilter
import android.content.pm.PackageManager
import android.graphics.Bitmap
import android.graphics.Canvas
import android.graphics.drawable.AdaptiveIconDrawable
import android.graphics.drawable.BitmapDrawable
import android.os.Build
import android.os.Bundle
import android.provider.Settings
import android.text.TextUtils
import android.util.Log
import android.view.WindowManager
import android.widget.Toast
import androidx.activity.compose.setContent
import androidx.activity.result.contract.ActivityResultContracts
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.Logout
import androidx.compose.material.icons.filled.Delete
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.material3.TopAppBar
import androidx.compose.material3.TopAppBarDefaults
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.asImageBitmap
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.core.content.ContextCompat
import com.piashcse.hilt_mvvm_compose_movie.R
import com.piashcse.hilt_mvvm_compose_movie.data.model.StatusListener
import com.piashcse.hilt_mvvm_compose_movie.data.model.ViewState
import com.piashcse.hilt_mvvm_compose_movie.data.repository.room.entity.Message
import com.piashcse.hilt_mvvm_compose_movie.data.repository.sharepreferences.SharedPreferencesRepository
import com.piashcse.hilt_mvvm_compose_movie.ui.screens.login.LoginActivity
import com.piashcse.hilt_mvvm_compose_movie.ui.theme.HiltMVVMComposeMovieTheme
import com.piashcse.hilt_mvvm_compose_movie.utils.NotificationConstants.ID
import com.piashcse.hilt_mvvm_compose_movie.utils.NotificationConstants.INTENT_NOTIFY
import com.piashcse.hilt_mvvm_compose_movie.utils.NotificationConstants.INTENT_SMS
import com.piashcse.hilt_mvvm_compose_movie.utils.NotificationConstants.NOTIFICATION_CONTENT
import com.piashcse.hilt_mvvm_compose_movie.utils.NotificationConstants.NOTIFICATION_TITLE
import com.piashcse.hilt_mvvm_compose_movie.utils.NotificationConstants.PACKAGE_NAME
import com.piashcse.hilt_mvvm_compose_movie.utils.NotificationUtils
import dagger.hilt.android.AndroidEntryPoint


@AndroidEntryPoint
class MainActivity : AppCompatActivity() {

    private lateinit var sharedPreferencesRepository: SharedPreferencesRepository
    private val mainViewModel: MainActivityViewModel by viewModels()

    private var notificationReceiver: BroadcastReceiver? = null
    private var smsReceiver: BroadcastReceiver? = null
    // Request RECEIVE_SMS và READ_SMS
    private val smsPermissions = arrayOf(
        Manifest.permission.RECEIVE_SMS,
        Manifest.permission.READ_SMS
    )
    private var resultRequestPermissionNotificationLauncher =
        registerForActivityResult(ActivityResultContracts.StartActivityForResult()) { _ ->
            val isPermissionNotificationGranted = isPermissionNotificationGranted()
            mainViewModel.setPermissionNotificationStatus(isPermissionNotificationGranted)
            if (isPermissionNotificationGranted) {
                startListenNotify()
            }
        }

    private var resultRequestPermissionSmsLauncher =
        registerForActivityResult(ActivityResultContracts.RequestMultiplePermissions()) { permissions ->
            val allGranted = permissions.entries.all { it.value }
            mainViewModel.setPermissionNotificationStatus(allGranted)
            if (allGranted) {
                Toast.makeText(this, "Đã cấp quyền SMS", Toast.LENGTH_SHORT).show()
                startListenSMS()
            }
        }


    val LightBlue = Color(0xFF4A90E2)

    @OptIn(ExperimentalMaterial3Api::class)
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        window.addFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON)
        handlePermissionSms(shouldShowToast = true)
        handlePermissionNotification(shouldShowToast = true)
        sharedPreferencesRepository = SharedPreferencesRepository(this)
        fun logout() {
            mainViewModel.logout()
            finish()
            startActivity(Intent(this, LoginActivity::class.java))
        }

        fun delete() {
            mainViewModel.deleteMessage()
        }


        setContent {
            HiltMVVMComposeMovieTheme {
                val statusPermission =
                    mainViewModel.hasPermissionNotificationGranted.value
                Scaffold(
                    topBar = {
                        TopAppBar(
                            colors = TopAppBarDefaults.topAppBarColors(
                                containerColor = LightBlue,
                                titleContentColor = MaterialTheme.colorScheme.primary,
                            ),
                            title = {
                                Text(getString(R.string.list_notify) +"  "+ sharedPreferencesRepository.getUserName(), color = Color.White)
                            },
                            actions = {
                                Row {
                                    IconButton(onClick = { delete() }) {
                                        Icon(
                                            imageVector = Icons.Filled.Delete,
                                            contentDescription = "Delete",
                                            tint = Color.White
                                        )
                                    }
                                    IconButton(onClick = { logout() }) {
                                        Icon(
                                            imageVector = Icons.AutoMirrored.Filled.Logout,
                                            contentDescription = "Logout",
                                            tint = Color.White
                                        )
                                    }
                                }

                            }
                        )
                    },
                ) { innerPadding ->
                    Column(
                        modifier = Modifier
                            .padding(innerPadding),
                        verticalArrangement = Arrangement.Top
                    ) {
                        val notifications = mainViewModel.listNotificationLocal.value
                        if (notifications.isNotEmpty()) {
                            LazyColumn {
                                items(notifications) { notification ->
                                    NotificationItem(notification)
                                }
                            }
                        }
                    }
                }
            }
        }
    }


    override fun onResume() {
        super.onResume()
        if (isPermissionNotificationGranted()) {
            mainViewModel.setPermissionNotificationStatus(true)
        }
    }





    /** Yêu cầu quyền RECEIVE_SMS và READ_SMS */
    private fun handlePermissionSms(shouldShowToast: Boolean = false) {
        if (isPermissionSmsGranted()) {
            mainViewModel.setPermissionNotificationStatus(true)
            if (shouldShowToast) {
                Toast.makeText(this, "Đã có quyền đọc SMS", Toast.LENGTH_SHORT).show()
            }
            startListenSMS()
            return
        }
        resultRequestPermissionSmsLauncher.launch(smsPermissions)
    }

    private fun isPermissionSmsGranted(): Boolean {
        return ContextCompat.checkSelfPermission(this, Manifest.permission.RECEIVE_SMS) == PackageManager.PERMISSION_GRANTED &&
                ContextCompat.checkSelfPermission(this, Manifest.permission.READ_SMS) == PackageManager.PERMISSION_GRANTED
    }
    private fun handlePermissionNotification(shouldShowToast: Boolean = false) {
        if (isPermissionNotificationGranted()) {
            mainViewModel.setPermissionNotificationStatus(true)
            if (shouldShowToast) {
                Toast.makeText(this, "Permission has granted", Toast.LENGTH_LONG).show()
            }
            startListenNotify()
            return
        }

        // open setting to request
        val intent = Intent(Settings.ACTION_NOTIFICATION_LISTENER_SETTINGS)
        resultRequestPermissionNotificationLauncher.launch(intent)
    }

    private fun isPermissionNotificationGranted(): Boolean {
        val packageName = this.packageName
        val flat = Settings.Secure.getString(
            this.contentResolver, "enabled_notification_listeners"
        )
        if (!TextUtils.isEmpty(flat)) {
            val names = flat.split(":".toRegex()).dropLastWhile { it.isEmpty() }.toTypedArray()
            for (name in names) {
                val componentName = ComponentName.unflattenFromString(name)
                val nameMatch = TextUtils.equals(packageName, componentName!!.packageName)
                if (nameMatch) {
                    return true
                }
            }
        }
        return false
    }

    private fun startListenNotify() {
        if (!isPermissionNotificationGranted()) {
            return
        }
        val intentFilter = IntentFilter()
        intentFilter.addAction(INTENT_NOTIFY)
        notificationReceiver = object : BroadcastReceiver() {
            override fun onReceive(context: Context?, intent: Intent?) {
                val packageName = intent!!.getStringExtra(PACKAGE_NAME)
                val title = intent.getStringExtra(NOTIFICATION_TITLE)
                val content = intent.getStringExtra(NOTIFICATION_CONTENT)
                val id = intent.getStringExtra(ID)

                val data = HashMap<String, Any?>()
                data["id"] = id
                data["packageName"] = packageName
                data["title"] = title
                data["content"] = content
                data["statusProcessNotification"] = ViewState.StatusProcessNotification.DOING
                data["timeCreated"] = System.currentTimeMillis()
                Log.d("dongnd1", "receive: $data")
                if (!content.isNullOrEmpty() && id != null && id != "0") {
//                    data["content"] = contentBidv
//                    data["packageName"] = packageNameBidv
                    mainViewModel.addNotification(data)
                }

            }
        }
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.UPSIDE_DOWN_CAKE) {
            this.registerReceiver(notificationReceiver, intentFilter, RECEIVER_EXPORTED)
        } else {
            this.registerReceiver(notificationReceiver, intentFilter)
        }
        Log.d("dongnd1", "start listener notification")
        mainViewModel.setStatusListening(StatusListener.LISTENING)
    }
    private fun startListenSMS() {
        if (!isPermissionSmsGranted()) {
            return
        }
        val intentFilterSMS = IntentFilter()
        intentFilterSMS.addAction(INTENT_SMS)
        smsReceiver = object : BroadcastReceiver() {
            override fun onReceive(context: Context?, intent: Intent?) {
                val packageName = intent!!.getStringExtra(PACKAGE_NAME)
                val title = intent.getStringExtra(NOTIFICATION_TITLE)
                val content = intent.getStringExtra(NOTIFICATION_CONTENT)
                val id = intent.getStringExtra(ID)

                val data = HashMap<String, Any?>()
                data["id"] = id
                data["packageName"] = packageName
                data["title"] = title
                data["content"] = content
                data["statusProcessNotification"] = ViewState.StatusProcessNotification.DOING
                data["timeCreated"] = System.currentTimeMillis()
                Log.d("dongnd1", "receive: $data")
                if (!content.isNullOrEmpty() && id != null && id != "0") {
//                    data["content"] = contentBidv
//                    data["packageName"] = packageNameBidv
                    mainViewModel.addSMS(data)
                }

            }
        }
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.UPSIDE_DOWN_CAKE) {
            this.registerReceiver(smsReceiver, intentFilterSMS, RECEIVER_EXPORTED)
        } else {
            this.registerReceiver(smsReceiver, intentFilterSMS)
        }
        Log.d("dongnd1", "start listener notification")
        mainViewModel.setStatusListening(StatusListener.LISTENING)
    }
    private fun stopLister() {
        this.unregisterReceiver(notificationReceiver)
        notificationReceiver = null
        mainViewModel.setStatusListening(StatusListener.STOP)
    }


}

@Composable
fun NotificationItem(message: Message) {
    val context = LocalContext.current
    val drawable =
        NotificationUtils.getIconFromPackageName(context, message.packageName)
    val bitmap = when (drawable) {
        is BitmapDrawable -> drawable.bitmap
        is AdaptiveIconDrawable -> {
            val bitmap = Bitmap.createBitmap(
                drawable.intrinsicWidth,
                drawable.intrinsicHeight,
                Bitmap.Config.ARGB_8888
            )
            val canvas = Canvas(bitmap)
            drawable.setBounds(0, 0, canvas.width, canvas.height)
            drawable.draw(canvas)
            bitmap
        }

        else -> null
    }
    var statusProcessNotification: Int = ViewState.StatusProcessNotification.DOING
    try {
        statusProcessNotification = message.status
    } catch (_: Exception) {
    }

    val colorStatusProcessNotification = when (statusProcessNotification) {
        ViewState.StatusProcessNotification.DOING -> Color.DarkGray
        ViewState.StatusProcessNotification.FAIL -> Color.Red
        ViewState.StatusProcessNotification.SUCCESS -> Color.Blue
        else -> Color.DarkGray
    }

    val colorResult  = when (statusProcessNotification) {
        ViewState.StatusProcessNotification.DOING -> Color.DarkGray
        ViewState.StatusProcessNotification.FAIL -> Color.Red
        ViewState.StatusProcessNotification.SUCCESS -> Color.Blue
        else -> Color.DarkGray
    }

    Card(
        modifier = Modifier
            .fillMaxWidth()
            .padding(8.dp),
        elevation = CardDefaults.cardElevation(
            defaultElevation = 6.dp
        ),
        colors = CardDefaults.cardColors(
            containerColor = Color.LightGray, //Card background color
            contentColor = Color.Black  ,//Card content color,e.g.text

        ),
        shape = RoundedCornerShape(8.dp) // Tạo góc bo tròn cho Card
    ) {
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .padding(5.dp)
        ) {

            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(5.dp),
                verticalAlignment = Alignment.CenterVertically
            ) {

                if (bitmap != null) {
                    Image(
                        bitmap = bitmap.asImageBitmap(),
                        contentDescription = null,
                        contentScale = ContentScale.Crop,
                        modifier = Modifier
                            .size(32.dp)
                            .clip(CircleShape)

                    )
                }
                Box(
                    modifier = Modifier
                        .padding(horizontal = 10.dp)
                        .weight(1f)
                ) {
                    Text(
                        text = "Message: ${message.message}",
                        fontSize = 12.sp,
                        maxLines = 5
                    )
                }

                Box(
                    modifier = Modifier
                        .background(
                            color = colorStatusProcessNotification,
                            shape = RoundedCornerShape(50)
                        )
                        .padding(
                            horizontal = 16.dp,
                            vertical = 8.dp
                        )
                ) {
                    Text(
                        text = ViewState.StatusProcessNotification.getProcessNotificationDisplay(
                            statusProcessNotification
                        ),
                        color = Color.White,
                        fontSize = 12.sp
                    )

                }
            }

            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(5.dp),
                verticalAlignment = Alignment.CenterVertically
            ){
                Box(
                    modifier = Modifier
                        .padding(horizontal = 10.dp)
                        .weight(1f)
                ) {
                    Text(
                        text ="Kết Quả: ${message.response}",
                        fontSize = 15.sp,
                        maxLines = 5,
                        color = colorResult,
                    )
                }
            }
        }
    }
}
