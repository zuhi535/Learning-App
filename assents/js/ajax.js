document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".quiz-form").forEach(form => {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            let formData = new FormData(this);

            fetch("submit_quiz.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Sikeresen elküldve!");
                } else {
                    alert("Hiba történt!");
                }
            })
            .catch(error => console.error("Hiba:", error));
        });
    });
});
