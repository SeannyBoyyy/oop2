document.addEventListener("DOMContentLoaded", function () {
    // Handle Edit Button Click
    document.querySelectorAll(".editBtn").forEach(button => {
        button.addEventListener("click", function () {
            document.getElementById("editMovieId").value = this.dataset.id;
            document.getElementById("editTitle").value = this.dataset.title;
            document.getElementById("editDescription").value = this.dataset.description;
            document.getElementById("editGenre").value = this.dataset.genre;
            document.getElementById("editRating").value = this.dataset.rating;
            document.getElementById("editDuration").value = this.dataset.duration;
            document.getElementById("editRelease").value = this.dataset.release;
            document.getElementById("editStatus").value = this.dataset.status;
        });
    });

    // Handle Delete Button Click
    document.querySelectorAll(".deleteBtn").forEach(button => {
        button.addEventListener("click", function () {
            document.getElementById("deleteMovieId").value = this.dataset.id;
            document.getElementById("deleteMovieTitle").innerText = this.dataset.title;
        });
    });
});
