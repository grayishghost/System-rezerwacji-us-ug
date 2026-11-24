const checkIn = document.querySelector('#data_od');
const checkOut = document.querySelector('#data_do');
if (checkIn && checkOut) {
  const validateDates = () => {
    checkOut.min = checkIn.value;
    checkOut.setCustomValidity(checkOut.value && checkOut.value <= checkIn.value ? 'Data wymeldowania musi być późniejsza niż data zameldowania.' : '');
  };
  checkIn.addEventListener('change', validateDates);
  checkOut.addEventListener('change', validateDates);
}
