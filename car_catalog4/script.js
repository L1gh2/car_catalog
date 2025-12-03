document.addEventListener('DOMContentLoaded', function() {
    // Обработчик события клика на кнопку "Производители" в навигационном меню
    const manufacturersBtn = document.getElementById('manufacturersBtn');
    if (manufacturersBtn) {
        manufacturersBtn.addEventListener('click', function() {
            window.location.href = 'manufacturers.php';
        });
    }

    // Обработчики для кнопок "Подробнее" на странице производителей
    const detailButtons = document.querySelectorAll('section.manufacturers ul li button');
    detailButtons.forEach(button => {
        button.addEventListener('click', function() {
            const manufacturerId = this.previousElementSibling.href.split('manufacturer_id=')[1];
            window.location.href = `models.php?manufacturer_id=${manufacturerId}`;
        });
    });

    // Обработчик события клика на кнопку "Производители" с идентификатором loginBtn
    const loginButton = document.getElementById("loginBtn");
    if (loginButton) {
        loginButton.addEventListener("click", function() {
            window.location.href = "manufacturers.php";
        });
    }
});
