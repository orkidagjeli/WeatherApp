<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather Data</title>
    <link rel="stylesheet" href="css.css"> <!-- Link to the external CSS file -->
</head>
<body>

    <!-- Weather Card -->
    <div class="weather-card" id="weather-card">
        <div class="mask"></div>
        <div class="card-img-overlay">
            <p class="display-2 my-3" id="current-temp">--°C</p>
            <p class="mb-2">Feels Like: <strong id="feels-like">--°C</strong></p>
            <h5 id="weather-description">Description</h5>
        </div>
    </div>

    <!-- Weather Data Table -->
    <table class="floating-table">
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>Current Temperature</th>
                <th>Feels Like</th>
                <th>Weather Description</th>
                <th>Weather Icon</th>
            </tr>
        </thead>
        <tbody id="weather-data">
            <!-- Data will be inserted here -->
        </tbody>
    </table>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function fetchWeatherData() {
            $.ajax({
                url: 'backend.php', 
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log(response); // For debugging
                    var tableBody = '';
                    var weatherInfo = response[0]; // Assuming only one record is returned

                    // Update Weather Card
                    $('#current-temp').text(sanitize(weatherInfo.current_temp) + '°');
                    $('#feels-like').text(sanitize(weatherInfo.feels_like) + '°');
                    $('#weather-description').text(sanitize(weatherInfo.weather_description));

                    // Set background image based on weather description
                    var weatherCard = $('#weather-card');
                    var description = weatherInfo.weather_description.toLowerCase();
                    if (description.includes('rain')) {
                        weatherCard.css('background-image', 'url("https://media.istockphoto.com/id/547033564/photo/rain-flows-down-from-a-roof-down.jpg?s=612x612&w=0&k=20&c=aLopVs-9BIIe2Fc5uiB8vFu6SqIUXT1f1P6lY7kwKbM=")');
                    } else if (description.includes('sunny')) {
                        weatherCard.css('background-image', 'url("https://pasadenanow.com/main/wp-content/uploads/2018/01/Sunny7402.jpg")');
                    } else if (description.includes('wind')) {
                        weatherCard.css('background-image', 'url("https://www.shutterstock.com/image-vector/image-cute-cloud-blowing-wind-600nw-1048046437.jpg")');
                    } else if (description.includes('cloud')) {
                        weatherCard.css('background-image', 'url("https://i.pinimg.com/736x/66/95/33/6695331f75ad5dbe126d77743e0460a4.jpg")');
                    } else {
                        weatherCard.css('background-image', 'url("https://images.theconversation.com/files/442675/original/file-20220126-17-1i0g402.jpg?ixlib=rb-4.1.0&q=45&auto=format&w=1356&h=668&fit=crop")'); // Default background
                    }

                    // Update Weather Data Table
                    $.each(response, function(index, row) {
                        var weatherIcon = getWeatherIcon(row.weather_description); // Get the icon based on description
                        tableBody += '<tr>';
                        tableBody += '<td>' + sanitize(row.timestamp) + '</td>';
                        tableBody += '<td>' + sanitize(row.current_temp) + '°</td>';
                        tableBody += '<td>' + sanitize(row.feels_like) + '°</td>';
                        tableBody += '<td>' + sanitize(row.weather_description) + '</td>';
                        tableBody += '<td><img src="' + weatherIcon + '" alt="' + sanitize(row.weather_description) + ' icon" class="weather-icon" /></td>';
                        tableBody += '</tr>';
                    });
                    $('#weather-data').html(tableBody);
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching weather data:', xhr.responseText); // For debugging
                }
            });
        }

        function getWeatherIcon(description) {
            var desc = description.toLowerCase();
            if (desc.includes('rain')) {
                return 'https://cdn-icons-png.flaticon.com/512/4724/4724091.png'; 
            } else if (desc.includes('sunny')) {
                return 'https://cdn.icon-icons.com/icons2/1370/PNG/512/if-weather-3-2682848_90785.png'; 
            } else if (desc.includes('wind')) {
                return 'https://cdn-icons-png.freepik.com/512/7084/7084520.png'; 
            } else if (desc.includes('cloud')) {
                return 'https://cdn-icons-png.flaticon.com/512/1163/1163736.png'; 
            } else {
                return 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR4IHBGQwRqf4xCHQwv9iokF4IRww7e-Kft7g&s'; // Default icon
            }
        }

        function sanitize(str) {
            return $('<div>').text(str).html();
        }

        setInterval(fetchWeatherData, 300000); // Refresh every 5 minutes
        fetchWeatherData(); // Initial fetch
    </script>
</body>
</html>
