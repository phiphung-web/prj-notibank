package com.piashcse.hilt_mvvm_compose_movie.utils

import android.annotation.SuppressLint
import com.piashcse.hilt_mvvm_compose_movie.data.model.NotificationData
import com.piashcse.hilt_mvvm_compose_movie.data.model.ViewState
import timber.log.Timber
import java.text.SimpleDateFormat
import java.util.*


object Utils {
    private data class OtpInfo(
        val code: String,
        val amount: String,
        val merchant: String
    )

    private val otpCodeRegex =
        """(?:ma\s+(?:so\s+xac\s+thuc\s+)?OTP|OTP)\s*(?:la)?\s*:?\s*([0-9][0-9\s-]{3,12}[0-9])"""
            .toRegex(RegexOption.IGNORE_CASE)
    private val otpCodeBeforeKeywordRegex =
        """([0-9][0-9\s-]{3,12}[0-9])\s+(?:la\s+)?ma\s+xac\s+thuc\s+OTP"""
            .toRegex(RegexOption.IGNORE_CASE)
    private val otpAmountRegex =
        """so\s+tien\s+([0-9][0-9.,]*)\s*([A-Z]{3})?""".toRegex(RegexOption.IGNORE_CASE)
    private val otpMerchantRegex =
        """so\s+tien\s+[0-9][0-9.,]*\s*[A-Z]{3}\s+tai\s+([^,]+)"""
            .toRegex(RegexOption.IGNORE_CASE)
    private val otpBizMerchantRegex =
        """giao\s+dich\s+tren\s+([^,.]+)""".toRegex(RegexOption.IGNORE_CASE)

    private fun isOtpMessage(message: String): Boolean {
        return message.contains("OTP", ignoreCase = true)
    }

    @SuppressLint("SimpleDateFormat")
    @JvmStatic
    fun getDateTime(milisecondTime: Long): String? {
        return try {
            val sdf = SimpleDateFormat("MMyyyy")
            val netDate = Date(milisecondTime)
            sdf.format(netDate)
        } catch (e: Exception) {
            e.toString()
        }
    }

    @JvmStatic
    fun convertStringToInt(str: String): Int? {
        return try {
            val parsedInt = str.trim().toIntOrNull()
            parsedInt
        } catch (nfe: NumberFormatException) {
            -1
        }
    }

    private fun getPhoneNumber(data: String): String {
        return try {
            val dataItem = data.replace("ND:", "").trim().split("\\s+".toRegex())
            if (dataItem.size > 2) {
                dataItem[1]
            } else {
                dataItem[0]
            }
        } catch (ex: Exception) {
            ""
        }
    }

    private fun parseOtpInfo(message: String): OtpInfo? {
        val otpCode = otpCodeRegex.find(message)?.groupValues?.get(1)
            ?: otpCodeBeforeKeywordRegex.find(message)?.groupValues?.get(1)
            ?: return null
        val amount = otpAmountRegex.find(message)
            ?.groupValues
            ?.get(1)
            ?.replace("""[,.]""".toRegex(), "")
            ?: "1"
        val merchant = otpMerchantRegex.find(message)?.groupValues?.get(1)
            ?: otpBizMerchantRegex.find(message)?.groupValues?.get(1)
            ?: ViewState.BankValue.MB_ONLINE_OTP

        return OtpInfo(
            code = otpCode.replace("""\D""".toRegex(), ""),
            amount = amount,
            merchant = merchant.trim()
        )
    }

    private fun createOtpNotificationData(
        message: String,
        sender: String,
        timeCreated: Long,
        monthReceiver: String?
    ): NotificationData {
        val otpInfo = parseOtpInfo(message)
        return NotificationData(
            timeCreated,
            otpInfo?.merchant ?: ViewState.BankValue.MB_ONLINE_OTP,
            sender,
            otpInfo?.code ?: "",
            otpInfo?.amount ?: "1",
            message,
            ViewState.StatusProcessNotification.DOING,
            ViewState.TypeBank.MB_ONLINE_OTP,
            otpInfo?.merchant,
            monthReceiver,
            "1"
        )
    }


    @JvmStatic
    fun getSMSReceiver(message: String, sender: String, time_created: Long): NotificationData {
        lateinit var notificationData: NotificationData
        var typeBank = -1
        try {
            val monthReceiver = getDateTime(time_created)
            if (isOtpMessage(message)) {
                typeBank = ViewState.TypeBank.MB_ONLINE_OTP
                notificationData = createOtpNotificationData(message, sender, time_created, monthReceiver)
                } else {
                    // 2. LUỒNG CŨ: Xử lý tin nhắn Biến động số dư MBBANK gốc của bạn
                    val contentSMS = message.replace(ViewState.ChangeValue.MB,"").trim().split("|").toTypedArray()
                    if (contentSMS.size > 2) {
                        typeBank = ViewState.TypeBank.MBBANK

                        val valueGD = contentSMS[1].split("VND").toTypedArray()
                        var price = "0"
                        var time = ""

                        if(valueGD.size > 1){
                            price = valueGD[0].replace("""[GD:,VND+]""".toRegex(), "")
                            time = valueGD[1]
                        }
                        val valueAccountBalance = contentSMS[2].replace("""[SD:,.VND]""".toRegex(), "")
                        val phone = contentSMS[2].replace("TU:","")
                        notificationData = NotificationData(
                            time_created, time, sender, " ", price, message,
                            ViewState.StatusProcessNotification.DOING, typeBank, null, monthReceiver, valueAccountBalance.toString()
                        )
                    } else {
                        typeBank = ViewState.TypeBank.MBBANK
                        notificationData = NotificationData(
                            time_created, " ", sender, " ", "1", message,
                            ViewState.StatusProcessNotification.DOING, typeBank, null, monthReceiver, "1"
                        )
                    }
                }
            
        } catch (ex: java.lang.Exception) {

            Timber.w("forwarding received message error $ex")
            val monthReceiver = getDateTime(time_created)
            val contentSMS = message.split("\n+".toRegex()).toTypedArray()
            val time = "00:00"
            notificationData = NotificationData(
                time_created, time, sender, "", "0", message,
                ViewState.StatusProcessNotification.DOING, typeBank, null, monthReceiver, "0"
            )
            return notificationData


        }
        return notificationData

    }


    @JvmStatic
    fun getNotifyReceiver(message: String, sender: String, time_created: Long): NotificationData {
        lateinit var notificationData: NotificationData
        var typeBank = -1
//        val vietCombank = "MBVCB"
//        (TPBank): 30/08/23;09:45
//        TK: xxxx3857401
//        PS:+10.000VND
//        SD: 59.098VND
//        SD KHA DUNG: 59.098VND
//        ND: Long 0989212298 bk12345
        //   ND: 0963634390 FT23290545036130
        //ND: MBVCB.4441125484.097426.0964049666.CT tu 9964049666 DUONG - Viet Combank
        //ND: TPB;56716239999;0375794647  - Agribrank
        // (TPBank): 07/10/24;14:04
        //SO GD: 011ITC1242817331
        // TK: xxxx3857401
        // PS:+2.000VND
        // SD: 49.873VND
        // SD KHA DUNG: 49.873VND
        // ND: HD0989212298
        ///
        try {
            val monthReceiver = getDateTime(time_created)
            if (isOtpMessage(message)) {
                return createOtpNotificationData(message, sender, time_created, monthReceiver)
            }

            // sms for TP Bank
            if (sender == ViewState.BankValue.TPBANk) {
                typeBank = ViewState.TypeBank.TPBANk
                val contentSMS = message.split("\n+".toRegex()).toTypedArray()

                var price = ""
                var valuePrice = 0
                var phone = ""
                var valueAccountBalance = 0
                var accountBalance = ""
                var msgContentSendMoney = ""

                val time =
                    contentSMS.get(0).replace("""[(TPBank),;]""".toRegex(), " ").trim().drop(1)

                if (contentSMS.size > 3) {
                    price = contentSMS[2].replace("""[PS:,.VND]""".toRegex(), "").toString()
                    valuePrice = convertStringToInt(price)!!
                }

                if (contentSMS.size > 5) {
                    accountBalance =
                        contentSMS[3].replace("""[SD:,.VND]""".toRegex(), "").toString()
                    valueAccountBalance = convertStringToInt(accountBalance)!!
                }

                if (contentSMS.size > 6) {
                    msgContentSendMoney = contentSMS.get(5)
                }

                if (valuePrice > 0) {
                    if (msgContentSendMoney.contains(ViewState.BankValue.VIETCOMBANK)) {
                        val data = msgContentSendMoney.split(".").toTypedArray()
                        phone = if (data.size > 3) {
                            data[3].trim()
                        } else {
                            msgContentSendMoney
                        }
                    } else if (msgContentSendMoney.contains(ViewState.BankValue.AGRIBANK)) {
                        val data = msgContentSendMoney.split(";").toTypedArray()
                        phone = if (data.size > 2) {
                            data[2].trim()
                        } else {
                            msgContentSendMoney
                        }
                    } else {
                        val msgSendMoney = msgContentSendMoney.replace("ND:", "").trim()
                        val data = msgSendMoney.split(" ").toTypedArray()
                        phone = if (data.size > 1) {
                            data[0].trim()
                        } else {
                            msgSendMoney
                        }
                    }
                }

                notificationData = NotificationData(
                    time_created,
                    time,
                    sender,
                    phone,
                    valuePrice.toString(),
                    message,
                    ViewState.StatusProcessNotification.DOING,
                    typeBank,
                    null,
                    monthReceiver,
                    valueAccountBalance.toString()
                )


            } else if (sender == ViewState.BankValue.BIDV) { // SMS for BIDV
                //TK120xxx5711 tai BIDV +600,000VND vao 17:04 28/08/2023. So du:3,000,000VND. ND: CK12010006665793 LE THI LAN Chuyen tien
//                TK 452xxx1986 tai BIDV +50,000VND vao 00:56 16/09/2023. So du:159,000VND. ND: TKThe :56716239999, tai Tienphongbank. 098921229888
                // TK120xxx5711 tai BIDV -32,000VND vao 13:15 23/08/2023. So du:276,098VND. ND: MB-TKThe :04260197401, tai Tienphongbank. ND NGUYEN HOANG LONG Chuyen tien an -CTLN
                // TK120xxx5711 tai BIDV -501,100VND vao 09:00 31/08/2023. So du:11,206,921VND. ND: Rut tien tai ATM BIDV
                //BIDV xin thông báo đến Quý khách
                //Thời gian GD: 16:54 21/10/2024
                //Tài khoản thanh toán : 1206665711
                //Số tiền: + 175,000 VND
                //Số dư cuối: 2,954,114 VND
                //Nội dung giao dịch: TKThe :04260192901, tai Tienphongbank. Nguyen Thi Phuong chuyen tien taxi -CTLNHIDI000010257496374-1/1-CRE-002
                val contentSMS = message.split("\n").toTypedArray()
                //                val contentSMS = message.split("[.]+".toRegex()).toTypedArray()
                if (contentSMS.size > 2) {
                    val info =contentSMS[0].split(" ").toTypedArray()
                    var time =""
                    if(info.size > 2){
                        time = info[4]+ " " + info[5]
                    }
                    typeBank = ViewState.TypeBank.BIDV
                    val money = contentSMS[2].replace("""[Số tiền GD:,.VND]""".toRegex(), "").trim()
//                    val price =
//                        money?.get(1)?.replace("""[,VND]""".toRegex(), "")?.trim().toString()
//                     Số dư cuối: 1,223,332VND Nội dung giao dịch: TKThe :04263857401, tai Tienphongbank
                    val infoBalance = contentSMS[4].split("Nội dung giao dịch:").toTypedArray()
                    var accountBalance = ""
                    if(infoBalance.isNotEmpty()){
                        accountBalance =
                            contentSMS[3].replace("""[Số dư cuối:,.VND]""".toRegex(), "").toString()
                    }

                    val valueAccountBalance = convertStringToInt(accountBalance)!!
//                    val valuePrice = convertStringToInt(price)!!
                    var phone = ""

                    notificationData = NotificationData(
                        time_created,
                        time,
                        sender,
                        phone,
                        money.toString(),
                        message,
                        ViewState.StatusProcessNotification.DOING,
                        typeBank,
                        null,
                        monthReceiver,
                        valueAccountBalance.toString()
                    )
//
                }
            } else if (sender == ViewState.BankValue.MBBANK) {
                //  TK 03xxx225 GD: -200,000VND 29/05/24 10:58  SD: 17,250,803VND ND: Thanh toan QR Xevipnoibaivn - Ma giao dich/ Trace 172734
                // Notify
                //               Thông báo biến động số dư
                //TK 09xxx912|GD: +100,000VND 07/10/24 15:24 |SD: 100,000VND|TU: TRAN ANH HUY - 87979991986|ND: tra a Huy test Ma giao dich Trace960300 Trace 960300
                val contentSMS = message.replace(ViewState.ChangeValue.MB,"").trim().split("|").toTypedArray()
                if (contentSMS.size > 2) {
                    typeBank = ViewState.TypeBank.MBBANK

                    val valueGD = contentSMS[1].split("VND").toTypedArray()
                    var price = "0"
                    var time = ""

                    if(valueGD.size > 1){
                        price = valueGD[0].replace("""[GD:,VND+]""".toRegex(), "")
                        time = valueGD[1]
                    }
                    var valueAccountBalance =  contentSMS[2].replace("""[SD:,.VND]""".toRegex(), "")
                    //replace("SD:","").toString()
                    var phone = contentSMS[2].replace("TU:","")
                    notificationData = NotificationData(
                        time_created,
                        time,
                        sender,
                        " ",
                        price,
                        message,
                        ViewState.StatusProcessNotification.DOING,
                        typeBank,
                        null,
                        monthReceiver,
                        valueAccountBalance.toString()
                    )
                }
//                    val typeBank = ViewState.TypeBank.MBBANK
//                    val data = contentSMS[1]
//                    val strMSG = data.split("VND").toTypedArray()
//                    val price = strMSG.get(0).replace("""[,]""".toRegex(), "").trim().toString()
//                    val valuePrice = convertStringToInt(price)!!
//                    val dataBalance = strMSG.get(1).split("SD:")
//                    var accountBalance = ""
//                    var time = ""
//                    if (dataBalance.size > 1) {
//                        time = dataBalance[0].trim().toString()
//                        accountBalance =
//                            dataBalance[1].replace("""[,]""".toRegex(), "").trim().toString()
//                    }
//                    val valueAccountBalance = convertStringToInt(accountBalance)!!
//                    notificationData = NotificationData(
//                        time_created,
//                        time,
//                        sender,
//                        " ",
//                        valuePrice.toString(),
//                        message,
//                        ViewState.StatusProcessNotification.DOING,
//                        typeBank,
//                        null,
//                        monthReceiver,
//                        valueAccountBalance.toString()
//                    )
//
//                }
            } else if (sender == ViewState.BankValue.MSB_BANK) {
                val contentSMS = message.split("\n").toTypedArray()
                if (contentSMS.size > 2) {
                    typeBank = ViewState.TypeBank.MSB_BANK

                    val InFoCK  = contentSMS[0].split("(GD:").toTypedArray()
                    var price = "0"
                    var time = ""

                    if(InFoCK.size > 1){
                        val data = InFoCK[1].split(",Thue/Phi").toTypedArray()
                        if(data.isNotEmpty()){
                            price = data[0].replace(",".toRegex(), "")
                        }
                        val infoTime = InFoCK[0].split("TK:").toTypedArray()
                        if(infoTime.isNotEmpty()){
                            time = infoTime[0].trim()
                        }
                    }
                    var valueAccountBalance =  contentSMS[2].replace("""[SD:,.VND]""".toRegex(), "")
                    //replace("SD:","").toString()
                    var messageData = contentSMS[1].split("-")
                    var phone =""
                    if(messageData.isNotEmpty()){
                        if(messageData.size <= 1){
                            phone = messageData[0].replace("ND:","")
                        }else if(messageData.size <= 3){
                            phone = messageData[2]
                        }
                    }
                    notificationData = NotificationData(
                        time_created,
                        time,
                        sender,
                        phone,
                        price,
                        message,
                        ViewState.StatusProcessNotification.DOING,
                        typeBank,
                        null,
                        monthReceiver,
                        valueAccountBalance.toString()
                    )
                }
            }else if(sender == ViewState.BankValue.SACOMBANK){
//                Ngày 23/08/2025 17:15 TK: 022203061991. PS: +5,000 VND. Số dư khả dụng: 5,000 VND. CHUYEN TIEN NHANH QUA QR CKN 967145 - NGUYEN HOANG LONG - Ngan hang TMCP Tien Phong
                val contentSMS = message.split(".").toTypedArray()
                if (contentSMS.size > 2) {
                    typeBank = ViewState.TypeBank.SACOM_BANK
                    val TimeCK  = contentSMS[0].split("(PS:").toTypedArray()
                    val infoTime = TimeCK[0].split("TK:").toTypedArray()
                    var time = ""
                    var price = "0"
                    var phone = ""
                    var valueAccountBalance =""
                    if(infoTime.isNotEmpty()){
                        time = infoTime[0].replace("Ngày".toRegex(), "").trim()
                    }


                    val InFoCK  = contentSMS[1].split("PS:").toTypedArray()
                    if(InFoCK.size > 1){
                        val data = InFoCK[1].split("VND").toTypedArray()
                        if(data.isNotEmpty()){
                            price = data[0].replace(",".toRegex(), "").trim()
                        }
                    }

                    if (contentSMS.size > 3) {
                        valueAccountBalance =
                            contentSMS[2].replace("""[Số dư khả dụng:,.VND]""".toRegex(), "").trim()
                    }
                    //replace("SD:","").toString()
                    if (contentSMS.size >=4) {
                        val messageData = contentSMS[3].split("CKN")

                        if(messageData.isNotEmpty()){
                            phone = messageData[0].replace("HD".toRegex(), "")
                        }
                    }

                    notificationData = NotificationData(
                        time_created,
                        time,
                        sender,
                        phone,
                        price,
                        message,
                        ViewState.StatusProcessNotification.DOING,
                        typeBank,
                        null,
                        monthReceiver,
                        valueAccountBalance.toString()
                    )
                }
            }

        } catch (ex: java.lang.Exception) {

            Timber.w("forwarding received message error $ex")
            val monthReceiver = getDateTime(time_created)
            val contentSMS = message.split("\n+".toRegex()).toTypedArray()
            val time = "00:00"
            notificationData = NotificationData(
                time_created, time, sender, "", "0", message,
                ViewState.StatusProcessNotification.DOING, typeBank, null, monthReceiver, "0"
            )
            return notificationData


        }
        return notificationData

    }

}
