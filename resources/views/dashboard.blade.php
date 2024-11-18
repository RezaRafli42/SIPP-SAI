@extends('layouts.layout')
<title>Dashboard</title>
@section('main-panel')
    <div class="main-panel">
        <div class="content-wrapper">
            {{-- Greeting --}}
            <div class="row">
                <div class="col-md-12 grid-margin">
                    <div class="row">
                        <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                            <h3 class="font-weight-bold">Welcome {{ auth()->user()->name }}</h3>
                            <h6 class="font-weight-normal mb-0">All systems are running smoothly!
                            </h6>
                        </div>
                        <div class="col-12 col-xl-4">
                            <div class="justify-content-end d-flex">
                                <div class="flex-md-grow-1 flex-xl-grow-0">
                                    <button class="btn btn-sm btn-light bg-white" type="button">
                                        <i class="mdi mdi-calendar"></i> <span id="current-date"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Weather and 4 cards --}}
            <div class="row">
                <div class="col-md-6 grid-margin stretch-card">
                    <div class="card tale-bg" style="border: solid 4px white; border-radius: 20px">
                        <div class="card-people mt-auto" style="max-height: 410px">
                            {{-- <img src="images/dashboard/people.svg" alt="people"> --}}
                            <div id="lottie-animation" style="width: 100%; height: 100%;"></div>
                            <div class="weather-info">
                                <div class="d-flex">
                                    <div id="temperature">
                                        <!-- Suhu akan ditampilkan di sini -->
                                        <span class="spinner-border" role="status" aria-hidden="true"></span>
                                        <span></span>
                                    </div>
                                    <div class="ml-2">
                                        <h4 class="location font-weight-normal" id="location-name"></h4>
                                        <h6 class="font-weight-normal">Jakarta Utara</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 grid-margin transparent">
                    <div class="row" style="height: 210px">
                        <div class="col-md-6 stretch-card transparent" style="padding-right: 8px; padding-bottom: 15px">
                            <div class="card card-tale">
                                <div class="card-body" style="border: solid 4px white; border-radius: 20px">
                                    <p class="mb-4 pt-4">Total Purchase Request This Month</p>
                                    <p class="fs-30 mb-2">{{ $totalPR }}</p>
                                    <p>from all ships.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 stretch-card transparent" style="padding-left: 8px; padding-bottom: 15px">
                            <div class="card card-dark-blue">
                                <div class="card-body" style="border: solid 4px white; border-radius: 20px">
                                    <p class="mb-4 pt-4">Total Purchase Order This Month</p>
                                    <p class="fs-30 mb-2">{{ $totalPO }}</p>
                                    <p>from all ships.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="height: 210px">
                        <div class="col-md-6 mb-4 mb-lg-0 stretch-card transparent" style="padding-right: 8px">
                            <div class="card card-light-blue">
                                <div class="card-body" style="border: solid 4px white; border-radius: 20px">
                                    <p class="mb-3 pt-3">Total Spend This Month</p>
                                    <p class="display-5">Rp</p>
                                    <p class="display-4 mb-2 text-nowrap totalSpend">
                                        <!-- Preloader for Total Spend -->
                                        <span class="spinner-border" role="status" aria-hidden="true"></span>
                                        <span>Loading...</span>
                                    </p>
                                    <p>exclude PPN.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 stretch-card transparent" style="padding-left: 8px">
                            <div class="card card-light-danger">
                                <div class="card-body" style="border: solid 4px white; border-radius: 20px">
                                    <p class="mb-4 pt-4">Total Purchase Requests Done</p>
                                    <p class="fs-30 mb-2">{{ $totalPRDone }}</p>
                                    <p id="percentagePRDone">
                                        <!-- Preloader for Total Spend -->
                                        <span class="spinner-border" role="status" aria-hidden="true"></span>
                                        <span>Loading...</span>
                                    </p> <!-- Placeholder for the percentage -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Log and ship with most pr --}}
            <div class="row">
                <div class="col-md-6 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="head-log bg-white" style="position: sticky; top: 0">
                                <p class="card-title">Log</p>
                                <div class="input-group mb-3" style="position: sticky; top: 0">
                                    <input id="searchLog" type="text" class="form-control" placeholder="Search...">
                                </div>
                            </div>
                            <div style="max-height: 400px; overflow-y: auto">
                                <ul class="icon-data-list" id="logResults">
                                    @foreach ($logs as $log)
                                        <li>
                                            <div class="d-flex">
                                                <img src="images/uploads/user-photos/{{ $log->users->profile_photo }}"
                                                    alt="user">
                                                <div>
                                                    <p class="text-info mb-1">{{ $log->users->name }}</p>
                                                    <p class="mb-0">{{ $log->action }}</p>
                                                    <small>{{ $log->time }}</small>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <p class="card-title">Ship With Most Purchase Requests</p>
                                <a href="/purchaseRequests" class="text-info">View all</a>
                            </div>
                            <p class="font-weight-500">The total number of PR's from all ships over time, highlighting the
                                top 4 ships with the highest number of PR's.</p>
                            <div id="shipMostPR-legend" class="chartjs-legend mt-4 mb-2"></div>
                            <canvas id="shipMostPR-chart" height="180"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Top Suppplier --}}
            <div class="row">
                <div class="col-md-12 grid-margin stretch-card">
                    <div class="card position-relative">
                        <div class="card-body">
                            <div id="detailedReports" class="carousel slide detailed-report-carousel position-static pt-2"
                                data-ride="carousel">
                                <p class="card-title mb-4">Top 3 Suppliers With the Highest Spend (This Month)</p>
                                <div class="carousel-inner">
                                    <!-- Preloader: This will be shown until the actual data is loaded -->
                                    <div class="d-flex justify-content-center align-items-center" id="carousel-preloader">
                                        <div class="spinner-border" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                        <p class="ml-3">Loading data...</p>
                                    </div>
                                </div>
                                <a class="carousel-control-prev" href="#detailedReports" role="button"
                                    data-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="sr-only">Previous</span>
                                </a>
                                <a class="carousel-control-next" href="#detailedReports" role="button"
                                    data-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="sr-only">Next</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Warehouse --}}
            <div class="row">
                <div class="col-md-4 stretch-card grid-margin">
                    <div class="card">
                        <div class="card-body pr-2">
                            <p class="card-title mb-0">Office Warehouse</p>
                            {{-- <div class="table-responsive" style="max-height: calc(100vh - 10px)"> --}}
                            <div style="max-height: 510px; overflow-y: auto">
                                <div style="">
                                    <table class="table table-borderless" style="width: 100%; border-collapse: collapse;">
                                        <thead style="position: sticky; top: 0; background-color: white; z-index: 10;">
                                            <tr>
                                                <th class="pl-0 pb-2 border-bottom">Code</th>
                                                <th class="border-bottom pb-2">Name</th>
                                                <th class="border-bottom pb-2">Quantity</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($officeWarehouse as $item)
                                                <tr>
                                                    <td class="pl-0 text-left">{{ $item->item_pms }}</td>
                                                    <td>
                                                        <p class="mb-0 text-left">
                                                            <span
                                                                class="font-weight-bold mr-2">{{ $item->item_name }}</span>
                                                        </p>
                                                    </td>
                                                    <td class="text-muted text-left">{{ $item->total_quantity }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 stretch-card grid-margin">
                    <div class="row">
                        <div class="col-md-12 grid-margin stretch-card mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <p class="card-title">Usage Item (Top 5)</p>
                                    <div class="charts-data">
                                        @foreach ($usageItem as $item)
                                            <div class="mt-3">
                                                <p class="mb-0">{{ $item->item_name }}</p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="progress progress-md flex-grow-1 mr-4">
                                                        <div class="progress-bar bg-inf0" role="progressbar"
                                                            style="width: {{ $item->total_used > 100 ? 100 : $item->total_used }}%"
                                                            aria-valuenow="{{ $item->total_used }}" aria-valuemin="0"
                                                            aria-valuemax="100"></div>
                                                    </div>
                                                    <p class="mb-0">{{ $item->total_used }}</p>
                                                </div>
                                            </div>
                                        @endforeach

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 stretch-card grid-margin grid-margin-md-0">
                            <div class="card data-icon-card-primary">
                                <div class="card-body">
                                    <p class="card-title text-white">Number of Transfers</p>
                                    <div class="row">
                                        <div class="col-8 text-white">
                                            <h3>{{ $totalIT }}</h3>
                                            <p class="text-white font-weight-500 mb-0">The total number of inventory
                                                transfers from
                                                the office to ships over all time and for all ships. This is calculated as a
                                                sum.</p>
                                        </div>
                                        <div class="col-4 background-icon">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 stretch-card grid-margin">
                    <div class="card">
                        <div class="card-body">
                            <canvas id="prMonth-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- content-wrapper ends -->
        <!-- partial:partials/_footer.html -->
    @endsection
    @section('script')
        {{-- LOTTIE --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.7.5/lottie.min.js"></script>

        {{-- CUACA --}}
        <script>
            $(document).ready(function() {
                const dateElement = document.getElementById('current-date');
                const today = new Date();
                const options = {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                };
                dateElement.innerHTML = `Today (${today.toLocaleDateString('en-GB', options)})`;

                const apiKey = '030b5b931c4ad6b95248addc46878874'; // Ganti dengan API key dari OpenWeatherMap
                const city = 'Jakarta';
                const apiUrl = `https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${apiKey}&units=metric`;

                fetch(apiUrl)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data && data.main && data.main.temp && data.weather && data.weather[0]) {
                            const temp = data.main.temp;
                            const locationName = data.name;
                            const weatherCondition = data.weather[0].main.toLowerCase();

                            // Set icon based on weather condition
                            let iconClass;
                            if (weatherCondition.includes('clear')) {
                                iconClass = 'mdi mdi-weather-sunny';
                            } else if (weatherCondition.includes('rain')) {
                                iconClass = 'mdi mdi-weather-rainy';
                            } else {
                                iconClass = 'mdi mdi-weather-cloudy'; // Default icon for other conditions
                            }

                            document.getElementById('temperature').innerHTML =
                                `<h2 class="mb-0 font-weight-normal"><i class="${iconClass} mr-2"></i>${temp}<sup>°C</sup></h2>`;
                            document.getElementById('location-name').innerText = locationName;
                        } else {
                            console.error("Invalid data structure:", data);
                        }
                    })
                    .catch(error => console.error('Error fetching weather data:', error));
            });
        </script>

        {{-- SHIP MOST PR --}}
        <script>
            if ($("#shipMostPR-chart").length) {
                var shipNames = @json($topShips->pluck('ship_name')); // Extract ship names
                var totalPRs = @json($topShips->pluck('total_pr')); // Extract total PR counts
                var selesaiPRs = @json($topShips->pluck('selesai_pr')); // Extract PR counts with status 'Selesai'

                var ShipMostPRChartCanvas = $("#shipMostPR-chart").get(0).getContext("2d");
                var ShipMostPRChart = new Chart(ShipMostPRChartCanvas, {
                    type: "bar",
                    data: {
                        labels: shipNames, // Use ship names as labels
                        datasets: [{
                            label: "Total PR",
                            data: totalPRs, // Use total PR counts as data
                            backgroundColor: "#98BDFF",
                            barPercentage: 2, // Adjust bar width
                            categoryPercentage: 0.9 // Adjust space between bars
                        }, {
                            label: "Completed PR",
                            data: selesaiPRs, // Use PR counts with status 'Selesai' as data
                            backgroundColor: "#151a41",
                            barPercentage: 2, // Adjust bar width
                            categoryPercentage: 0.9 // Adjust space between bars
                        }],
                    },
                    options: {
                        cornerRadius: 5,
                        responsive: true,
                        maintainAspectRatio: true,
                        layout: {
                            padding: {
                                left: 0,
                                right: 0,
                                top: 20,
                                bottom: 0,
                            },
                        },
                        scales: {
                            yAxes: [{
                                display: true,
                                gridLines: {
                                    display: true,
                                    drawBorder: false,
                                    color: "#F2F2F2",
                                },
                                ticks: {
                                    display: true,
                                    beginAtZero: true,
                                    stepSize: 1,
                                    fontColor: "#6C7383",
                                },
                            }],
                            xAxes: [{
                                stacked: false,
                                ticks: {
                                    beginAtZero: true,
                                    fontColor: "#6C7383",
                                },
                                gridLines: {
                                    color: "rgba(0, 0, 0, 0)",
                                    display: false,
                                },
                                barPercentage: 1, // Reduce bar width to make the bars closer
                                categoryPercentage: 0.5, // Reduce space between categories
                            }],
                        },
                        legend: {
                            display: true, // Show legend for both bars
                        },
                        elements: {
                            point: {
                                radius: 0,
                            },
                        },
                    },
                });
            }
        </script>

        {{-- SEARCH LOG --}}
        <script>
            $(document).ready(function() {
                $('#searchLog').on('keyup', function() {
                    var query = $(this).val();
                    $.ajax({
                        url: "{{ route('dashboard.search') }}",
                        type: "GET",
                        data: {
                            searchLog: query
                        },
                        success: function(data) {
                            $('#logResults').html(data);
                        }
                    });
                });
            });
        </script>

        {{-- PR PER MONTH --}}
        <script>
            if ($("#prMonth-chart").length) {
                var months = @json($months); // Extract the formatted month-year labels
                var totalPRs = @json($totalPRs); // Extract total PR counts

                var PRMonthChartCanvas = $("#prMonth-chart").get(0).getContext("2d");
                var PRMonthChart = new Chart(PRMonthChartCanvas, {
                    type: "line",
                    data: {
                        labels: months, // Use the formatted months as labels
                        datasets: [{
                            label: "Purchase Requests in the Last 4 Months",
                            data: totalPRs, // Use total PR counts as data
                            backgroundColor: "rgba(98, 181, 229, 0.5)", // Adjust line color
                            borderColor: "#98BDFF",
                            fill: true,
                            borderWidth: 2,
                            tension: 0.3, // Smooth the line
                            pointRadius: 3, // Point size on the line
                            pointBackgroundColor: "#ffffff", // Point color
                            pointBorderWidth: 2, // Point border size
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: {
                            padding: {
                                left: 0,
                                right: 0,
                                top: 20,
                                bottom: 0,
                            },
                        },
                        scales: {
                            yAxes: [{
                                display: true,
                                gridLines: {
                                    display: true,
                                    drawBorder: false,
                                    color: "#F2F2F2",
                                },
                                ticks: {
                                    display: true,
                                    beginAtZero: true,
                                    stepSize: 1,
                                    fontColor: "#6C7383",
                                },
                            }],
                            xAxes: [{
                                display: true,
                                ticks: {
                                    fontColor: "#6C7383",
                                },
                                gridLines: {
                                    display: false,
                                },
                            }],
                        },
                        legend: {
                            display: true, // Display legend
                        },
                        elements: {
                            point: {
                                radius: 3, // Point size on the line
                            },
                        },
                    },
                });
            }
        </script>

        <!-- PERCENTAGE PR DONE -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Get the total PR Done and Total PR from the Blade view
                const totalPRDone = {{ $totalPRDone }};
                const totalAllPRs = {{ $allPRs }};

                // Calculate the percentage of PR Done
                let percentagePRDone = 0;
                if (totalAllPRs > 0) {
                    percentagePRDone = (totalPRDone / totalAllPRs) * 100;
                }

                // Display the percentage with 2 decimal places
                document.getElementById('percentagePRDone').textContent = percentagePRDone.toFixed(1) +
                    '% of all Purchase Requests (All time)';
            });
        </script>

        {{-- TOTAL SPEND THIS MONTH --}}
        <script>
            document.addEventListener('DOMContentLoaded', async function() {
                // Get the purchaseOrders data passed from Laravel
                const purchaseOrders = @json($purchaseOrders);
                // console.log("Purchase Orders Data: ", purchaseOrders);

                let totalConvertedPrice = 0; // Variable to store total converted price

                // Function to fetch conversion rates from Frankfurter API
                async function fetchConversionRate(baseCurrency, targetCurrency, date) {
                    const url = `https://api.frankfurter.app/${date}?from=${baseCurrency}&to=${targetCurrency}`;
                    try {
                        // console.log(
                        //     `Fetching conversion rate for ${baseCurrency} to ${targetCurrency} on ${date}`);
                        const response = await fetch(url);
                        const data = await response.json();
                        // console.log("Conversion rate data: ", data);
                        return data.rates[targetCurrency];
                    } catch (error) {
                        // console.error("Error fetching conversion rate: ", error);
                        return null;
                    }
                }

                // Iterate over each purchase order item and convert currency if necessary
                for (const item of purchaseOrders) {
                    const {
                        currency,
                        price,
                        quantity,
                        purchase_date
                    } = item;
                    const numericPrice = parseFloat(price); // Ensure price is treated as a number
                    const totalItemPrice = numericPrice * quantity; // Calculate price based on quantity
                    // console.log(
                    //     `Processing item with currency: ${currency}, price: ${price}, quantity: ${quantity}, date: ${purchase_date}`
                    // );
                    // console.log(`Total price for this item (price * quantity): ${totalItemPrice}`);

                    if (currency !== "IDR") {
                        const conversionRate = await fetchConversionRate(currency, 'IDR', purchase_date);
                        if (conversionRate) {
                            const convertedPrice = totalItemPrice * conversionRate;
                            // console.log(`Converted price from ${currency} to IDR: ${convertedPrice}`);
                            totalConvertedPrice += convertedPrice; // Add converted price to total
                        }
                    } else {
                        // If currency is already in IDR, add it directly to total
                        // console.log(`Currency is IDR, adding price directly: ${totalItemPrice}`);
                        totalConvertedPrice += totalItemPrice;
                    }

                    // Log the total price after each iteration
                    // console.log(`Current total converted price: ${totalConvertedPrice}`);
                }

                // Convert totalConvertedPrice to Indonesian currency format
                const formattedTotalPrice = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR'
                }).format(totalConvertedPrice);

                // Log the final total price
                // console.log(`Final total converted price: ${formattedTotalPrice}`);

                // Display the total in the HTML element with class 'totalSpend'
                $('.totalSpend').html(`${formattedTotalPrice}`);
                // console.log(formattedTotalPrice);
            });
        </script>

        {{-- TOP SUPPLIER --}}
        <script>
            document.addEventListener('DOMContentLoaded', async function() {
                // Fetch all the purchase orders and supplier data
                let topSuppliers = @json($topSuppliers);

                // Create an object to store aggregated data per supplier
                let aggregatedSuppliers = {};

                // Create an object to store cached conversion rates
                let conversionCache = {};

                // Loop through each purchase order and aggregate data for each supplier
                for (let purchaseOrder of topSuppliers) {
                    if (!aggregatedSuppliers[purchaseOrder.supplier_id]) {
                        // Initialize supplier entry
                        aggregatedSuppliers[purchaseOrder.supplier_id] = {
                            supplier_name: purchaseOrder.supplier_name || "No Name",
                            supplier_address: purchaseOrder.supplier_address || "No Address",
                            supplier_city: purchaseOrder.supplier_city || "No City",
                            supplier_country: purchaseOrder.supplier_country || "No Country",
                            total_spend: 0, // We'll calculate this below
                            currency: purchaseOrder.currency,
                            items: [], // Placeholder for items
                            purchase_orders: [],
                        };
                    }

                    // Ensure that quantity and price are numbers; default to 0 if missing
                    const quantity = parseFloat(purchaseOrder.quantity) || 0;
                    const unitPrice = parseFloat(purchaseOrder.price) || 0;

                    // Calculate the total spend for this purchase order (quantity * price)
                    let totalSpendForOrder = quantity * unitPrice;

                    // If currency is not IDR, convert it using the conversion API
                    let convertedAmount = 0;
                    if (purchaseOrder.currency !== 'IDR') {
                        convertedAmount = await convertCurrencyOnDate(purchaseOrder.currency, 'IDR',
                            totalSpendForOrder, purchaseOrder.purchase_date);
                    } else {
                        convertedAmount = totalSpendForOrder;
                    }

                    // Add to supplier's total spend
                    aggregatedSuppliers[purchaseOrder.supplier_id].total_spend += convertedAmount;

                    // Add items if available
                    aggregatedSuppliers[purchaseOrder.supplier_id].items = purchaseOrder.items || [];

                    // Save the purchase order in the supplier's entry
                    aggregatedSuppliers[purchaseOrder.supplier_id].purchase_orders.push(purchaseOrder);
                }

                // Convert aggregated suppliers to an array and sort by total spend
                const suppliersArray = Object.values(aggregatedSuppliers).sort((a, b) => b.total_spend - a
                    .total_spend);

                // Get the top 3 suppliers
                const top3Suppliers = suppliersArray.slice(0, 3);

                // Now, update the UI to display the top 3 suppliers
                updateCarousel(top3Suppliers);
            });

            // Function to fetch API for currency conversion with a specific date
            async function convertCurrencyOnDate(fromCurrency, toCurrency, amount, date) {
                const formattedDate = new Date(date).toISOString().split('T')[0]; // Format date as YYYY-MM-DD
                const url = `https://api.frankfurter.app/${formattedDate}?from=${fromCurrency}&to=${toCurrency}`;

                try {
                    const response = await fetch(url);
                    const data = await response.json();
                    const conversionRate = data.rates[toCurrency];
                    return amount * conversionRate;
                } catch (error) {
                    console.error('Error converting currency:', error);
                    return amount; // Return the original amount if conversion fails
                }
            }

            // Function to update the carousel with the top 3 suppliers
            function updateCarousel(suppliers) {
                const carouselInner = document.querySelector('.carousel-inner');
                carouselInner.innerHTML = ''; // Clear the existing items

                suppliers.forEach((supplier, index) => {
                    const activeClass = index === 0 ? 'active' : '';

                    // Build the purchase orders list
                    let purchaseOrdersRows = supplier.items.map(item => `
            <tr class="text-center">
                <td class="text-muted">${item.item_name || "Unknown Item"}</td>
                <td class="w-100 px-0">
                    <div class="progress progress-md mx-4">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: ${(item.total_quantity / 100) * 100}%" aria-valuenow="${item.total_quantity}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </td>
                <td>
                    <h5 class="font-weight-bold mb-0">${item.total_quantity}</h5>
                </td>
            </tr>
        `).join('');

                    // Set the image URL based on the rank
                    let imageUrl = '';
                    if (index === 0) {
                        imageUrl = '/images/medali/medali 1.png'; // Ensure this path is correct
                    } else if (index === 1) {
                        imageUrl = '/images/medali/medali 2.png';
                    } else if (index === 2) {
                        imageUrl = '/images/medali/medali 3.png';
                    }

                    const carouselItem = `
            <div class="carousel-item ${activeClass}">
                <div class="row">
                    <div class="col-md-12 col-xl-5 d-flex flex-column justify-content-start">
                        <div class="ml-xl-4 mt-3">
                            <p class="card-title" id="supplierName">${supplier.supplier_name}</p>
                            <h1 class="text-primary text-nowrap mb-0" id="supplierSpend">IDR ${new Intl.NumberFormat('id-ID', {
                                                    style: 'currency',
                                                    currency: 'IDR'
                                                }).format(supplier.total_spend)}</h1>
                            <p class="mb-3 text-danger">*exclude PPN</p>
                            <h3 class="font-weight-500 mb-xl-3 text-primary">${supplier.supplier_country}</h3>
                            <p class="mb-2 mb-xl-0" id="supplierAddress" style="font-size:1rem;">${supplier.supplier_address}, ${supplier.supplier_city}, ${supplier.supplier_country}</p>
                        </div>
                    </div>
                    <div class="col-md-12 col-xl-7">
                        <div class="row">
                            <div class="col-md-8 border-right">
                                <div class="table-responsive mb-3 mb-md-0 mt-3">
                                    <table class="table table-borderless report-table">
                                        ${purchaseOrdersRows} <!-- Insert dynamic rows here -->
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-4 mt-3 text-center">
                                <img src="${imageUrl}" alt="Rank Image" style="width: 100%;" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

                    // Append the carousel item to the inner carousel
                    carouselInner.innerHTML += carouselItem;
                });
            }
        </script>

        {{-- LOTTIE --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Array of Lottie animation file paths
                const lottieFiles = [
                    'images/lottie/Animation - 1727239628352.json',
                    'images/lottie/Animation - 1727248673948.json',
                    'images/lottie/Animation - 1727248747526.json',
                    'images/lottie/Animation - 1727249012437.json',
                    'images/lottie/Animation - 1727249282767.json',
                    'images/lottie/Animation - 1727249316439.json',
                ];

                // Function to get a random file from the array
                function getRandomLottieFile() {
                    const randomIndex = Math.floor(Math.random() * lottieFiles.length);
                    return lottieFiles[randomIndex];
                }

                // Load the random Lottie animation
                lottie.loadAnimation({
                    container: document.getElementById('lottie-animation'), // Element to display the animation
                    renderer: 'svg', // You can use 'canvas' too
                    loop: true, // Whether the animation should loop
                    autoplay: true, // Start the animation automatically
                    path: getRandomLottieFile() // Use the random file path
                });
            });
        </script>
    @endsection
</div>
