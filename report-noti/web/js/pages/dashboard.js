// $(document).ready(function () {
//     $.ajax({
//         type: "GET",
//         url: "/dash-board/get-statistic-money-trip-full",
//         data: { date: $(this).val() },
//         success: function (response) {
//             let json = JSON.parse(response);
//             $('.customers_collected_all').text(addCommas(json.customers_collected))
//             $('.customers_debt_all').text(addCommas(json.customers_debt))
//             $('.drivers_collected_all').text(addCommas(json.drivers_collected))
//             $('.drivers_debt_all').text(addCommas(json.drivers_debt))
//             $('.profits_collected_all').text(addCommas(json.profits_collected))
//             $('.profits_debt_all').text(addCommas(json.profits_debt))
//             $('.total_customer_revenue_all').text(addCommas(json.total_customer_revenue))
//             $('.total_driver_revenue_all').text(addCommas(json.total_driver_revenue))
//             $('.total_profit_all').text(addCommas(json.total_profit))
//         }
//     });
// })

// $(document).ready(function () {
//     $.ajax({
//         type: "GET",
//         url: "/dash-board/get-statistic-money-trip-day",
//         data: { date: $(this).val() },
//         success: function (response) {
//             let json = JSON.parse(response);
//             $('.customers_collected').text(addCommas(json.customers_collected))
//             $('.customers_debt').text(addCommas(json.customers_debt))
//             $('.drivers_collected').text(addCommas(json.drivers_collected))
//             $('.drivers_debt').text(addCommas(json.drivers_debt))
//             $('.profits_collected').text(addCommas(json.profits_collected))
//             $('.profits_debt').text(addCommas(json.profits_debt))
//             $('.total_customer_revenue').text(addCommas(json.total_customer_revenue))
//             $('.total_driver_revenue').text(addCommas(json.total_driver_revenue))
//             $('.total_profit').text(addCommas(json.total_profit))
//         }
//     });
// })