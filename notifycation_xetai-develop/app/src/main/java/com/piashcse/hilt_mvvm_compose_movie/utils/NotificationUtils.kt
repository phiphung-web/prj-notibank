package com.piashcse.hilt_mvvm_compose_movie.utils

import android.content.Context
import android.content.pm.PackageManager
import android.graphics.Bitmap
import android.graphics.Canvas
import android.graphics.drawable.Drawable
import com.piashcse.hilt_mvvm_compose_movie.R

object NotificationUtils {
    fun getBitmapFromDrawable(drawable: Drawable): Bitmap {
        val bmp = Bitmap.createBitmap(
            drawable.intrinsicWidth,
            drawable.intrinsicHeight,
            Bitmap.Config.ARGB_8888
        )
        val canvas = Canvas(bmp)
        drawable.setBounds(0, 0, canvas.width, canvas.height)
        drawable.draw(canvas)
        return bmp
    }

    fun getIconFromPackageName(context: Context, packageName: String): Drawable? {
        return when {
            packageName.contains(AppConstant.MB_BANK_PACKAGE) -> context.getDrawable(R.drawable.icon_mb)
            packageName.contains(AppConstant.MB_BANK_BIZ_PACKAGE) -> context.getDrawable(R.drawable.icon_mb)
            packageName.contains(AppConstant.TP_BANK_PACKAGE) -> context.getDrawable(R.drawable.icon_tbbank)
            packageName.contains(AppConstant.BIDV_BANK_PACKAGE) -> context.getDrawable(R.drawable.icon_bidv)
            packageName.contains(AppConstant.MSB_PACKAGE) -> context.getDrawable(R.drawable.icon_msb)
            packageName.contains(AppConstant.SACOMBANK_PACKAGER) -> context.getDrawable(R.drawable.ic_sacombank)
            else -> context.getDrawable(R.drawable.icon_bidv)
        }
    }
}