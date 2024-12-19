// Dummy data for crops and temperature/humidity
const cropData = [
    { batchNo: 'B001', name: 'Tomatoes', quantity: '500 kg', harvestDate: '2024-01-01', location: 'Farm A' },
    { batchNo: 'B002', name: 'Potatoes', quantity: '750 kg', harvestDate: '2024-01-05', location: 'Farm B' },
    // Add more dummy crop data here
  ];
  
  const tempHumidityData = {
    temperature: [22, 23, 24, 25, 26], // Example temperature data
    humidity: [60, 62, 65, 66, 67] // Example humidity data
  };
  
  // Populating the crop table
  function populateCropTable() {
    const tableBody = document.getElementById('crop-data');
    tableBody.innerHTML = ''; // Clear previous data
    cropData.forEach(crop => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${crop.batchNo}</td>
        <td>${crop.name}</td>
        <td>${crop.quantity}</td>
        <td>${crop.harvestDate}</td>
        <td>${crop.location}</td>
        <td><button class="edit-btn">Edit</button><button class="delete-btn">Delete</button></td>
      `;
      tableBody.appendChild(row);
    });
  }
  
  // Temperature & Humidity live graph
  function updateTempHumidityChart() {
    const ctx = document.getElementById('tempHumidityChart').getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['1', '2', '3', '4', '5'],
        datasets: [{
          label: 'Temperature (°C)',
          data: tempHumidityData.temperature,
          borderColor: 'rgba(255, 99, 132, 1)',
          fill: false
        }, {
          label: 'Humidity (%)',
          data: tempHumidityData.humidity,
          borderColor: 'rgba(54, 162, 235, 1)',
          fill: false
        }]
      },
      options: {
        responsive: true,
        scales: {
          x: {
            beginAtZero: true
          }
        }
      }
    });
  }
  
  // Call functions to populate data
  window.onload = function() {
    populateCropTable();
    updateTempHumidityChart();
  };
  
  // Search functionality
  function searchTable() {
    const searchInput = document.getElementById('search-bar').value.toLowerCase();
    const tableRows = document.querySelectorAll('#crop-table tbody tr');
    tableRows.forEach(row => {
      const cells = row.querySelectorAll('td');
      const cropName = cells[1].textContent.toLowerCase();
      if (cropName.indexOf(searchInput) === -1) {
        row.style.display = 'none';
      } else {
        row.style.display = '';
      }
    });
  }
  


  // Example JS function for form validation or other actions
document.querySelector('form').addEventListener('submit', function(event) {
    const cropName = document.getElementById('name').value;
    if (!cropName) {
        alert('Please enter a crop name!');
        event.preventDefault();
    }
});


// Event listener for crop name input
document.getElementById('crop_name').addEventListener('input', function() {
    var cropName = this.value.trim();

    // If the crop name is filled, show the table
    if (cropName) {
        document.getElementById('crop-table').style.display = 'block';
        fetchBatchNumbers(cropName); // Fetch batch numbers when crop name is entered
    } else {
        document.getElementById('crop-table').style.display = 'none';
    }
});

// Fetch batch numbers from the database
function fetchBatchNumbers(cropName) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'fetch_batches.php?crop_name=' + cropName, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            var batches = JSON.parse(xhr.responseText);
            var tbody = document.querySelector('#crop-table tbody');
            tbody.innerHTML = ''; // Clear previous rows

            batches.forEach(function(batch) {
                var row = document.createElement('tr');
                row.innerHTML = `<td>${batch.batch_number}</td><td>${cropName}</td><td>${batch.farmer_id}</td><td>${batch.harvest_date}</td>`;
                tbody.appendChild(row);
            });
        }
    };
    xhr.send();
}

// Validate Farmer ID existence
document.getElementById('farmer_id').addEventListener('change', function() {
    var farmerId = this.value;
    var warning = document.getElementById('farmer-warning');

    if (farmerId) {
        checkFarmerExistence(farmerId, warning);
    } else {
        warning.style.display = 'none';
    }
});

function checkFarmerExistence(farmerId, warningElement) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'check_farmer.php?farmer_id=' + farmerId, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            if (xhr.responseText === 'exists') {
                warningElement.style.display = 'none';
            } else {
                warningElement.style.display = 'inline';
            }
        }
    };
    xhr.send();
}


document.addEventListener("DOMContentLoaded", function() {
    const farmerIdInput = document.getElementById("farmer_id");
    const cropSelect = document.getElementById("crop_name");
    const batchSelect = document.getElementById("batch_number");
    const farmerWarning = document.getElementById("farmer_warning");

    // Check if Farmer ID exists when user types in the Farmer ID field
    farmerIdInput.addEventListener("blur", function() {
        const farmerId = farmerIdInput.value;

        if (farmerId) {
            fetch("check_farmer.php?farmer_id=" + farmerId)
                .then(response => response.text())
                .then(data => {
                    if (data === 'not_exists') {
                        farmerWarning.style.display = "inline";
                    } else {
                        farmerWarning.style.display = "none";
                    }
                });
        }
    });

    // Fetch batch numbers when a crop is selected
    cropSelect.addEventListener("change", function() {
        const cropName = cropSelect.value;

        if (cropName) {
            fetch("fetch_batches.php?crop_name=" + cropName)
                .then(response => response.json())
                .then(data => {
                    batchSelect.innerHTML = '<option value="">Select Batch</option>'; // Clear existing options
                    if (data.length > 0) {
                        data.forEach(batch => {
                            const option = document.createElement("option");
                            option.value = batch.batch_number;
                            option.textContent = `Batch #${batch.batch_number} (Farmer ID: ${batch.farmer_id}, Date: ${batch.harvest_date})`;
                            batchSelect.appendChild(option);
                        });
                        batchSelect.disabled = false;
                    } else {
                        batchSelect.disabled = true;
                    }
                });
        } else {
            batchSelect.disabled = true;
        }
    });
});
