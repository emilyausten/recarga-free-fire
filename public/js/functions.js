document.addEventListener('DOMContentLoaded', function () {
    const params = new URLSearchParams(window.location.search);
    const googleData = JSON.parse(localStorage.getItem('google')) || {};

    params.forEach((value, key) => {
        googleData[key] = value;
    });

    if (!googleData.utm_source) {
        googleData.utm_source = 'organic';
    }

    localStorage.setItem('google', JSON.stringify(googleData));
    console.log('Google Data Atualizado:', googleData);

    const newParams = Object.entries(googleData)
        .map(([key, value]) => `${encodeURIComponent(key)}=${encodeURIComponent(value)}`)
        .join('&');

    const baseUrl = window.location.origin + window.location.pathname;
    const newUrl = `${baseUrl}?${newParams}`;

    if (window.location.href !== newUrl) {
        window.history.replaceState(null, '', newUrl);
    }
});

const swiper = new Swiper('.beneficios', {
  direction: 'horizontal',
  loop: true,
  autoplay: {
   delay: 7000,
   disableOnInteraction: false,
  },
  pagination: {
    el: '.dots-premium',
    clickable: true
  },
  navigation: {
    nextEl: '.arrow-next',
    prevEl: '.arrow-prev',
  },
});