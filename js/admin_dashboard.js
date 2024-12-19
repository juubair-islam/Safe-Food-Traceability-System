// JS to handle Admin dashboard specific interactions

// Example for handling the search input (could be expanded for other functionalities)
function searchTable() {
    let input = document.getElementById('search-bar');
    let filter = input.value.toUpperCase();
    let table = document.getElementById('crop-table');
    let tr = table.getElementsByTagName('tr');
  
    for (let i = 0; i < tr.length; i++) {
      let td = tr[i].getElementsByTagName('td')[1]; // Search by Crop Name (second column)
      if (td) {
        let txtValue = td.textContent || td.innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
          tr[i].style.display = '';
        } else {
          tr[i].style.display = 'none';
        }
      }
    }
  }
  
  // Dummy data for the temperature/humidity graph to be dynamically updated
  setInterval(() => {
    const tempValue = Math.floor(Math.random() * 10) + 20; // Dummy temperature value between 20 and 30
    const humidityValue = Math.floor(Math.random() * 20) + 50; // Dummy humidity value between 50 and 70
    document.getElementById('temp-value').textContent = `${tempValue} °C`;
    document.getElementById('humidity-value').textContent = `${humidityValue}%`;
  }, 1000); // Update every second
  