document.addEventListener('DOMContentLoaded', function () {
    var blogForm = document.querySelector('.blog-form');

    if (blogForm) {
        blogForm.addEventListener('submit', function (event) {
            var title = document.getElementById('title').value.trim();
            var content = document.getElementById('content').value.trim();
            var formError = document.getElementById('form-error');

            if (title === '' || content === '') {
                event.preventDefault();
                formError.textContent = 'Title and content are required.';
                formError.style.display = 'block';
            } else {
                formError.textContent = '';
                formError.style.display = 'none';
            }
        });
    }

    var deleteForms = document.querySelectorAll('.delete-form');

    for (var index = 0; index < deleteForms.length; index++) {
        deleteForms[index].addEventListener('submit', function (event) {
            var confirmed = window.confirm('Are you sure you want to delete this blog?');

            if (!confirmed) {
                event.preventDefault();
            }
        });
    }
});
