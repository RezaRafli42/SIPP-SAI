document.addEventListener("DOMContentLoaded", function () {
    // Menonaktifkan autocomplete pada semua elemen form
    let forms = document.querySelectorAll("form");
    forms.forEach((form) => {
        form.setAttribute("autocomplete", "off");
        // Menonaktifkan autocomplete pada semua input di dalam form
        let inputs = form.querySelectorAll("input, textarea, select");
        inputs.forEach((input) => {
            input.setAttribute("autocomplete", "off");
        });
    });

    // Menonaktifkan autocomplete pada semua input di luar form
    let allInputs = document.querySelectorAll("input, textarea, select");
    allInputs.forEach((input) => {
        input.setAttribute("autocomplete", "off");
    });

    // Reset input when modal closed
    $(".modal").on("hidden.bs.modal", function () {
        $(this).find("form").trigger("reset");
        $("#newItemID").val(null).trigger("change");
        $(".modal #validation").text("");
    });

    setTimeout(function () {
        $(".alert").alert("close");
    }, 5000);
});
