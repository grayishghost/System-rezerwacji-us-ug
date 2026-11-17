document.querySelectorAll('form[data-confirm]').forEach(form => {
  form.addEventListener('submit', event => {
    if (!window.confirm(form.dataset.confirm)) event.preventDefault();
  });
});
const config = document.getElementById('booking-config');
const dayForm = document.getElementById('day-form');
if (config && dayForm) {
  let request = 0;
  let controller;
  const load = async event => {
    if (!window.fetch || !window.AbortController) return;
    event.preventDefault();
    const number = ++request;
    if (controller) controller.abort();
    controller = new AbortController();
    const date = dayForm.elements.data.value;
    const target = document.getElementById('time-slots');
    const message = document.getElementById('slots-message');
    const slotForm = document.getElementById('slot-form');
    const button = slotForm.querySelector('button');
    button.disabled = true;
    target.replaceChildren();
    message.textContent = 'Sprawdzam wolne godziny…';
    const url = new URL(config.dataset.api, window.location.origin);
    url.search = new URLSearchParams({usluga: config.dataset.service, pracownik: config.dataset.employee, data: date, przeloz: config.dataset.move});
    try {
      const response = await fetch(url, {signal: controller.signal, credentials: 'same-origin', cache: 'no-store'});
      const data = await response.json();
      if (number !== request) return;
      if (!response.ok) throw new Error(data.error || 'Nie udało się pobrać godzin.');
      const pageUrl = new URL(window.location.href);
      pageUrl.searchParams.set('data', date);
      window.history.replaceState(null, '', pageUrl);
      slotForm.action = pageUrl.href;
      const fragment = document.createDocumentFragment();
      data.slots.forEach(slot => {
        const label = document.createElement('label');
        label.className = 'time-slot';
        const input = document.createElement('input');
        input.type = 'radio'; input.name = 'godzina'; input.value = slot.value; input.required = true;
        const span = document.createElement('span');
        span.textContent = slot.value + ' – ' + slot.end;
        label.append(input, span); fragment.append(label);
      });
      target.replaceChildren(fragment);
      message.textContent = data.slots.length ? 'Wybierz jedną z ' + data.slots.length + ' wolnych godzin.' : 'Brak wolnych terminów. Wybierz inną datę lub pracownika.';
      button.disabled = !data.slots.length;
    } catch (error) {
      if (error.name === 'AbortError' || number !== request) return;
      message.textContent = 'Nie udało się pobrać godzin. Użyj przycisku „Pokaż wolne godziny”, aby odświeżyć stronę.';
      dayForm.removeEventListener('submit', load);
    }
  };
  dayForm.addEventListener('submit', load);
  dayForm.elements.data.addEventListener('change', load);
}
