document.addEventListener("DOMContentLoaded", function () {
    const inputBoxes = document.querySelectorAll(".input_box");

    inputBoxes.forEach((box) => {
        const input = box.querySelector("input");
        const error = box.querySelector(".text_error");

        input.addEventListener("input", () => {
            if (input.value.trim() === "") {
                error.classList.remove("hidden");
                error.classList.add("show");
            } else {
                error.classList.remove("show");
                error.classList.add("hidden");
            }
        });
    });
});