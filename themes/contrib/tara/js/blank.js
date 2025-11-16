const input = document.getElementById('edit-combine-1');
const dropdown = document.getElementById('location-dropdown');

input.addEventListener('focus', () => {
  dropdown.style.display = 'block';
});

document.addEventListener('click', (e) => {
  if (!document.querySelector('.location-box').contains(e.target)) {
    dropdown.style.display = 'none';
  }
});

// Use My Location Click
document.querySelector('.dropdown-item.detect').addEventListener('click', function () {
  alert("Detecting location here… geolocation code add कर देंगे ✅");
});