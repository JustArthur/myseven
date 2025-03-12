document.addEventListener("DOMContentLoaded", function () {
    const inputBoxes = document.querySelectorAll(".input_box");

    inputBoxes.forEach((box) => {
        const input = box.querySelector("input");
        const error = box.querySelector(".text_error");

        input.addEventListener("input", () => {
            if (input.value.trim() === "") {
                error.classList.remove("hidden");
                error.classList.add("show");
                input.style.border = "1px solid var(--error-color)";
            } else {
                error.classList.remove("show");
                error.classList.add("hidden");
                input.style.border = "";
            }
        });
    });
});