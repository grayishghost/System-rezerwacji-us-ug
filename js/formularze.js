document.addEventListener("DOMContentLoaded", function () {
  var haslo = document.getElementById("haslo");
  var haslo2 = document.getElementById("haslo2");
  if (!haslo || !haslo2) {
    return;
  }

  function sprawdzZgodnosc() {
    if (haslo2.value !== "" && haslo.value !== haslo2.value) {
      haslo2.setCustomValidity("Hasła nie są takie same.");
    } else {
      haslo2.setCustomValidity("");
    }
  }

  haslo.addEventListener("input", sprawdzZgodnosc);
  haslo2.addEventListener("input", sprawdzZgodnosc);
});
