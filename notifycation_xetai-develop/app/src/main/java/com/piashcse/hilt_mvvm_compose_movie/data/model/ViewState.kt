package com.piashcse.hilt_mvvm_compose_movie.data.model

import com.piashcse.hilt_mvvm_compose_movie.utils.AppConstant

object ViewState {
    class StatusProcessNotification {
        companion object {
            const val DOING = -1
            const val SUCCESS = 1
            const val FAIL = 0

            fun getProcessNotificationDisplay(code: Int) : String {
                return when (code) {
                    SUCCESS -> "Thành Công"
                    FAIL -> "Thất Bại"
                    else -> "Đang Xử Lý"
                }
            }
        }
    }

    class TypeBank {
        companion object {
            const val TPBANk = 1
            const val BIDV = 2
            const val MBBANK = 3
            const val MSB_BANK = 5
            const val SACOM_BANK = 7

            const val VIB_BANK = 6
            const val MB_ONLINE_OTP = 8
        }
    }

    object BankValue {
        const val TPBANk = "TPBank"
        const val BIDV = "BIDV"
        const val VIETCOMBANK = "MBVCB"
        const val AGRIBANK = "TPB;"
        const val MBBANK = "MBBANK"
        const val VPBANK = "VPBANK"
        const val MSB_BANK = "MSBBANK"
        const val SACOMBANK = "SACCOMBANK"
        const val MB_ONLINE_OTP = "MBOnlineOTP"
        const val GOOGLE = "Google"
        const val MISA = "misa.vn"
        
        fun getBankValueFromPackageName(packageName: String): String {
            return when {
                packageName.contains(AppConstant.MB_BANK_PACKAGE) -> MBBANK
                packageName.contains(AppConstant.MB_BANK_BIZ_PACKAGE) -> MBBANK
                packageName.contains(AppConstant.TP_BANK_PACKAGE) -> TPBANk
                packageName.contains(AppConstant.BIDV_BANK_PACKAGE) -> BIDV
                packageName.contains(AppConstant.VP_BANK_PACKAGE) -> VPBANK
                packageName.contains(AppConstant.MSB_PACKAGE) -> MSB_BANK
                packageName.contains(AppConstant.SACOMBANK_PACKAGER) -> SACOMBANK
                packageName.contains(AppConstant.MB_ONLINE_OTP_PACKAGE) -> MB_ONLINE_OTP    
                else -> ""
            }
        }

    }

    class ChangeValue {
        companion object {
            const val MB ="Thông báo biến động số dư\n"


        }
    }
}