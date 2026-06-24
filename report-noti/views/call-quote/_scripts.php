<?php
/* @var $this yii\web\View */
/* @var $source_call array */
?>

<script>
    var scheduleListTrip = JSON.parse('<?php echo json_encode(SCHEDULE_LIST_TRIP) ?>');
    var callSource = JSON.parse('<?php echo json_encode($source_call) ?>');
    var idCallBack = <?php echo isset($_GET['idCallBack']) ? $_GET['idCallBack'] : 0 ?>;
</script>

<?php
$script = <<<JS
        \$(document).on("click", ".btn-reject-call-search", function(){
            \$("#modalReject").modal("show");
            let phone = \$(this).attr('data-phone')
            let hotline = \$(this).attr('data-hotline')
            let source = findSourceByHotline(callSource, hotline);
            if(source != null) \$('.source_trip-booking-modal').val(source)
            \$('#booking-customer_phone').val(phone)
            \$('#booking-hotline').val(hotline)
            return false;
        });

        \$(document).on('click', '.btn-save-reject' , function(){
            let form = \$('#form-update-status-booking').serializeArray()
            var jsonObject = form.reduce(function(obj, item) {
                obj[item.name] = item.value;
                return obj;
            }, {});
            \$.ajax({
                url: '/call-quote/update-booking',
                type: 'post',
                data: {
                    form: jsonObject,
                },
                success: function(json) {
                    if (json.status) {
                        toastr.success(json.message);
                        \$('#modalReject').modal('hide');
                    } else {
                        toastr.error(json.message);
                    }
                }
            });
            return false;
        })

        \$(document).on('change', '.js-checkbox-round-trip', function(){
            var checkbox = \$(this);
            var isChecked = checkbox.prop('checked');
            if (isChecked) {
                \$('.js-checkbox-1-chieu').prop('checked', false)
            }
        })

        \$(document).on('change', '.js-checkbox-1-chieu', function(){
            var checkbox = \$(this);
            var isChecked = checkbox.prop('checked');
            if (isChecked) {
                \$('.js-checkbox-round-trip').prop('checked', false)
            }
        })

        // Debounce function to prevent too many API calls
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Debounced version of getDistance function
        const debouncedGetDistance = debounce(function() {
            getDistance();
        }, 500); // Wait 500ms after user stops typing

        // Debounced version of address search function
        const debouncedAddressSearch = debounce(function(input, suggestionsContainer) {
            searchAddress(input, suggestionsContainer);
        }, 300); // Wait 300ms after user stops typing

        // Debounced version of fetchPriceData function
        const debouncedFetchPriceData = debounce(function(distance, hourWait, overnight, scheduleData, surcharge, voucher, phone, idCallBack, pickupAddress, destinationAddress, pickupTime) {
            fetchPriceData(distance, hourWait, overnight, scheduleData, surcharge, voucher, phone, idCallBack, pickupAddress, destinationAddress,pickupTime);
        }, 500); // Wait 500ms after user stops changing inputs

        // Address autocomplete functionality
        let autocompleteService;
        let placesService;

        // Initialize Google Maps services when API is loaded
        function initGoogleMapsServices() {
            if (typeof google !== 'undefined' && google.maps) {
                autocompleteService = new google.maps.places.AutocompleteService();
                placesService = new google.maps.places.PlacesService(document.createElement('div'));
            }
        }

        // Search for address suggestions
        function searchAddress(input, suggestionsContainer) {
            if (!autocompleteService || !input.trim()) {
                suggestionsContainer.style.display = 'none';
                return;
            }

            const request = {
                input: input,
                location: new google.maps.LatLng(21.0285, 105.8542), // Hanoi center
                radius: 400000,
                componentRestrictions: { country: 'vn' },
                types: ['geocode', 'establishment']
            };

            autocompleteService.getPlacePredictions(request, function(predictions, status) {
                if (status === google.maps.places.PlacesServiceStatus.OK && predictions) {
                    displaySuggestions(predictions, suggestionsContainer);
                } else {
                    suggestionsContainer.style.display = 'none';
                }
            });
        }

        // Display address suggestions
        function displaySuggestions(predictions, suggestionsContainer) {
            suggestionsContainer.innerHTML = '';

            if (predictions.length === 0) {
                suggestionsContainer.style.display = 'none';
                return;
            }

            predictions.forEach((prediction, index) => {
                const item = document.createElement('div');
                item.className = 'address-suggestion-item';
                item.setAttribute('data-index', index);

                const mainText = document.createElement('span');
                mainText.className = 'suggestion-main-text';
                mainText.textContent = prediction.structured_formatting.main_text;

                const secondaryText = document.createElement('span');
                secondaryText.className = 'suggestion-secondary-text';
                secondaryText.textContent = prediction.structured_formatting.secondary_text;

                item.appendChild(mainText);
                item.appendChild(secondaryText);

                item.addEventListener('click', function() {
                    selectAddress(prediction, suggestionsContainer);
                });

                item.addEventListener('mouseenter', function() {
                    // Remove active class from all items
                    suggestionsContainer.querySelectorAll('.address-suggestion-item').forEach(el => {
                        el.classList.remove('active');
                    });
                    // Add active class to current item
                    this.classList.add('active');
                });

                suggestionsContainer.appendChild(item);
            });

            suggestionsContainer.style.display = 'block';
        }

        // Select an address from suggestions
        function selectAddress(prediction, suggestionsContainer) {
            const input = suggestionsContainer.previousElementSibling;
            input.value = prediction.description;
            suggestionsContainer.style.display = 'none';

            // Trigger distance calculation if both addresses are filled
            const pickupAddress = \$('.pickup_address').val().trim();
            const destinationAddress = \$('.destination_address').val().trim();

            if (pickupAddress && destinationAddress) {
                debouncedGetDistance();
            }
        }

        // Handle keyboard navigation
        function handleKeyboardNavigation(event, suggestionsContainer) {
            const items = suggestionsContainer.querySelectorAll('.address-suggestion-item');
            const activeItem = suggestionsContainer.querySelector('.address-suggestion-item.active');
            let currentIndex = -1;

            if (activeItem) {
                currentIndex = parseInt(activeItem.getAttribute('data-index'));
            }

            switch(event.key) {
                case 'ArrowDown':
                    event.preventDefault();
                    if (currentIndex < items.length - 1) {
                        if (activeItem) activeItem.classList.remove('active');
                        items[currentIndex + 1].classList.add('active');
                    } else if (currentIndex === -1 && items.length > 0) {
                        items[0].classList.add('active');
                    }
                    break;

                case 'ArrowUp':
                    event.preventDefault();
                    if (currentIndex > 0) {
                        activeItem.classList.remove('active');
                        items[currentIndex - 1].classList.add('active');
                    }
                    break;

                case 'Enter':
                    event.preventDefault();
                    if (activeItem) {
                        const predictionIndex = parseInt(activeItem.getAttribute('data-index'));
                        const predictions = JSON.parse(activeItem.parentElement.getAttribute('data-predictions') || '[]');
                        if (predictions[predictionIndex]) {
                            selectAddress(predictions[predictionIndex], suggestionsContainer);
                        }
                    }
                    break;

                case 'Escape':
                    suggestionsContainer.style.display = 'none';
                    break;
            }
        }

        \$(document).on('change', '.overnight-general-checkbox, .wait-general, .distance-general, .js-checkbox-schedule, .surcharge, .pickup-time, .voucher-call', function(){
            getPrice();
        })

        // Initialize Google Maps services when page loads
        \$(document).ready(function() {
            // Check if Google Maps API is loaded
            if (typeof google !== 'undefined' && google.maps) {
                initGoogleMapsServices();
            } else {
                // Wait for Google Maps API to load
                window.initGoogleMapsServices = initGoogleMapsServices;
            }
        });

        // Handle address input events with autocomplete
        \$(document).on('input', '.pickup_address', function() {
            const input = this;
            const suggestionsContainer = document.getElementById('pickup-suggestions');
            debouncedAddressSearch(input.value, suggestionsContainer);
        });

        \$(document).on('input', '.destination_address', function() {
            const input = this;
            const suggestionsContainer = document.getElementById('destination-suggestions');
            debouncedAddressSearch(input.value, suggestionsContainer);
        });

        // Handle keyboard navigation for pickup address
        \$(document).on('keydown', '.pickup_address', function(event) {
            const suggestionsContainer = document.getElementById('pickup-suggestions');
            handleKeyboardNavigation(event, suggestionsContainer);
        });

        // Handle keyboard navigation for destination address
        \$(document).on('keydown', '.destination_address', function(event) {
            const suggestionsContainer = document.getElementById('destination-suggestions');
            handleKeyboardNavigation(event, suggestionsContainer);
        });

        // Hide suggestions when clicking outside
        \$(document).on('click', function(event) {
            if (!\$(event.target).closest('.address-autocomplete-container').length) {
                \$('.address-suggestions').hide();
            }
        });

        // Handle blur events for address inputs with debounce
        \$(document).on('blur', '.pickup_address, .destination_address', function(){
            // Delay hiding suggestions to allow for clicks
            setTimeout(function() {
                \$('.address-suggestions').hide();
            }, 200);

            let pickupAddress = \$('.pickup_address').val().trim();
            let destinationAddress = \$('.destination_address').val().trim();

            // Only call getDistance if both addresses are filled
            if (pickupAddress && destinationAddress) {
                debouncedGetDistance();
            }
        })

        \$(document).on('click', '.btn-click-advise' , function(){
            \$('.wrap-source').remove()
            getPrice();
        })

        \$(document).on('click', '.btn-reverse' , function(){
            let pickupAddress = \$('.pickup_address').val();
            let destinationAddress = \$('.destination_address').val();
           let temp = pickupAddress;
            pickupAddress = destinationAddress;
            destinationAddress = temp;
            \$('.pickup_address').val(pickupAddress);
            \$('.destination_address').val(destinationAddress);
        })

        if(\$(window).width() >= 768) \$('.sidebar-toggle').trigger('click')

         \$('.distance-general').on('blur', function() {
            let inputValue = \$(this).val();
            if (!isDecimal(inputValue)) {
                \$('#error-message').text('Vui lòng nhập số thập phân hợp lệ.');
            } else {
                \$('#error-message').text('');
            }
        });

        function isDecimal(value) {
            return /^\d+(\.\d+)?\$/.test(value);
        }

        function getPrice() {
            let distance = \$('.distance-general').val();
            let hourWait = \$('.wait-general').val();
            let overnight = \$('.overnight-general-checkbox').is(':checked') ? 1 : 0;
            let surcharge = \$('.surcharge').val();
            let voucher = \$('.voucher-call').val();
            let phone = \$('.js-phone-call').text();
            let idCallBack = \$('.js-request-callback-id').val();
            let pickupAddress = \$('.pickup_address').val();
            let pickupTime = \$('.pickup-time').val();
            let destinationAddress = \$('.destination_address').val();
            let scheduleData = [];
            \$('.js-checkbox-schedule:checked').each(function() {
                scheduleData.push(\$(this).val());
            });

            debouncedFetchPriceData(distance, hourWait, overnight, scheduleData, surcharge, voucher, phone, idCallBack, pickupAddress, destinationAddress, pickupTime);
        }

        function getDistance() {
            let pickupAddress = \$('.pickup_address').val().trim();
            let destinationAddress = \$('.destination_address').val().trim();

            // Validate that both addresses are provided
            if (!pickupAddress || !destinationAddress) {
                console.log('Both pickup and destination addresses are required');
                return;
            }

            \$.ajax({
                url: '/call-quote/get-distance',
                type: 'get',
                data: {
                    pickupAddress: pickupAddress,
                    destinationAddress: destinationAddress,
                },
                success: function(json) {
                    if (json.distance) {
                        $('.distance-general').val(json.distance);
                        const distance = json.distance;
                        const hourWait = $('.wait-general').val() || 0;
                        const overnight = $('.overnight-general-checkbox').is(':checked') ? 1 : 0;
                        let scheduleData = [];
                        $('.js-checkbox-schedule:checked').each(function() {
                            scheduleData.push($(this).val());
                        });
                        const surcharge = $('.surcharge').val() || 0;
                        const voucher = $('.voucher-call').val() || '';
                        const phone = $('.js-phone-call').text() || '';
                        const idCallBack = $('.js-request-callback-id').val() || 0;
                        const pickupAddress = $('.pickup_address').val() || '';
                        const pickupTime = $('.pickup-time').val() || '';
                        const destinationAddress = $('.destination_address').val() || '';
                        debouncedFetchPriceData(distance, hourWait, overnight, scheduleData, surcharge, voucher, phone, idCallBack, pickupAddress, destinationAddress, pickupTime);
                    } else {
                        $('.table-advise').html('<div class="text-danger">Không lấy được khoảng cách</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error calculating distance:', error);
                }
            });
        }

        function fetchPriceData(distance, hourWait, overnight, scheduleData, surcharge, voucher, phone, idCallBack, pickupAddress, destinationAddress, pickupTime) {
            \$.ajax({
                url: '/call-quote/find-price',
                type: 'get',
                data: {
                    distance: distance,
                    hourWait: hourWait,
                    overnight: overnight,
                    scheduleData: scheduleData,
                    surcharge: surcharge,
                    voucher: voucher,
                    phone: phone,
                    idCallBack: idCallBack,
                    pickupAddress: pickupAddress,
                    pickup_time: pickupTime,
                    destinationAddress: destinationAddress,
                },
                success: function(json) {
                    var html = json.data;
                    var tableList = \$(".table-advise");
                    tableList.html('');
                    tableList.append(html);
                }
            });
        }

        function findSourceByHotline(object, hotline) {
            console.log(hotline);
            for (var key in object) {
                if (object.hasOwnProperty(key)) {
                    if (object[key].includes(hotline)) {
                        return key;
                    }
                }
            }
            return null;
        }
    JS;
$this->registerJs($script);
?>
